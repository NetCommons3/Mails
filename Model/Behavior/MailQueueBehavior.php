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
App::uses('ComponentCollection', 'Controller');
App::uses('DefaultRolePermission', 'Roles.Model');

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
 * @var string 回答（アンケート、登録フォームなど）
 */
	const
		MAIL_QUEUE_WORKFLOW_TYPE_NONE = 'none',
		MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW = 'workflow',
		MAIL_QUEUE_WORKFLOW_TYPE_COMMENT = 'contentComment',
		MAIL_QUEUE_WORKFLOW_TYPE_ANSWER = 'answer';

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

		$this->settings[$model->alias]['addEmbedTagsValues'] = array();
		$this->settings[$model->alias]['userIds'] = null;
		$this->settings[$model->alias]['toAddresses'] = null;
		$this->settings[$model->alias]['isMailSendPost'] = null;
		$this->settings[$model->alias]['notSendRoomUserIds'] = array();

		$model->MailSetting = ClassRegistry::init('Mails.MailSetting', true);
		$model->MailQueue = ClassRegistry::init('Mails.MailQueue', true);
		$model->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);
		$model->SiteSetting = ClassRegistry::init('SiteManager.SiteSetting', true);
		$model->RolesRoomsUser = ClassRegistry::init('Rooms.RolesRoomsUser', true);
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
		$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];
		// --- リマインダー
		if ($useReminder) {
			$this->__saveQueueReminder($model);
		}

		// --- 通常メール
		// メールを送るかどうか
		if (! $this->isMailSend($model)) {
			return true;
		}

		$sendTime = $this->__getSendTimePublish($model);
		$this->saveQueue($model, array($sendTime));

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
 * 任意で送信するユーザID セット
 * グループ送信（回覧板、カレンダー等）、アンケートを想定
 *
 * @param Model $model モデル
 * @param array $userIds ユーザID配列
 * @return void
 */
	public function setToUserIds(Model $model, $userIds) {
		$this->settings[$model->alias]['userIds'] = $userIds;
	}

/**
 * 任意で送信するメールアドレス セット
 *
 * @param Model $model モデル
 * @param array $toAddresses メールアドレス 配列
 * @return void
 */
	public function setToAddresses(Model $model, $toAddresses) {
		$this->settings[$model->alias]['toAddresses'] = $toAddresses;
	}

/**
 * 投稿メールのON, OFF セット
 * 回覧板、カレンダー等の利用を想定
 *
 * @param Model $model モデル
 * @param int $isMailSendPost 0:通知しない、1:通知する(デフォルト)
 * @return void
 */
	public function setIsMailSendNotice(Model $model, $isMailSendPost) {
		$this->settings[$model->alias]['isMailSendPost'] = $isMailSendPost;
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
 * ルーム配信で送らないユーザID セット
 *
 * @param Model $model モデル
 * @param array $notSendRoomUserIds ユーザID配列
 * @return void
 */
	public function setNotSendRoomUserIds(Model $model, $notSendRoomUserIds) {
		$this->settings[$model->alias]['notSendRoomUserIds'] = $notSendRoomUserIds;
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
		return isset($sendTime) ? $sendTime : NetCommonsTime::getNowDatetime();
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
 * プラグイン名 ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getPluginName(Model $model) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// コンテンツコメントは pluginsテーブルに登録なしで Current::read('Plugin') とれないため、ここで直セット
			return __d('content_comments', 'comment');
		}
		// 通常
		return Current::read('Plugin.Name');
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
 * 公開許可あり（承認者、承認OFF時の一般）の編集か ゲット
 *
 * @param Model $model モデル
 * @return bool
 */
	private function __isPublishableEdit(Model $model) {
		if (!Current::permission('content_comment_publishable')) {
			// 公開権限なし
			return false;
		}

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		// --- コンテンツコメント
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			$created = Hash::get($model->data, $model->alias . '.created');
			if (isset($created)) {
				// 新規登録
				return false;
			}
			return true;
		}

		// --- 通常
		$contentKey = $this->__getContentKey($model);
		$keyField = $this->settings[$model->alias]['keyField'];
		$conditions = array($model->alias . '.' . $keyField => $contentKey);
		$data = $model->find('all', array(
			'recursive' => -1,
			'conditions' => $conditions,
			'order' => array($model->alias . '.modified DESC'),
			'callbacks' => false,
		));

		if (count($data) <= 1) {
			// 新規登録
			return false;
		}

		// keyに対して2件以上記事がある = 編集
		// 1つ前のコンテンツのステータス
		$beforeStatus = $data[1][$model->alias]['status'];
		$status = $data[0][$model->alias]['status'];

		// 承認ONでもOFFでも、公開中の記事を編集して、公開だったら、公開の編集
		// ・承認ONで、承認者が公開中の記事を編集しても、公開許可ありの編集で、メール送らない
		// ・承認OFFで、公開中の記事を編集しても、公開許可ありの編集で、メール送らない
		// ・・公開中の記事（１つ前の記事のstatus=1）
		// ・・編集した記事が公開（status=1）
		// ※承認ONで公開中の記事を編集して、編集した記事が公開なのは、承認者だけ
		if ($beforeStatus == WorkflowComponent::STATUS_PUBLISHED && $status == WorkflowComponent::STATUS_PUBLISHED) {
			// 公開の編集
			return true;
		}

		// 公開以外の編集
		return false;
	}

