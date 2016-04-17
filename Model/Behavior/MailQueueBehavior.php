<?php
/**
 * メールキュー Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMailAssignTag', 'Mails.Utility');
App::uses('MailSettingFixedPhrase', 'Mails.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * メールキュー Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
 */
class MailQueueBehavior extends ModelBehavior {

/**
 * 承認機能の種類
 *
 * @var string 承認機能なし
 * @var string ワークフロー
 * @var string コンテンツコメント
 * @var string 回答（アンケート、登録フォーム等）
 */
	const
		MAIL_QUEUE_WORKFLOW_TYPE_NONE = 'none',
		MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW = 'workflow',
		MAIL_QUEUE_WORKFLOW_TYPE_COMMENT = 'contentComment',
		MAIL_QUEUE_WORKFLOW_TYPE_ANSWER = 'answer';

/**
 * セッティングの種類(setSettingで利用)
 *
 * @var string 任意で送信するユーザID（グループ送信（回覧板、カレンダー等）、アンケートを想定）
 * @var string 任意で送信するメールアドレス（登録フォーム等を想定）
 * @var string 投稿メールのON, OFF（回覧板、カレンダー等を想定）
 * @var string ルーム配信で送らないユーザID
 * @var string プラグイン名
 */
	const
		MAIL_QUEUE_SETTING_USER_IDS = 'userIds',
		MAIL_QUEUE_SETTING_TO_ADDRESSES = 'toAddresses',
		MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST = 'isMailSendPost',
		MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS = 'notSendRoomUserIds',
		MAIL_QUEUE_SETTING_PLUGIN_NAME = 'pluginName';

/**
 * setup
 *
 * #### サンプルコード
 * ##### Model
 * ```
 * public $actsAs = array(
 *	'Mails.MailQueue' => array(
 *		'embedTags' => array(
 *			'X-SUBJECT' => 'Video.title',
 *			'X-BODY' => 'Video.description',
 *		),
 *	),
 * ```
 * 注意事項：ワークフロー利用時はWorkflow.Workflowより下に記述
 *
 * @param Model $model モデル
 * @param array $settings 設定値
 * @return void
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::setup
 */
	public function setup(Model $model, $settings = array()) {
		$this->settings[$model->alias] = $settings;

		// --- 設定ないパラメータの処理
		if (!isset($this->settings[$model->alias]['workflowType'])) {
			// --- ワークフローのstatusによって送信内容を変える
			if ($model->Behaviors->loaded('Workflow.Workflow')) {
				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW;
			} else {
				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_NONE;
			}
		}
		if (!isset($this->settings[$model->alias]['keyField'])) {
			$this->settings[$model->alias]['keyField'] = 'key';
		}
		if (!isset($this->settings[$model->alias]['pluginKey'])) {
			$this->settings[$model->alias]['pluginKey'] = Current::read('Plugin.key');
		}
		if (!isset($this->settings[$model->alias]['reminder'])) {
			$this->settings[$model->alias]['reminder']['sendTimes'] = null;
			$this->settings[$model->alias]['reminder']['useReminder'] = 0; // リマインダー使わない
		}
		if (!isset($this->settings[$model->alias]['publishablePermissionKey'])) {
			$this->settings[$model->alias]['publishablePermissionKey'] = 'content_publishable';
		}

		$this->settings[$model->alias]['addEmbedTagsValues'] = array();
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_USER_IDS] = null;
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_TO_ADDRESSES] = null;
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST] = null;
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS] = array();
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_PLUGIN_NAME] = Current::read('Plugin.Name');

		$model->Behaviors->load('Mails.IsMailSend', $this->settings);

		$model->MailSetting = ClassRegistry::init('Mails.MailSetting', true);
		$model->MailQueue = ClassRegistry::init('Mails.MailQueue', true);
		$model->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);
		$model->SiteSetting = ClassRegistry::init('SiteManager.SiteSetting', true);
	}

