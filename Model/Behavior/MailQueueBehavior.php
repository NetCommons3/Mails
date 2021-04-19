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

App::uses('ModelBehavior', 'Model');
App::uses('NetCommonsMailAssignTag', 'Mails.Utility');
App::uses('MailSend', 'Mails.Utility');
App::uses('MailSettingFixedPhrase', 'Mails.Model');
App::uses('Current', 'NetCommons.Utility');

/**
 * メールキュー Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MailQueueBehavior extends ModelBehavior {

/**
 * 承認機能の種類
 *
 * @var string 承認機能なし
 * @var string ワークフロー
 * @var string コンテンツコメント
 * @var string 回答（アンケート、登録フォーム等）
 * @var string グループのみ（回覧板、カレンダー(プライベート予定のグループ共有)）
 */
	const
		MAIL_QUEUE_WORKFLOW_TYPE_NONE = 'none',
		MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW = 'workflow',
		MAIL_QUEUE_WORKFLOW_TYPE_COMMENT = 'contentComment',
		MAIL_QUEUE_WORKFLOW_TYPE_ANSWER = 'answer',
		MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY = 'groupOnly';

/**
 * セッティングの種類(setSettingで利用)
 *
 * @var string 任意で送信するユーザID（グループ送信（回覧板、カレンダー等）、アンケートを想定）
 * @var string 任意で送信するメールアドレス（登録フォーム等を想定）
 * @var string 投稿メールのON, OFF（回覧板、カレンダー等を想定）
 * @var string ルーム配信で送らないユーザID
 * @var string プラグイン名
 * @var string 承認機能の種類
 * @var string 末尾定型文
 */
	const
		MAIL_QUEUE_SETTING_USER_IDS = 'userIds',
		MAIL_QUEUE_SETTING_TO_ADDRESSES = 'toAddresses',
		MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST = 'isMailSendPost',
		MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS = 'notSendRoomUserIds',
		MAIL_QUEUE_SETTING_PLUGIN_NAME = 'pluginName',
		MAIL_QUEUE_SETTING_WORKFLOW_TYPE = 'workflowType',
		MAIL_QUEUE_SETTING_MAIL_BODY_AFTER = 'mailBodyAfter';

/**
 * ビヘイビアの初期設定
 *
 * @var array
 */
	protected $_defaultSettings = array(
		'embedTags' => array(
			'X-SUBJECT' => null,
			'X-BODY' => null,
		),
		'addEmbedTagsValues' => array(),
		'embedTagsWysiwyg' => array(),
		'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
		'keyField' => 'key',
		'editablePermissionKey' => 'content_editable',
		'publishablePermissionKey' => 'content_publishable',
		//'useWorkflow' => null,
		'publishStartField' => null,
		'pluginKey' => null,
		'reminder' => array(
			'sendTimes' => null,
			'useReminder' => 0,
		),
		self::MAIL_QUEUE_SETTING_PLUGIN_NAME => null,
		self::MAIL_QUEUE_SETTING_USER_IDS => array(),
		self::MAIL_QUEUE_SETTING_TO_ADDRESSES => null,
		self::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST => null,
		self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS => array(),
		self::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER => '',
	);

/**
 * @var array メール設定データ配列
 */
	protected $_mailSettingPlugin = null;

/**
 * コンテンツ編集前の作成者・更新者を保持(ワークフローで使用するため)
 *
 * @var array
 */
	protected $_dataBeforeModified = [];