/**
 * 通常の投稿メールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @return bool
 */
	public function isMailSend(Model $model, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		if (! $this->__isMailSendCommon($model, $typeKey)) {
			return false;
		}

		// 投稿メールOFFなら、メール送らない
		$isMailSendPost = $this->settings[$model->alias]['isMailSendPost'];
		if (isset($isMailSendPost) && $isMailSendPost == '0') {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// DBに公開日時の項目あり & 公開日時がセットされていて & cron使えず未来日メールなら、送らない（ブログを想定）
		// 公開日時
		$sendTime = $this->__getSendTimePublish($model);
		if (! $this->__isMailSendTime($model, $sendTime)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 公開許可あり（承認者、承認OFF時の一般）の編集 and 投稿メールフラグが未設定の場合、メール送らない
		// 編集フラグ
		$isPublishableEdit = $this->__isPublishableEdit($model);
		if ($isPublishableEdit && $isMailSendPost === null) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * リマインダーメールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @return bool
 */
	public function isMailSendReminder(Model $model, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		if (! $this->__isMailSendCommon($model, $typeKey)) {
			return false;
		}

		// リマインダーの公開以外はメール送らない
		$status = Hash::get($model->data, $model->alias . '.status');
		if ($status != WorkflowComponent::STATUS_PUBLISHED) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// リマインダーが複数日あって、全て日時が過ぎてたら、メール送らない
		$isMailSendReminder = false;
		$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
		foreach ($sendTimeReminders as $sendTime) {
			if ($this->__isMailSendTime($model, $sendTime)) {
				$isMailSendReminder = true;
			}
		}
		if (! $isMailSendReminder) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * メールを送るかどうか - 共通処理
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @return bool
 */
	private function __isMailSendCommon(Model $model, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$siteSetting = $model->SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array('Mail.from')
		));
		$from = Hash::get($siteSetting['Mail.from'], '0.value');

		// Fromが空ならメール未設定のため、メール送らない
		if (empty($from)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// プラグインキー
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$pluginKey = Current::read('Plugin.key');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// コンテンツコメントは使ってくれているプラグインのキーでメール設定取得
			$pluginKey = $model->data[$model->alias]['plugin_key'];
		}

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingPlugin = $model->MailSetting->getMailSettingPlugin(null, $typeKey, $pluginKey);
		$isMailSend = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send');

		// プラグイン設定でメール通知を使わないなら、メール送らない
		if (! $isMailSend) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$status = Hash::get($model->data, $model->alias . '.status');

		// 一時保存はメール送らない
		if ($status == WorkflowComponent::STATUS_IN_DRAFT) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * メール送信日時で送るかどうか
 *
 * @param Model $model モデル
 * @param date $sendTime メール送信日時
 * @return bool
 */
	private function __isMailSendTime(Model $model, $sendTime) {
		if ($sendTime === null) {
			return true;
		}

		// SiteSettingからメール設定を取得する
		$siteSetting = $model->SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array(
				'Mail.use_cron',
			)
		));

		$useCron = Hash::get($siteSetting['Mail.use_cron'], '0.value');
		$now = NetCommonsTime::getNowDatetime();

		// クーロンが使えなくて未来日なら、未来日メールなので送らない
		if (empty($useCron) && strtotime($now) < strtotime($sendTime)) {
			return false;
		}

		$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];

		if (! $useReminder) {
			return true;
		}

		// リマインダーで日時が過ぎてたら、メール送らない
		if (strtotime($now) > strtotime($sendTime)) {
			return false;
		}

		return true;
	}