/**
 * afterSave is called after a model is saved.
 *
 * @param Model $model モデル
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return bool
 * @see Model::save()
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::afterSave
 */
	public function afterSave(Model $model, $created, $options = array()) {
		$model->Behaviors->load('Mails.MailQueueDelete');
		$contentKey = $this->__getContentKey($model);

		// 未来日系の送信日時更新を考慮して delete->insert
		/** @see MailQueueDeleteBehavior::deleteQueue() */
		$model->deleteQueue($contentKey);
		// MailQueueDeleteBehaviorはunloadしない。モデル側のactAsで既に、MailQueueDeleteBehavior を読み込んでいる場合、下記エラーが出るため。
		// Notice (8): Undefined index: MailQueueDelete [CORE/Cake/Utility/ObjectCollection.php, line 128]
		// Warning (2): call_user_func_array() expects parameter 1 to be a valid callback, first array member is not a valid class name or object [CORE/Cake/Utility/ObjectCollection.php, line 128]

		// --- リマインダー
		/** @see IsMailSendBehavior::isMailSendReminder() */
		if ($model->isMailSendReminder()) {
			$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
			$this->saveQueue($model, $sendTimeReminders);
		}

		$sendTimePublish = $this->__getSendTimePublish($model);
		$settingPluginKey = $this->__getSettingPluginKey($model);

		// --- 通常メール
		/** @see IsMailSendBehavior::isMailSend() */
		if ($model->isMailSend(MailSettingFixedPhrase::DEFAULT_TYPE, $contentKey, $sendTimePublish,
				$settingPluginKey)) {
			$this->saveQueue($model, array($sendTimePublish));
		}

		return true;
	}

/**
 * 追加の埋め込みタグ セット
 * ・追加タグをセットできる
 * ・X-URL等、既存タグ値の上書きできる
 *
 * @param Model $model モデル
 * @param string $embedTag 埋め込みタグ
 * @param string $value タグから置き換わる値
 * @return void
 */
	public function setAddEmbedTagValue(Model $model, $embedTag, $value) {
		$this->settings[$model->alias]['addEmbedTagsValues'][$embedTag] = $value;
	}

/**
 * セッティング セット
 *
 * @param Model $model モデル
 * @param string $settingKey セッティングのキー
 * @param string|array $settingValue セッティングの値
 * @return void
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_TO_ADDRESSES
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_PLUGIN_NAME
 */
	public function setSetting(Model $model, $settingKey, $settingValue) {
		$this->settings[$model->alias][$settingKey] = $settingValue;
	}

/**
 * リマインダー送信日時 セット
 *
 * @param Model $model モデル
 * @param array $sendTimeReminders リマインダー送信日時 配列
 * @return void
 */
	public function setSendTimeReminder(Model $model, $sendTimeReminders) {
		$now = NetCommonsTime::getNowDatetime();
		foreach ($sendTimeReminders as $key => $sendTime) {
			// リマインダーで日時が過ぎてたら、メール送らないので、除外する
			if (strtotime($now) > strtotime($sendTime)) {
				unset($sendTimeReminders[$key]);
			}
		}
		if (empty($sendTimeReminders)) {
			return;
		}

		$this->settings[$model->alias]['reminder']['sendTimes'] = $sendTimeReminders;
		$this->settings[$model->alias]['reminder']['useReminder'] = 1;
	}

/**
 * 公開するメール送信日時 ゲット
 *
 * @param Model $model モデル
 * @return date 送信日時
 */
	private function __getSendTimePublish(Model $model) {
		// DBに項目があり期限付き公開の時のみ、公開日時を取得する（ブログを想定）。その後、未来日メール送られる
		if ($model->hasField(['public_type', 'publish_start']) && $model->data[$model->alias]['public_type'] == WorkflowBehavior::PUBLIC_TYPE_LIMITED) {
			return $model->data[$model->alias]['publish_start'];
		}
		return null;
	}

/**
 * save時のメール送信日時 ゲット
 *
 * @param date $sendTime モデル
 * @return date 送信日時
 */
	private function __getSaveSendTime($sendTime = null) {
		if ($sendTime === null) {
			$sendTime = NetCommonsTime::getNowDatetime();
		}
		return $sendTime;
	}

/**
 * 承認つかうフラグ ゲット
 *
 * @param Model $model モデル
 * @return int 承認つかうフラグ
 */
	private function __getUseWorkflow(Model $model) {
		// 暫定対応：3/20現時点。今後見直し予定  https://github.com/NetCommons3/Mails/issues/44
		$key = Hash::get($this->settings, $model->alias . '.useWorkflow');
		if ($key === null) {
			// 暫定対応
			$useWorkflow = 1;
		} else {
			$useWorkflow = Hash::get($model->data, $key);
		}
		return $useWorkflow;
	}

/**
 * コンテンツキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getContentKey(Model $model) {
		$keyField = $this->settings[$model->alias]['keyField'];
		return $model->data[$model->alias][$keyField];
	}

/**
 * プラグイン設定を取得するためのプラグインキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getSettingPluginKey(Model $model) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			return $model->data[$model->alias]['plugin_key'];
		}
		// 通常
		return Current::read('Plugin.key');
	}

/**
 * 登録ユーザID ゲット
 *
 * @param Model $model モデル
 * @return string 登録ユーザID
 */
	private function __getCreatedUserId(Model $model) {
		// コンテンツコメント承認時に利用, update時は created_user がセットされないので、findする
		$createdUserId = Hash::get($model->data, $model->alias . '.created_user');
		if ($createdUserId === null) {
			// コンテンツコメント承認時に利用
			$data = $model->find('first', array(
				'recursive' => -1,
				'conditions' => array('id' => $model->data[$model->alias]['id']),
				'callbacks' => false,
			));
			$createdUserId = $data[$model->alias]['created_user'];
		}
		return $createdUserId;
	}