/**
 * setup
 *
 * #### サンプルコード
 * ##### Model
 * ```php
 * public $actsAs = array(
 *	'Mails.MailQueue' => array(
 *		'embedTags' => array(
 *			'X-SUBJECT' => 'Video.title',
 *			'X-BODY' => 'Video.description',
 *		),
 * 		// アンケート回答、登録フォーム回答時は指定
 * 		//'workflowType' => MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER,
 * 		// アンケートの未来公開日は指定
 * 		//'publishStartField' => 'answer_start_period',
 * 		// 動画のような{X-BODY}がウィジウィグでない時は指定
 * 		//'embedTagsWysiwyg' => array(),
 * 		// FAQのような{X-BODY}でない箇所がウィジウィグの時に指定
 * 		//'embedTagsWysiwyg' => array('X-ANSWER'),
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
		$workflowTypeKey = self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE;

		// --- 設定ないパラメータの処理
		if (!isset($this->settings[$model->alias][$workflowTypeKey])) {
			// --- ワークフローのstatusによって送信内容を変える
			if ($model->Behaviors->loaded('Workflow.Workflow')) {
				$this->settings[$model->alias][$workflowTypeKey] = self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW;
			} else {
				$this->settings[$model->alias][$workflowTypeKey] = self::MAIL_QUEUE_WORKFLOW_TYPE_NONE;
			}
		}
		// メール定型文の種類
		if (!isset($this->settings[$model->alias]['typeKey'])) {
			if ($this->settings[$model->alias][$workflowTypeKey] == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER) {
				// 回答タイプ
				$this->settings[$model->alias]['typeKey'] = MailSettingFixedPhrase::ANSWER_TYPE;
			}
		}
		// 埋め込みタグのウィジウィグ対象（メール送信プレーンテキストの時、strap_tagsされる対象）
		if (!isset($this->settings[$model->alias]['embedTagsWysiwyg'])) {
			$this->settings[$model->alias]['embedTagsWysiwyg'] = array('X-BODY');
		}
		$this->_defaultSettings['pluginKey'] = Current::read('Plugin.key');
		$this->_defaultSettings[self::MAIL_QUEUE_SETTING_PLUGIN_NAME] = Current::read('Plugin.Name');
		$this->settings[$model->alias] =
			Hash::merge($this->_defaultSettings, $this->settings[$model->alias]);

		$model->loadModels([
			'MailSetting' => 'Mails.MailSetting',
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
			'SiteSetting' => 'SiteManager.SiteSetting',
		]);
	}

/**
 * beforeSave is called before a model is saved. Returning false from a beforeSave callback
 * will abort the save operation.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False if the operation should abort. Any other result will continue.
 * @see Model::save()
 */
	public function beforeSave(Model $model, $options = array()) {
		$this->beforeSaveQueue($model);
		return true;
	}