/**
 * リマインダーでキュー保存
 *
 * @param Model $model モデル
 * @return bool
 */
	private function __saveQueueReminder(Model $model) {
		// リマインダーメールを送るかどうか
		if (! $this->isMailSendReminder($model)) {
			return true;
		}

		// リマインダーは delete->insert
		$contentKey = $this->__getContentKey($model);
		$model->Behaviors->load('Mails.MailQueueDelete');
		/** @see MailQueueDeleteBehavior::deleteQueue() */
		$model->deleteQueue($contentKey);
		// MailQueueDeleteBehaviorはunloadしない。モデル側のactAsで既に、MailQueueDeleteBehavior を読み込んでいる場合、下記エラーが出るため。
		// Notice (8): Undefined index: MailQueueDelete [CORE/Cake/Utility/ObjectCollection.php, line 128]
		// Warning (2): call_user_func_array() expects parameter 1 to be a valid callback, first array member is not a valid class name or object [CORE/Cake/Utility/ObjectCollection.php, line 128]

		$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
		return $this->saveQueue($model, $sendTimeReminders);
	}

/**
 * キュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @param string $typeKey メールの種類
 * @return bool
 */
	public function saveQueue(Model $model, $sendTimes, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$languageId = Current::read('Language.id');
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$status = Hash::get($model->data, $model->alias . '.status');

		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW ||
			$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 承認完了通知メール(即時)
				$this->__saveQueueNoticeMail($model, $languageId, $typeKey);

				// 投稿メール - ルーム配信
				$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				// 承認依頼通知メール(即時)
				$this->__saveQueueNoticeMail($model, $languageId, $typeKey);

			} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
				// --- 差戻し
				// ※ コンテンツコメントは、ここの処理に入ることはない
				// 差戻し通知メール(即時)
				$this->__saveQueueNoticeMail($model, $languageId, $typeKey);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			// --- ワークフローの機能自体、使ってないプラグインの処理
			// --- 公開
			// 投稿メール - ルーム配信
			$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER) {
			// --- 回答
			// 回答メール配信(即時)
			$this->__saveQueueAnswerMail($model, $languageId, $typeKey);
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

		// MailQueueUserは新規登録
		//$mailQueueUser = $model->MailQueueUser->create();
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
					$notSendRoomUserIds = $this->settings[$model->alias]['notSendRoomUserIds'];
					$notSendRoomUserIds = array_unique($notSendRoomUserIds);
					$notSendRoomUserIds = implode('|', $notSendRoomUserIds);
					$mailQueueUser['MailQueueUser']['not_send_room_user_ids'] = $notSendRoomUserIds;
				}

				$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
				/** @see MailQueueUser::saveMailQueueUser() */
				if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				// ルームIDをクリア
				$mailQueueUser['MailQueueUser']['room_id'] = null;

				// 登録者にも配信
				$createdUserId = $this->__getCreatedUserId($model);
				$addUserIds = $this->settings[$model->alias]['userIds'];
				$addUserIds[] = $createdUserId;
				// 登録者と追加ユーザ達の重複登録を排除
				$addUserIds = array_unique($addUserIds);

				// 追加のユーザ達に配信
				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'user_id', $addUserIds);
			}
		}

		return $mailQueueResult['MailQueue']['id'];
	}