/**
 * キュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @param string $typeKey メールの種類
 * @return bool
 */
	public function saveQueue(Model $model, $sendTimes = null, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$languageId = Current::read('Language.id');
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$status = Hash::get($model->data, $model->alias . '.status');

		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW ||
			$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			// 承認依頼通知, 差戻し通知, 承認完了通知メール(即時)
			$this->__saveQueueNoticeMail($model, $languageId, $typeKey);

			// --- 公開
			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// 投稿メール - ルーム配信
				$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			// --- ワークフローの機能自体、使ってないプラグインの処理
			// --- 公開
			// 投稿メール - ルーム配信
			$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER) {
			// --- 回答
			// 回答メール配信(即時)
			$userIds = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_USER_IDS];
			$toAddresses = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_TO_ADDRESSES];

			// ユーザIDに配信(即時)、メールアドレスに配信(即時) - メールキューSave
			$mailQueueId = $this->saveQueuePostMail($model, $languageId, null, $userIds, $toAddresses, $typeKey);

			// ルーム内の承認者達に配信
			$this->__addMailQueueUserInRoomAuthorizers($model, $mailQueueId);
		}

		return true;
	}

/**
 * 投稿メール - メールキューSave
 * 公開時を想定
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param array $sendTimes メール送信日時 配列
 * @param array $userIds 送信ユーザID 配列
 * @param array $toAddresses 送信先メールアドレス 配列
 * @param string $typeKey メールの種類
 * @return int メールキューID
 * @throws InternalErrorException
 */
	public function saveQueuePostMail(Model $model, $languageId, $sendTimes = null, $userIds = null, $toAddresses = null, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		if ($sendTimes === null) {
			$sendTimes[] = $this->__getSaveSendTime();
		}
		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey);

		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$blockKey = Current::read('Block.key');

		// MailQueueUser
		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
			'not_send_room_user_ids' => null,
		);

		// 以下、実行する時は、公開時を想定

		foreach ($sendTimes as $sendTime) {

			// メール内容save
			$mailQueue['MailQueue']['send_time'] = $this->__getSaveSendTime($sendTime);
			$mailQueue = $model->MailQueue->create($mailQueue);
			/** @see MailQueue::saveMailQueue() */
			if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];

			if (isset($userIds)) {
				// --- ユーザIDに配信
				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'user_id', $userIds);

			} elseif (isset($toAddresses)) {
				// --- メールアドレスに配信
				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'to_address', $toAddresses);

			} else {
				// --- ルーム配信
				$roomId = Current::read('Room.id');
				$mailQueueUser['MailQueueUser']['room_id'] = $roomId;

				// 未来日送信は2通（承認完了とルーム配信）送るため、送らないユーザIDをセットしない
				$now = NetCommonsTime::getNowDatetime();
				if ($mailQueue['MailQueue']['send_time'] <= $now) {
					// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
					// ルーム配信で送らないユーザID セット
					$notSendRoomUserIds = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS];
					$notSendRoomUserIds = array_unique($notSendRoomUserIds);
					$notSendRoomUserIds = implode('|', $notSendRoomUserIds);
					$mailQueueUser['MailQueueUser']['not_send_room_user_ids'] = $notSendRoomUserIds;
				}

				$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
				/** @see MailQueueUser::saveMailQueueUser() */
				if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				// --- 追加のユーザ達に配信
				// ルームIDをクリア
				$mailQueueUser['MailQueueUser']['room_id'] = null;

				// 登録者にも配信
				$createdUserId = $this->__getCreatedUserId($model);
				$addUserIds = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_USER_IDS];
				$addUserIds[] = $createdUserId;
				// 登録者と追加ユーザ達の重複登録を排除
				$addUserIds = array_unique($addUserIds);

				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'user_id', $addUserIds);
			}
		}

		return $mailQueueResult['MailQueue']['id'];
	}