/**
 * saveQueue() の前に呼び出す。基本beforeSave()で呼び出されるが、
 * 手動でsaveQueue()を呼び出すときは、このメソッドを呼び出すこと
 *
 * @param Model $model Model using this behavior
 * @return void
 */
	public function beforeSaveQueue(Model $model) {
		if (isset($this->settings[$model->alias][self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE])) {
			$workflowType = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE];
		} else {
			$workflowType = null;
		}

		$workflowTypeCheck = array(
			self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW,
			self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
		);

		if (in_array($workflowType, $workflowTypeCheck, true)) {
			//ワークフローは、keyカラムがあることが前提
			$contentKeyField = $this->settings[$model->alias]['keyField'];
			if (! $model->hasField($contentKeyField) ||
					! isset($model->data[$model->alias][$contentKeyField])) {
				return true;
			}

			$contentKeyValue = $model->data[$model->alias]['key'];
			$conditions = [
				$contentKeyField => $contentKeyValue,
			];
			if ($model->hasField('block_id')) {
				//ブログ等はブロックIDにインデックスが張られているため、
				//それを使うようにするために条件に追加する
				$conditions['block_id'] = Current::read('Block.id');
			}

			$modified = $model->find('first', array(
				'recursive' => -1,
				'fields' => array('created_user', 'modified_user'),
				'conditions' => $conditions,
				'order' => [$model->primaryKey => 'desc'],
				'callbacks' => false,
			));

			if ($modified) {
				$this->_dataBeforeModified[$model->alias][$contentKeyValue] = $modified[$model->alias];
			}
		}

		return true;
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
		$contentKey = $this->__getContentKey($model);
		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);

		$workflowTypeCheck = array(
			self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
			self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER,
		);
		if (!in_array($workflowType, $workflowTypeCheck, true)) {
			// 未来日系の送信日時更新を考慮して delete->insert
			// コンテンツコメントは、同じ動画に複数コメントしてもコンテンツキー同じで消されると困る＆未来日系ありえないため、除外
			// 回答も未来日系ありえないため、除外
			$model->Behaviors->load('Mails.MailQueueDelete', $this->settings[$model->alias]);
			/** @see MailQueueDeleteBehavior::deleteQueue() */
			$model->deleteQueue($contentKey);
			// MailQueueDeleteBehaviorはunloadしない。モデル側のactAsで既に、MailQueueDeleteBehavior を読み込んでいる場合、下記エラーが出るため。
			// Notice (8): Undefined index: MailQueueDelete [CORE/Cake/Utility/ObjectCollection.php, line 128]
			// Warning (2): call_user_func_array() expects parameter 1 to be a valid callback, first array member is not a valid class name or object [CORE/Cake/Utility/ObjectCollection.php, line 128]
			$model->Behaviors->disable('Mails.MailQueueDelete');
		}

		$model->Behaviors->load('Mails.IsMailSend', $this->settings[$model->alias]);
		$typeKey = $this->settings[$model->alias]['typeKey'];

		// --- リマインダー
		/** @see IsMailSendBehavior::isMailSendReminder() */
		if ($model->isMailSendReminder()) {
			$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
			$this->saveQueue($model, $sendTimeReminders, $typeKey);
		}

		$sendTimePublish = $this->__getSendTimePublish($model);
		$settingPluginKey = $this->__getSettingPluginKey($model);

		// --- 通常メール
		/** @see IsMailSendBehavior::isMailSend() */
		if ($model->isMailSend($typeKey, $contentKey,
				$settingPluginKey)) {
			$this->saveQueue($model, array($sendTimePublish), $typeKey);

			// キューからメール送信
			MailSend::send();
		}
		$model->Behaviors->unload('Mails.IsMailSend');

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
 * @return array $this->settings(テスト用)
 */
	public function setAddEmbedTagValue(Model $model, $embedTag, $value) {
		$this->settings[$model->alias]['addEmbedTagsValues'][$embedTag] = $value;
		return $this->settings;
	}

/**
 * セッティング セット
 *
 * #### サンプルコード
 * ##### Model
 * ```php
 *	public function saveVideo($data) {
 *		$this->begin();
 *
 *		$this->set($data);
 *		if (! $this->validates()) {
 *			return false;
 *		}
 *
 *		try {
 *			// 試し：投稿メールのOFF セット(カレンダー、回覧板等)
 *			//$this->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST, 0);
 *			// 試し：メールアドレス セット(登録フォーム回答)
 *			//			$toAddresses = array(
 *			//				'test1@example.com',
 *			//				'test2@example.com',
 *			//			);
 *			//			$this->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_TO_ADDRESSES, $toAddresses);
 *			// 試し：ユーザID セット(アンケート設置者)、ユーザID複数でグループ配信（回覧板、カレンダー）
 *			//			$userIds = array(
 *			//				4,
 *			//				5,
 *			//			);
 *			//			$this->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS, $userIds);
 *			// 試し：グループ配信のみ（回覧板、カレンダー(プライベート予定のグループ共有)）
 *			//			$this->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE,
 *			//				MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY);
 *			// 試し：TO_ADDRESSESには表示しない（ルーム配信のみ表示）末尾定型文を追加（登録フォーム回答）
 *			//			$this->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER,
 *			//				__d('videos', 'mail_fixed_phrase_body_after'));
 *
 *			if (! $video = $this->save(null, false)) {
 *				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
 *			}
 *			$this->commit();
 *
 *		} catch (Exception $ex) {
 *			$this->rollback($ex);
 *		}
 *		return $video;
 *	}
 * ```
 *
 * @param Model $model モデル
 * @param string $settingKey セッティングのキー
 * @param string|array $settingValue セッティングの値
 * @return array $this->settings(テスト用)
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_TO_ADDRESSES
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_PLUGIN_NAME
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE
 * @see MailQueueBehavior::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER
 */
	public function setSetting(Model $model, $settingKey, $settingValue) {
		if ($settingKey === self::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER) {
			$settingValue = "\n\n" . $settingValue;
		}
		$this->settings[$model->alias][$settingKey] = $settingValue;
		return $this->settings;
	}