/**
 * 回答メール配信(即時) - メールキューSave
 * 登録フォーム、アンケートを想定
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return bool
 * @throws InternalErrorException
 */
	private function __saveQueueAnswerMail(Model $model, $languageId, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$toAddresses = $this->settings[$model->alias]['toAddresses'];
		$userIds = $this->settings[$model->alias]['userIds'];

		if (!empty($toAddresses)) {
			// メールアドレスに配信(即時) - メールキューSave
			$mailQueueId = $this->saveQueuePostMail($model, $languageId, null, null, $toAddresses, $typeKey);
		} elseif (!empty($userIds)) {
			// ユーザIDに配信(即時) - メールキューSave
			$mailQueueId = $this->saveQueuePostMail($model, $languageId, null, $userIds, null, $typeKey);
		}

		// ルーム内の承認者達に配信
		$this->__saveMailQueueUserInRoomsAuthorizer($model, $mailQueueId);

		return true;
	}

/**
 * 登録者に配信
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return void
 * @throws InternalErrorException
 */
	private function __saveMailQueueUserInCreatedUser(Model $model, $mailQueueId) {
		$createdUserId = $this->__getCreatedUserId($model);
		// コンテンツコメントで、参観者まで投稿を許可していると、ログインしていない人もコメント書ける。その時はuser_idなしなので送らない。
		if (empty($createdUserId)) {
			return;
		}

		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$blockKey = Current::read('Block.key');

		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'mail_queue_id' => $mailQueueId,
			'user_id' => $createdUserId,
			'room_id' => null,
			'to_address' => null,
			'not_send_room_user_ids' => null,
		);

		// MailQueueUserは新規登録
		$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias]['notSendRoomUserIds'][] = $createdUserId;
	}

/**
 * ルーム内の承認者達に配信
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return bool
 * @throws InternalErrorException
 */
	private function __saveMailQueueUserInRoomsAuthorizer(Model $model, $mailQueueId) {
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$blockKey = Current::read('Block.key');

		// MailQueueUserは新規登録
		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'mail_queue_id' => $mailQueueId,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
			'not_send_room_user_ids' => null,
		);

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$publishable = 'content_publishable';
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			$publishable = 'content_comment_publishable';
		}

		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($model, $publishable);
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$mailQueueUser['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
			// MailQueueUserは新規登録
			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
			/** @see MailQueueUser::saveMailQueueUser() */
			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
			// ルーム配信で送らないユーザID セット
			$this->settings[$model->alias]['notSendRoomUserIds'][] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
		}
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
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフロー
			// 承認しないなら、承認完了通知メール送らない
			$useWorkflow = $this->__getUseWorkflow($model);
			if (! $useWorkflow) {
				CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				return;
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメント
			// コメント承認しないなら、承認完了通知メール送らない
			$key = Hash::get($this->settings, $model->alias . '.useCommentApproval');
			$useCommentApproval = Hash::get($model->data, $key);
			if (! $useCommentApproval) {
				CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				return;
			}
		}

		// 投稿者がルーム内の承認者だったら、承認完了通知メール送らない
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($model, 'content_publishable');
		$rolesRoomsUserIds = Hash::extract($rolesRoomsUsers, '{n}.RolesRoomsUser.user_id');
		$createdUserId = $this->__getCreatedUserId($model);
		if (in_array($createdUserId, $rolesRoomsUserIds)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
		}

		// 定型文の種類
		$status = Hash::get($model->data, $model->alias . '.status');
		$fixedPhraseType = $this->__getFixedPhraseType($status);

		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey, $fixedPhraseType);
		$mailQueue['MailQueue']['send_time'] = $this->__getSaveSendTime();

		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$mailQueueId = $mailQueueResult['MailQueue']['id'];

		// 登録者に配信
		$this->__saveMailQueueUserInCreatedUser($model, $mailQueueId);
		// ルーム内の承認者達に配信
		$this->__saveMailQueueUserInRoomsAuthorizer($model, $mailQueueId);
	}