/**
 * 登録者に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return void
 * @throws InternalErrorException
 */
	private function __addMailQueueUserInCreatedUser(Model $model, $mailQueueId) {
		$createdUserId = $this->__getCreatedUserId($model);
		// コンテンツコメントで、参観者まで投稿を許可していると、ログインしていない人もコメント書ける。その時はuser_idなしなので送らない。
		if (empty($createdUserId)) {
			return;
		}

		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];

		/** @see MailQueueUser::addMailQueueUserInCreatedUser() */
		$model->MailQueueUser->addMailQueueUserInCreatedUser($mailQueueId, $createdUserId, $contentKey, $pluginKey);

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS][] = $createdUserId;
	}

/**
 * ルーム内の承認者達に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return bool
 * @throws InternalErrorException
 */
	private function __addMailQueueUserInRoomAuthorizers(Model $model, $mailQueueId) {
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$permissionKey = $this->settings[$model->alias]['publishablePermissionKey'];

		/** @see MailQueueUser::addMailQueueUserInRoomAuthorizers() */
		$notSendRoomUserIds = $model->MailQueueUser->addMailQueueUserInRoomAuthorizers($mailQueueId, $contentKey, $pluginKey, $permissionKey);

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS] = array_merge($this->settings[$model->alias][self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS], $notSendRoomUserIds);
	}

/**
 * 通知メール - 登録者に配信(即時) - メールキューSave
 * - 承認依頼通知, 差戻し通知, 承認完了通知メール
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return void
 * @throws InternalErrorException
 */
	private function __saveQueueNoticeMail(Model $model, $languageId, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$useWorkflow = $this->__getUseWorkflow($model);
		$createdUserId = $this->__getCreatedUserId($model);

		/** @see IsMailSendBehavior::isSendMailQueueNotice() */
		if (! $model->isSendMailQueueNotice($useWorkflow, $createdUserId)) {
			return;
		}

		// 定型文の種類
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = Hash::get($model->data, $model->alias . '.status');
		$fixedPhraseType = $mailAssignTag->getFixedPhraseType($status);

		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey, $fixedPhraseType);
		$mailQueue['MailQueue']['send_time'] = $this->__getSaveSendTime();

		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$mailQueueId = $mailQueueResult['MailQueue']['id'];

		// 登録者に配信
		$this->__addMailQueueUserInCreatedUser($model, $mailQueueId);

		// ルーム内の承認者達に配信
		$this->__addMailQueueUserInRoomAuthorizers($model, $mailQueueId);
	}

/**
 * メールキューデータ 新規作成
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @param string $fixedPhraseType SiteSettingの定型文の種類
 * @return array メールキューデータ
 * @throws InternalErrorException
 */
	private function __createMailQueue(Model $model, $languageId, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE, $fixedPhraseType = null) {
		$settingPluginKey = $this->__getSettingPluginKey($model);
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingPlugin = $model->MailSetting->getMailSettingPlugin($languageId, $typeKey, $settingPluginKey);

		$replyTo = Hash::get($mailSettingPlugin, 'MailSetting.replay_to');
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$pluginName = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_PLUGIN_NAME];
		$blockKey = Current::read('Block.key');

		// メール生文の作成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$mailAssignTag->initPlugin($languageId, $pluginName);
		$mailAssignTag->setMailFixedPhrase($languageId, $fixedPhraseType, $mailSettingPlugin);

		// --- 埋め込みタグ
		$mailAssignTag->setXUrl($contentKey);

		// ワークフロー
		$useWorkflowBehavior = $model->Behaviors->loaded('Workflow.Workflow');
		$mailAssignTag->setXWorkflowComment($model->data, $fixedPhraseType, $useWorkflowBehavior);

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$useTagBehavior = $model->Behaviors->loaded('Tags.Tag');

		// タグプラグイン
		$mailAssignTag->setXTags($model->data, $workflowType, $useTagBehavior);

		// 定型文の埋め込みタグをセット
		foreach ($this->settings[$model->alias]['embedTags'] as $embedTag => $dataKey) {
			$dataValue = Hash::get($model->data, $dataKey);
			$mailAssignTag->assignTag($embedTag, $dataValue);
		}

		// - 追加の埋め込みタグ セット
		// 既にセットされているタグであっても、上書きされる
		$mailAssignTag->assignTags($this->settings[$model->alias]['addEmbedTagsValues']);

		// 埋め込みタグ変換：メール定型文の埋め込みタグを変換して、メール生文にする
		$mailAssignTag->assignTagReplace();

		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'replay_to' => $replyTo,
			'mail_subject' => $mailAssignTag->fixedPhraseSubject,
			'mail_body' => $mailAssignTag->fixedPhraseBody,
			'send_time' => null,
		);

		// MailQueueは新規登録
		$mailQueue = $model->MailQueue->create($mailQueue);
		return $mailQueue;
	}
}