/**
 * self::$_mailSettingPlugin にメール設定をセットする
 *
 * @param Model $model Model
 * @param int|string $languageId Language.id
 * @param string $typeKey answer|contents
 * @param string $pluginKey プラグインキー
 * @param array $mailSetting セットするメール設定値
 * @return void
 */
	public function setMailSettingPlugin(
		Model $model,
		$languageId,
		string $typeKey,
		string $pluginKey,
		array $mailSetting
	) {
		$this->_mailSettingPlugin[$languageId][$typeKey][$pluginKey] = $mailSetting;
	}

/**
 * リマインダー送信日時 セット
 *
 * #### サンプルコード
 * ##### Model
 * ```php
 *	public function saveVideo($data) {
 *		$this->begin();
 *
 *		$this->set($data);
 *		if (! $this->validates()) {
 *			return false;
 *		}
 *
 *		try {
 *			// 試し：リマインダー(カレンダー等)
 *			// 送信条件：site_settings['Mail.use_cron'] => 1
 *			$netCommonsTime = new NetCommonsTime();
 *			$sendTimes = array(
 *				$netCommonsTime->toServerDatetime('2017-03-31 14:30:00'),
 *				$netCommonsTime->toServerDatetime('2017-04-20 13:30:00'),
 *			);
 *			$this->setSendTimeReminder($sendTimes);
 *
 *			if (! $video = $this->save(null, false)) {
 *				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
 *			}
 *			$this->commit();
 *
 *		} catch (Exception $ex) {
 *			$this->rollback($ex);
 *		}
 *		return $video;
 *	}
 * ```
 *
 * @param Model $model モデル
 * @param array $sendTimeReminders リマインダー送信日時 配列
 * @return array $this->settings(テスト用)
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
			return null;
		}

		$this->settings[$model->alias]['reminder']['sendTimes'] = $sendTimeReminders;
		$this->settings[$model->alias]['reminder']['useReminder'] = 1;
		return $this->settings;
	}

/**
 * 公開するメール送信日時 ゲット
 *
 * @param Model $model モデル
 * @return date 送信日時
 */
	private function __getSendTimePublish(Model $model) {
		// DBに項目があり期限付き公開の時のみ、公開日時を取得する（ブログを想定）。その後、未来日メール送られる
		if ($model->hasField(['public_type', 'publish_start']) &&
			$model->data[$model->alias]['public_type'] == WorkflowBehavior::PUBLIC_TYPE_LIMITED) {
			return $model->data[$model->alias]['publish_start'];
		}

		$publishStartField = Hash::get($this->settings, $model->alias . '.publishStartField');
		if (is_null($publishStartField)) {
			return null;
		}

		// DBに指定の項目があったら公開日時を取得する（アンケートを想定）。その後、未来日メール送られる
		return $model->data[$model->alias][$publishStartField];
	}

/**
 * プラグインのメール設定(定型文等) 取得
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	private function __getMailSettingPlugin(Model $model, $languageId,
											$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$settingPluginKey = $this->__getSettingPluginKey($model);
		if (!isset($this->_mailSettingPlugin[$languageId][$typeKey][$settingPluginKey])) {
			/** @see MailSetting::getMailSettingPlugin() */
			$this->_mailSettingPlugin[$languageId][$typeKey][$settingPluginKey] =
				$model->MailSetting->getMailSettingPlugin($languageId, $typeKey, $settingPluginKey);
		}
		return $this->_mailSettingPlugin[$languageId][$typeKey][$settingPluginKey];
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
		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			return $model->data[$model->alias]['plugin_key'];
		}
		// 通常
		return Current::read('Plugin.key');
	}