/**
 * SiteSettingの定型文の種類 ゲット
 *
 * @param string $status 承認ステータス
 * @return string
 * @throws InternalErrorException
 */
	private function __getFixedPhraseType($status) {
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			// --- 公開
			// 承認完了通知メール
			return NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION;

		} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
			// --- 承認依頼
			// 承認依頼通知メール
			return NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL;

		} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
			// --- 差戻し
			// 差戻し通知メール
			return NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL;
		}
		return null;
	}

/**
 * ルーム内で該当パーミッションありのユーザ ゲット
 *
 * @param Model $model モデル
 * @param string $permission パーミッション
 * @param string $roomId ルームID
 * @return array
 */
	private function __getRolesRoomsUsersByPermission(Model $model, $permission, $roomId = null) {
		if ($roomId === null) {
			$roomId = Current::read('Room.id');
		}

		$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
		//$permissions = $WorkflowComponent->getBlockRolePermissions(array($permission));
		$permissions = $WorkflowComponent->getRoomRolePermissions(array($permission), DefaultRolePermission::TYPE_ROOM_ROLE);
		foreach ($permissions['RoomRolePermission'][$permission] as $key => $roomRolePermission) {
			if (!$roomRolePermission['value']) {
				unset($permissions['RoomRolePermission'][$permission][$key]);
			}
		}

		//$roleKeys = array_keys($permissions['BlockRolePermissions'][$permission]);
		$roleKeys = array_keys($permissions['RoomRolePermission'][$permission]);
		$conditions = array(
			'Room.id' => $roomId,
			'RolesRoom.role_key' => $roleKeys,
		);
		$rolesRoomsUsers = $model->RolesRoomsUser->getRolesRoomsUsers($conditions);
		return $rolesRoomsUsers;
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
		$pluginName = $this->__getPluginName($model);
		$blockKey = Current::read('Block.key');

		// メール生文の作成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$mailAssignTag->initPlugin($languageId, $pluginName);
		if (isset($fixedPhraseType)) {
			$mailAssignTag->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType, $mailSettingPlugin);
		} else {
			$mailAssignTag->setMailFixedPhrasePlugin($mailSettingPlugin);
		}

		// --- 埋め込みタグ
		$url = $mailAssignTag->getXUrl($contentKey);
		$mailAssignTag->assignTag('X-URL', $url);

		// ワークフロー
		if ($model->Behaviors->loaded('Workflow.Workflow')) {
			$workflowComment = $mailAssignTag->getXWorkflowComment($fixedPhraseType, $model->data);
			$mailAssignTag->assignTag('X-WORKFLOW_COMMENT', $workflowComment);
		}

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');

		// タグプラグイン
		$mailAssignTag->assignTag('X-TAGS', '');
		// コメント以外
		if ($model->Behaviors->loaded('Tags.Tag') && $workflowType != self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			$tags = $mailAssignTag->getXTags($model->data);
			$mailAssignTag->assignTag('X-TAGS', $tags);
		}

		// 定型文の埋め込みタグをセット
		$embedTags = Hash::get($this->settings, $model->alias . '.embedTags');
		foreach ($embedTags as $embedTag => $dataKey) {
			$dataValue = Hash::get($model->data, $dataKey);
			$mailAssignTag->assignTag($embedTag, $dataValue);
		}

		// - 追加の埋め込みタグ セット
		// 既にセットされているタグであっても、上書きされる
		foreach ($this->settings[$model->alias]['addEmbedTagsValues'] as $embedTag => $value) {
			$mailAssignTag->assignTag($embedTag, $value);
		}

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