/**
 * キュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @param string $typeKey メールの種類
 * @return void
 */
	public function saveQueue(Model $model, $sendTimes = null,
								$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$model->Behaviors->load('Mails.IsMailSend', $this->settings[$model->alias]);

		$languageId = Current::read('Language.id');
		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		$userIds = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_USER_IDS];
		$toAddresses = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_TO_ADDRESSES];
		$roomId = Current::read('Room.id');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY) {
			// グループ配信は、ルーム配信しない
			$roomId = null;
		}

		$workflowTypeCheck = array(
			self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW,
			self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
		);
		if (in_array($workflowType, $workflowTypeCheck, true)) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			// 承認依頼通知, 差戻し通知, 承認完了通知メール(即時)
			$this->__saveQueueNoticeMail($model, $languageId, $typeKey);

			$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);
			$isMailSend = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send');
			$contentKey = $this->__getContentKey($model);

			/** @see IsMailSendBehavior::isSendMailQueuePublish() */
			if (! $model->isSendMailQueuePublish($isMailSend, $contentKey)) {
				return;
			}

			// 投稿メール - ルーム配信
			$this->saveQueuePostMail($model, $languageId, $sendTimes, $userIds, $toAddresses,
				$roomId, $typeKey);

		} else {
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE ||
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER ||
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY) {
			// ・承認機能なし - 「公開」記事の内容を投稿メールでルーム配信
			// ・回答メール配信(即時) - ユーザID、メールアドレス、ルームに即時配信
			// ・グループ送信のみ - ユーザIDに配信

			// メールキューSave
			$this->saveQueuePostMail($model, $languageId, $sendTimes, $userIds, $toAddresses,
				$roomId, $typeKey);
		}
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
 * @param int $roomId ルームID
 * @param string $typeKey メールの種類
 * @return void
 * @throws InternalErrorException
 */
	public function saveQueuePostMail(Model $model, $languageId, $sendTimes = null, $userIds = null,
										$toAddresses = null, $roomId = null, $typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$model->Behaviors->load('Mails.IsMailSend', $this->settings[$model->alias]);
		if ($sendTimes === null) {
			$sendTimes[] = $model->MailQueue->getSaveSendTime();
		}
		// 末尾定型文 なし
		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey);
		// 末尾定型文 あり
		$fixedPhraseBodyAfter =
			$this->settings[$model->alias][self::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER];
		$mailQueueBodyAfter =
			$this->__createMailQueue($model, $languageId, $typeKey, null, $fixedPhraseBodyAfter);

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
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
		);

		// 以下、実行する時は、公開時を想定

		foreach ($sendTimes as $sendTime) {

			/** @see IsMailSendBehavior::isMailSendTime() */
			// cron使えず未来日メールなら、送らない
			if (! $model->isMailSendTime($sendTime)) {
				CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				return;
			}

			$sendTime = $model->MailQueue->getSaveSendTime($sendTime);

			if (!empty($userIds)) {
				// メール内容save
				$mailQueueUser['MailQueueUser']['mail_queue_id'] =
					$this->__saveMailQueue($model, $mailQueue, $sendTime);

				// --- ユーザIDに配信
				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'user_id', $userIds);
			}
			if (!empty($toAddresses)) {
				// メール内容save
				$mailQueueUser['MailQueueUser']['mail_queue_id'] =
					$this->__saveMailQueue($model, $mailQueue, $sendTime);

				// --- メールアドレスに配信
				/** @see MailQueueUser::addMailQueueUsers() */
				$model->MailQueueUser->addMailQueueUsers($mailQueueUser, 'to_address', $toAddresses);

			}
			if (!empty($roomId)) {
				// メール内容save - 末尾定型文あり
				$mailQueueUser['MailQueueUser']['mail_queue_id'] =
					$this->__saveMailQueue($model, $mailQueueBodyAfter, $sendTime);

				// --- ルーム配信
				// ルーム配信で送らないユーザID
				$key = self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS;
				$notSendRoomUserIds = $this->settings[$model->alias][$key];
				$notSendRoomUserIds = Hash::merge($notSendRoomUserIds, $userIds);
				$this->settings[$model->alias][$key] = $notSendRoomUserIds;

				// 登録者に配信
				$this->__addMailQueueUserInCreatedUser($model,
					$mailQueueUser['MailQueueUser']['mail_queue_id']);

				// 登録者に配信で、ルーム配信で送らないユーザIDをセットしているので、再取得
				$notSendRoomUserIds = $this->settings[$model->alias][$key];

				// ルーム配信で送るパーミッション
				/** @see MailQueueUser::getSendRoomPermission() */
				$sendRoomPermission = $model->MailQueueUser->getSendRoomPermission($typeKey);

				// ルーム配信
				/** @see MailQueueUser::addMailQueueUserInRoom() */
				$model->MailQueueUser->addMailQueueUserInRoom($roomId, $mailQueueUser,
					$sendTime, $notSendRoomUserIds, $sendRoomPermission);
			}
		}
	}

/**
 * メール内容save
 *
 * @param Model $model モデル
 * @param array $mailQueue メールキュー
 * @param string $sendTime 送信日時
 * @return int MailQueue.id
 * @throws InternalErrorException
 */
	private function __saveMailQueue(Model $model, $mailQueue, $sendTime) {
		$mailQueue['MailQueue']['send_time'] = $sendTime;
		$mailQueue = $model->MailQueue->create($mailQueue);
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException('Failed ' . __METHOD__);
		}
		return $mailQueueResult['MailQueue']['id'];
	}

/**
 * 登録者に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return void
 */
	private function __addMailQueueUserInCreatedUser(Model $model, $mailQueueId) {
		$contentKey = $this->__getContentKey($model);

		$mailUserIds = [];
		if (isset($this->_dataBeforeModified[$model->alias][$contentKey])) {
			$mailUserIds[] =
				$this->_dataBeforeModified[$model->alias][$contentKey]['created_user'];
			$mailUserIds[] =
				$this->_dataBeforeModified[$model->alias][$contentKey]['modified_user'];
		}
		if (isset($model->data[$model->alias]['created_user'])) {
			$mailUserIds[] = $model->data[$model->alias]['created_user'];
		}
		if (isset($model->data[$model->alias]['modified_user'])) {
			$mailUserIds[] = $model->data[$model->alias]['modified_user'];
		}

		$notSendKey = self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS;
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		foreach ($mailUserIds as $createdUserId) {
			// ルーム配信で送らないユーザID にセット済みであれば、既に登録者に配信セット済みのため、セットしない
			$notSendRoomUserIds = $this->settings[$model->alias][$notSendKey];
			if (in_array($createdUserId, $notSendRoomUserIds)) {
				continue;
			}

			/** @see MailQueueUser::addMailQueueUserInCreatedUser() */
			$model->MailQueueUser->addMailQueueUserInCreatedUser($mailQueueId, $createdUserId, $contentKey,
				$pluginKey);

			// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
			// ルーム配信で送らないユーザID セット
			$this->settings[$model->alias][$notSendKey][] = $createdUserId;
		}
	}

/**
 * ルーム内の編集者、承認者達に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return bool
 */
	private function __addMailQueueUserInRoomAuthorizers(Model $model, $mailQueueId) {
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$permissionKey = $this->settings[$model->alias]['editablePermissionKey'];

		// 既に登録者に配信セット済みの人には送らない
		$notSendKey = self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS;
		$notSendRoomUserIds = $this->settings[$model->alias][$notSendKey];

		//// 編集者達(編集許可ありユーザ)
		///** @see MailQueueUser::addMailQueueUserInRoomByPermission() */
		//$notSendRoomUserIds = $model->MailQueueUser->addMailQueueUserInRoomByPermission($mailQueueId,
		//	$contentKey, $pluginKey, $permissionKey, $notSendRoomUserIds);

		// 承認者達(公開許可ありユーザ)
		$permissionKey = $this->settings[$model->alias]['publishablePermissionKey'];
		$notSendRoomUserIds = $model->MailQueueUser->addMailQueueUserInRoomByPermission($mailQueueId,
			$contentKey, $pluginKey, $permissionKey, $notSendRoomUserIds);

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias][$notSendKey] =
			Hash::merge($this->settings[$model->alias][$notSendKey], $notSendRoomUserIds);
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
	private function __saveQueueNoticeMail(Model $model, $languageId,
											$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);

		$isMailSendApproval = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send_approval');
		$modifiedUserId = Hash::get($model->data, $model->alias . '.modified_user');
		$settingPluginKey = $this->__getSettingPluginKey($model);

		/** @see IsMailSendBehavior::isSendMailQueueNotice() */
		if (! $model->isSendMailQueueNotice($isMailSendApproval, $modifiedUserId, $settingPluginKey)) {
			return;
		}

		// 承認コメント
		$comment = Hash::get($model->data, 'WorkflowComment.comment');
		$contentKey = $this->__getContentKey($model);
		/** @see IsMailSendBehavior::isPublishableEdit() */
		$isPublishableEdit = $model->isPublishableEdit($contentKey);

		// 定型文の種類
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = Hash::get($model->data, $model->alias . '.status');
		$fixedPhraseType = $mailAssignTag->getFixedPhraseType($status, $comment, $isPublishableEdit);

		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey, $fixedPhraseType);
		$mailQueue['MailQueue']['send_time'] = $model->MailQueue->getSaveSendTime();

		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException('Failed ' . __METHOD__);
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
 * @param string $fixedPhraseBodyAfter 末尾定型文
 * @return array メールキューデータ
 */
	private function __createMailQueue(Model $model,
										$languageId,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
										$fixedPhraseType = null,
										$fixedPhraseBodyAfter = '') {
		$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);
		$replyTo = Hash::get($mailSettingPlugin, 'MailSetting.reply_to');
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$pluginName = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_PLUGIN_NAME];
		$blockKey = Current::read('Block.key');

		// メール生文の作成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$mailAssignTag->initPlugin($languageId, $pluginName);
		$mailAssignTag->setMailFixedPhrase($languageId, $fixedPhraseType, $mailSettingPlugin);

		// 埋め込みタグのウィジウィグ対象
		$mailAssignTag->embedTagsWysiwyg = $this->settings[$model->alias]['embedTagsWysiwyg'];

		// 末尾定型文
		$mailAssignTag->setFixedPhraseBody($mailAssignTag->fixedPhraseBody . $fixedPhraseBodyAfter);

		// --- 埋め込みタグ
		$embedTags = $this->settings[$model->alias]['embedTags'];
		$xUrl = Hash::get($embedTags, 'X-URL', array());
		$mailAssignTag->setXUrl($contentKey, $xUrl);
		if (is_array($xUrl)) {
			$embedTags = Hash::remove($embedTags, 'X-URL');
		}

		$createdUserId = Hash::get($model->data, $model->alias . '.created_user');
		$mailAssignTag->setXUser($createdUserId);

		// ワークフロー
		$useWorkflowBehavior = $model->Behaviors->loaded('Workflow.Workflow');
		$mailAssignTag->setXWorkflowComment($model->data, $fixedPhraseType, $useWorkflowBehavior);

		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		$useTagBehavior = $model->Behaviors->loaded('Tags.Tag');

		// タグプラグイン
		$mailAssignTag->setXTags($model->data, $workflowType, $useTagBehavior);

		// 定型文の埋め込みタグをセット
		$mailAssignTag->assignTagDatas($embedTags, $model->data);

		// - 追加の埋め込みタグ セット
		// 既にセットされているタグであっても、上書きされる
		$mailAssignTag->assignTags($this->settings[$model->alias]['addEmbedTagsValues']);

		// 埋め込みタグ変換：メール定型文の埋め込みタグを変換して、メール生文にする
		$mailAssignTag->assignTagReplace();

		// メール本文の共通ヘッダー文、署名追加
		$mailAssignTag->fixedPhraseBody =
			$mailAssignTag->addHeaderAndSignature($mailAssignTag->fixedPhraseBody);

		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'reply_to' => $replyTo,
			'mail_subject' => $mailAssignTag->fixedPhraseSubject,
			'mail_body' => $mailAssignTag->fixedPhraseBody,
			'send_time' => null,
		);

		// MailQueueは新規登録
		$mailQueue = $model->MailQueue->create($mailQueue);
		return $mailQueue;
	}
}
