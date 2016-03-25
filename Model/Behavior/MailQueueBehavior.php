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

App::uses('NetCommonsMail', 'Mails.Utility');
App::uses('MailSetting', 'Mails.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');

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

		// priorityを下記と モデルの$actsAs で設定したけど、機能しなかった。 http://book.cakephp.org/2.0/ja/core-libraries/collections.html#id6
		//$this->settings[$model->alias]['priority'] = 15;

		// --- 設定ないパラメータの処理
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType === null) {
			// --- ワークフローのstatusによって送信内容を変える
			if ($model->Behaviors->loaded('Workflow.Workflow')) {
				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW;
			} else {
				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_NONE;
			}
		}

		//$this->settings[$model->alias]['mailSendTime'] = null;
		$this->settings[$model->alias]['addEmbedTagsValues'] = null;
		$this->settings[$model->alias]['addUserIds'] = null;
		$this->settings[$model->alias]['isMailSendPost'] = null;
		$this->settings[$model->alias]['beforeStatus'] = null;
		$this->settings[$model->alias]['isEdit'] = null;
		$this->settings[$model->alias]['reminder']['sendTimes'] = null;
		$this->settings[$model->alias]['reminder']['useReminder'] = 0; // リマインダー使わない
		$this->settings[$model->alias]['registration']['toAddresses'] = null;

		$model->loadModels([
			'MailSetting' => 'Mails.MailSetting',
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
			'SiteSetting' => 'SiteManager.SiteSetting',
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
		]);
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
 * 追加で送信するユーザID セット
 * グループ送信（回覧板、カレンダー等）を想定
 *
 * @param Model $model モデル
 * @param array $userIds ユーザID配列
 * @return void
 */
	public function setAddUserIds(Model $model, $userIds) {
		$this->settings[$model->alias]['addUserIds'] = $userIds;
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

	///**
	// * 追加で送信するメールアドレス セット
	// *
	// * @param Model $model モデル
	// * @param array $toAddresses メールアドレス 配列
	// * @return void
	// */
	//	public function setAddToAddresses(Model $model, $toAddresses) {
	//		$this->settings[$model->alias]['addToAddresses'] = $toAddresses;
	//	}

/**
 * 登録フォームで送信するメールアドレス セット
 *
 * @param Model $model モデル
 * @param array $toAddresses メールアドレス 配列
 * @return void
 */
	public function setRegistrationToAddresses(Model $model, $toAddresses) {
		$this->settings[$model->alias]['registration']['toAddresses'] = $toAddresses;
		// 承認機能の種類 セット
		$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER;
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
	private function __getSaveSendTime($sendTime) {
		return isset($sendTime) ? $sendTime : NetCommonsTime::getNowDatetime();
	}

/**
 * コンテンツキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getContentKey(Model $model) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// コンテンツコメント
			return $model->data[$model->alias]['content_key'];
		}
		// 通常
		return $model->data[$model->alias]['key'];
	}

/**
 * プラグインキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getPluginKey(Model $model) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// コンテンツコメントは pluginsテーブルに登録なしで Current::read('Plugin') とれないため、ここで直セット
			return 'content_comments';
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
		// --- リマインダー利用する
		if ($useReminder) {
			$this->__saveQueueReminder($model);
		}

		// --- 周知メール
		// メールを送るかどうか
		if (! $this->isMailSend($model)) {
			return true;
		}

		$sendTime = $this->__getSendTimePublish($model);
		$this->saveQueue($model, array($sendTime));

		return true;
	}

/**
 * メールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @return bool
 */
	public function isMailSend(Model $model, $typeKey = MailSetting::DEFAULT_TYPE) {
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSetting = $model->MailSetting->getMailSettingPlugin(null, $typeKey);
		$isMailSend = Hash::get($mailSetting, 'MailSetting.is_mail_send');

		// プラグイン設定でメール通知を使わないなら、メール送らない
		if (! $isMailSend) {
			return false;
		}

		// --- 編集フラグセット
		$isEdit = $this->settings[$model->alias]['isEdit'];
		if ($isEdit === null) {
			$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
			if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
				// コンテンツコメント
				$conditions = array($model->alias . '.content_key' => $model->data[$model->alias]['content_key']);
			} else {
				// 通常
				$conditions = array($model->alias . '.key' => $model->data[$model->alias]['key']);
			}
			$data = $model->find('all', array(
				'recursive' => -1,
				'conditions' => $conditions,
				'order' => array($model->alias . '.modified DESC'),
				'callbacks' => false,
			));

			// keyに対して2件以上記事がある = 編集
			if (count($data) >= 2) {
				$this->settings[$model->alias]['isEdit'] = 1;
				$isEdit = 1;
				// 1つ前のコンテンツのステータス
				$this->settings[$model->alias]['beforeStatus'] = $data[1][$model->alias]['status'];
			} else {
				$this->settings[$model->alias]['isEdit'] = 0;
				$isEdit = 0;
			}
		}

		$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];
		$status = Hash::get($model->data, $model->alias . '.status');

		if ($useReminder) {
			// --- リマインダー
			// リマインダーが複数日あって、全て日時が過ぎてたら、メール送らない
			$isMailSendReminder = false;
			$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
			foreach ($sendTimeReminders as $sendTime) {
				if ($this->__isMailSendTime($model, $sendTime)) {
					$isMailSendReminder = true;
				}
			}
			if (! $isMailSendReminder) {
				return false;
			}

		} else {
			// --- 通常の投稿
			// 投稿メールOFFなら、メール送らない
			$isMailSendPost = $this->settings[$model->alias]['isMailSendPost'];
			if (isset($isMailSendPost) && !$isMailSendPost) {
				return false;
			}

			// 公開日時
			$sendTime = $this->__getSendTimePublish($model);
			if (! $this->__isMailSendTime($model, $sendTime)) {
				return false;
			}

			// 投稿メールフラグが未設定の場合のみ処理（カレンダー、回覧板のメール通知を想定）
			if ($isEdit && $isMailSendPost === null) {
				// 承認ONでもOFFでも、公開中の記事を編集して、公開だったら、メール送らない
				// ・承認ONで、承認者が公開中の記事を編集しても、メール送らない
				// ・承認OFFで、公開中の記事を編集しても、メール送らない
				// ・・公開中の記事（１つ前の記事のstatus=1）
				// ・・編集した記事が公開（status=1）
				// ※承認ONで公開中の記事を編集して、編集した記事が公開なのは、承認者だけ
				$beforeStatus = $this->settings[$model->alias]['beforeStatus'];
				if ($beforeStatus == WorkflowComponent::STATUS_PUBLISHED && $status == WorkflowComponent::STATUS_PUBLISHED) {
					return false;
				}
			}
		}

		// 一時保存はメール送らない
		if ($status == WorkflowComponent::STATUS_IN_DRAFT) {
			return false;
		}

		// リマインダーの公開以外はメール送らない
		if ($useReminder && $status != WorkflowComponent::STATUS_PUBLISHED) {
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
		// メールを送るかどうか
		if (! $this->isMailSend($model)) {
			return true;
		}

		// リマインダーは delete->insert
		$contentKey = $this->__getContentKey($model);
		$model->Behaviors->load('Mails.MailQueueDelete');
		/** @see MailQueueDeleteBehavior::deleteQueue() */
		$model->deleteQueue($contentKey);
		// 暫定対応: 下記エラーができるため、コメントアウト。後処理で使わないので、本当はunloadしたい。
		// Notice (8): Undefined index: MailQueueDelete [CORE/Cake/Utility/ObjectCollection.php, line 128]
		// Warning (2): call_user_func_array() expects parameter 1 to be a valid callback, first array member is not a valid class name or object [CORE/Cake/Utility/ObjectCollection.php, line 128]
		// $model->Behaviors->unload('Mails.MailQueueDelete');

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
	public function saveQueue(Model $model, $sendTimes, $typeKey = MailSetting::DEFAULT_TYPE) {
		$languageId = Current::read('Language.id');
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$status = Hash::get($model->data, $model->alias . '.status');

		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信 - メールキューSave
				$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);

				// 承認完了通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, $typeKey);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				// 承認依頼メール - 登録者と承認者に配信(即時) - メールキューSave
				$this->__saveQueueApprovalMail($model, $languageId, 'content_publishable', $typeKey);

			} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
				// --- 差戻し
				// 差戻し通知メール - 登録者に配信(即時) - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, $typeKey);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメント
			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信(即時) - メールキューSave
				$this->saveQueuePostMail($model, $languageId, $sendTimes);

				// 承認完了通知メール - 登録者に配信(即時) - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				// 承認依頼メール - 登録者と承認者に配信(即時) - メールキューSave
				$this->__saveQueueApprovalMail($model, $languageId, 'content_comment_publishable');
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			// --- ワークフローの機能自体、使ってないプラグインの処理
			// --- 公開
			// 投稿メール - ルーム配信 - メールキューSave
			$this->saveQueuePostMail($model, $languageId, $sendTimes, null, null, $typeKey);

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER) {
			// --- 登録フォーム
			$this->__saveQueuePostMailByToAddress($model, $typeKey);
		}

		return true;
	}

/**
 * 投稿メール - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param array $sendTimes メール送信日時 配列
 * @param int $userId 送信ユーザID
 * @param string $toAddresses 送信先メールアドレス
 * @param string $typeKey メールの種類
 * @return array メールキューデータ
 * @throws InternalErrorException
 */
	public function saveQueuePostMail(Model $model, $languageId, $sendTimes = null, $userId = null, $toAddresses = null, $typeKey = MailSetting::DEFAULT_TYPE) {
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $model->MailSetting->getMailSettingPlugin($languageId, $typeKey);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		if (empty($replyTo)) {
			$replyTo = null;
		}
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->__getPluginKey($model);
		$pluginName = $this->__getPluginName($model);
		$blockKey = Current::read('Block.key');

		// 投稿メール - メールキューSave
		$postMail = new NetCommonsMail();
		$postMail->initPlugin($languageId, $pluginName);
		$postMail->setMailFixedPhrasePlugin($mailSettings);
		$postMail->setReplyTo($replyTo);
		$postMail = $this->__convertPlainText($model, $postMail);

		//$replyTo = key($postMail->replyTo());
		// MailQueueは新規登録
		//$mailQueue = $model->MailQueue->create();
		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'replay_to' => $replyTo,
			'mail_subject' => $postMail->subject,
			'mail_body' => $postMail->body,
			'send_time' => null,
		);

		// MailQueueUserは新規登録
		//$mailQueueUser = $model->MailQueueUser->create();
		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
		);

		if (isset($userId)) {
			// 登録者に配信
			// ここを実行する時は、承認依頼時を想定

			//			$mailQueue['MailQueue']['send_time'] = NetCommonsTime::getNowDatetime();
			//			$mailQueue = $model->MailQueue->create($mailQueue);
			//			/** @see MailQueue::saveMailQueue() */
			//			if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			//				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			//			}
			//
			//			$mailQueueUser['MailQueueUser']['user_id'] = $userId;
			//			$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];
			//			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
			//			/** @see MailQueueUser::saveMailQueueUser() */
			//			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
			//				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			//			}

		} elseif (isset($toAddresses)) {
			// メールアドレスに配信
			// ここを実行する時は、公開時を想定

			$mailQueue['MailQueue']['send_time'] = NetCommonsTime::getNowDatetime();
			$mailQueue = $model->MailQueue->create($mailQueue);
			/** @see MailQueue::saveMailQueue() */
			if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];

			foreach ($toAddresses as $toAddress) {
				$mailQueueUser['MailQueueUser']['to_address'] = $toAddress;
				$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
				/** @see MailQueueUser::saveMailQueueUser() */
				if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

		} else {
			foreach ($sendTimes as $sendTime) {
				// ルーム配信
				// ここを実行する時は、公開時を想定

				$mailQueue['MailQueue']['send_time'] = $this->__getSaveSendTime($sendTime);
				$mailQueue = $model->MailQueue->create($mailQueue);
				/** @see MailQueue::saveMailQueue() */
				if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				$roomId = Current::read('Room.id');
				$mailQueueUser['MailQueueUser']['room_id'] = $roomId;
				$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];
				$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
				/** @see MailQueueUser::saveMailQueueUser() */
				if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				// ルームIDをクリア
				$mailQueueUser['MailQueueUser']['room_id'] = null;

				// 追加のユーザ達に配信
				if (isset($this->settings[$model->alias]['addUserIds'])) {
					foreach ($this->settings[$model->alias]['addUserIds'] as $addUserId) {
						$mailQueueUser['MailQueueUser']['user_id'] = $addUserId;
						$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
						if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
							throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
						}
					}
				}
			}
		}

		return $mailQueueResult['MailQueue']['id'];
	}

/**
 * 投稿メール - メールアドレスに配信(即時) - メールキューSave
 * 登録フォームの投稿を想定
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @return bool
 * @throws InternalErrorException
 */
	private function __saveQueuePostMailByToAddress(Model $model, $typeKey = MailSetting::DEFAULT_TYPE) {
		$toAddresses = $this->settings[$model->alias]['registration']['toAddresses'];
		$languageId = Current::read('Language.id');

		// 投稿メール - メールアドレスに配信(即時) - メールキューSave
		$mailQueueId = $this->saveQueuePostMail($model, $languageId, null, null, $toAddresses, $typeKey);

		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->__getPluginKey($model);
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
		);

		// ルーム内の承認者達に配信(即時)
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($model, 'content_publishable');
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$mailQueueUser['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);

			/** @see MailQueueUser::saveMailQueueUser() */
			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * 通知メール - 登録者に配信(即時) - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return void
 * @throws InternalErrorException
 */
	private function __saveQueueNoticeMail(Model $model, $languageId, $typeKey = MailSetting::DEFAULT_TYPE) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフロー
			// 暫定対応：3/20現時点では、承認機能=ON, OFFでも投稿者に承認完了通知メールを送る。今後見直し予定  https://github.com/NetCommons3/Mails/issues/44
			// 承認しないなら、承認完了通知メール送らない
			//			$key = Hash::get($this->settings, $model->alias . '.useWorkflow');
			//			$useWorkflow = Hash::get($model->data, $key);
			$useWorkflow = 1;
			if (! $useWorkflow) {
				return;
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメント
			// コメント承認しないなら、承認完了通知メール送らない
			$key = Hash::get($this->settings, $model->alias . '.useCommentApproval');
			$useCommentApproval = Hash::get($model->data, $key);
			if (! $useCommentApproval) {
				return;
			}
		}

		$fixedPhraseType = null;
		$status = Hash::get($model->data, $model->alias . '.status');
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			// --- 公開
			// 承認完了通知メール
			$fixedPhraseType = NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION;

		} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
			// --- 差戻し
			// 差戻し通知メール
			$fixedPhraseType = NetCommonsMail::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL;
		}

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $model->MailSetting->getMailSettingPlugin($languageId, $typeKey);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		if (empty($replyTo)) {
			$replyTo = null;
		}
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->__getPluginKey($model);
		$pluginName = $this->__getPluginName($model);
		$createdUserId = $this->__getCreatedUserId($model);

		// 通知メール - （承認完了、差戻し）(即時) - メールキューSave
		$noticeMail = new NetCommonsMail();
		$noticeMail->initPlugin($languageId, $pluginName);
		$noticeMail->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType);
		$noticeMail->setReplyTo($replyTo);
		$noticeMail = $this->__convertPlainText($model, $noticeMail);

		//$replyTo = key($noticeMail->replyTo());
		$blockKey = Current::read('Block.key');
		$now = NetCommonsTime::getNowDatetime();

		// MailQueueは新規登録
		$mailQueue = $model->MailQueue->create();
		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'replay_to' => $replyTo,
			'mail_subject' => $noticeMail->subject,
			'mail_body' => $noticeMail->body,
			'send_time' => $now,
		);

		// MailQueueUserは新規登録
		$mailQueueUser = $model->MailQueueUser->create();
		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'user_id' => $createdUserId,
			'room_id' => null,
			'to_address' => null,
		);

		// 登録者に配信
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];

		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * 承認依頼メール - 登録者と承認者に配信(即時) - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $publishablePermission 公開パーミッション content_publishable or content_comment_publishable
 * @param string $typeKey メールの種類
 * @return void
 * @throws InternalErrorException
 */
	private function __saveQueueApprovalMail(Model $model, $languageId, $publishablePermission, $typeKey = MailSetting::DEFAULT_TYPE) {
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->__getPluginKey($model);
		$pluginName = $this->__getPluginName($model);
		$createdUserId = $this->__getCreatedUserId($model);
		$blockKey = Current::read('Block.key');

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $model->MailSetting->getMailSettingPlugin($languageId, $typeKey);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		if (empty($replyTo)) {
			$replyTo = null;
		}

		// 投稿メール - メールキューSave
		$postMail = new NetCommonsMail();
		$postMail->initPlugin($languageId, $pluginName);
		$postMail->setMailFixedPhrasePlugin($mailSettings);
		$postMail->setReplyTo($replyTo);
		$postMail = $this->__convertPlainText($model, $postMail);

		//$replyTo = key($postMail->replyTo());
		// MailQueueは新規登録
		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'replay_to' => $replyTo,
			'mail_subject' => $postMail->subject,
			'mail_body' => $postMail->body,
			'send_time' => null,
		);

		$mailQueue['MailQueue']['send_time'] = NetCommonsTime::getNowDatetime();
		$mailQueue = $model->MailQueue->create($mailQueue);
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$mailQueueId = $mailQueueResult['MailQueue']['id'];

		// コンテンツコメントで、参観者まで投稿を許可していると、ログインしていない人もコメント書ける。その時はuser_idなしなので送らない。
		if (!empty($createdUserId)) {
			// --- 承認依頼
			// 投稿メール - 登録者に配信(即時) - メールキューSave
			//$mailQueueId = $this->saveQueuePostMail($model, $languageId, null, $createdUserId, null, $typeKey);

			// MailQueueUserは新規登録
			$mailQueueUser['MailQueueUser'] = array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'user_id' => $createdUserId,
				'room_id' => null,
				'to_address' => null,
			);

			$mailQueueUser['MailQueueUser']['mail_queue_id'] = $mailQueueResult['MailQueue']['id'];
			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
			/** @see MailQueueUser::saveMailQueueUser() */
			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$mailQueueUser = null;
		}

		// MailQueueUserは新規登録
		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'mail_queue_id' => $mailQueueId,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
		);

		// ルーム内の承認者達に配信(即時)
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($model, $publishablePermission);
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$mailQueueUser['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);

			/** @see MailQueueUser::saveMailQueueUser() */
			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
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
		$permissions = $WorkflowComponent->getBlockRolePermissions(array($permission));

		$roleKeys = array_keys($permissions['BlockRolePermissions'][$permission]);
		$conditions = array(
			'Room.id' => $roomId,
			'RolesRoom.role_key' => $roleKeys,
		);
		$rolesRoomsUsers = $model->RolesRoomsUser->getRolesRoomsUsers($conditions);
		return $rolesRoomsUsers;
	}

/**
 * 定型文からメール生文に変換
 *
 * @param Model $model モデル
 * @param NetCommonsMail $mail NetCommonsメール
 * @return NetCommonsMail
 */
	private function __convertPlainText(Model $model, NetCommonsMail $mail) {
		$contentKey = $this->__getContentKey($model);

		// fullpassのURL
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => Current::read('Plugin.key'),
			'action' => 'view',
			'block_id' => Current::read('Block.id'),
			'frame_id' => Current::read('Frame.id'),
			'key' => $contentKey
		));
		$url = NetCommonsUrl::url($url, true);
		$mail->assignTag('X-URL', $url);

		// 暫定対応：3/20現時点。今後見直し予定  https://github.com/NetCommons3/Mails/issues/44
		// 承認つかう時、担当者へのコメントをメールに含める
		//			$key = Hash::get($this->settings, $model->alias . '.useWorkflow');
		//			$useWorkflow = Hash::get($model->data, $key);
		$useWorkflow = 1;
		if ($useWorkflow) {
			$workflowComment = Hash::get($model->data, 'WorkflowComment.comment');
			$commentLabel = __d('net_commons', 'Comments to the person in charge.');
			$workflowComment = $commentLabel . ":\r\n" . $workflowComment;
			$mail->assignTag('X-WORKFLOW_COMMENT', $workflowComment);
		}

		// --- 定型文の埋め込みタグをセット
		$embedTags = Hash::get($this->settings, $model->alias . '.embedTags');
		foreach ($embedTags as $embedTag => $dataKey) {
			$dataValue = Hash::get($model->data, $dataKey);
			$mail->assignTag($embedTag, $dataValue);
		}

		// --- 追加の埋め込みタグ セット
		if (isset($this->settings[$model->alias]['addEmbedTagsValues'])) {
			// 既にセットされているタグであっても、上書きされる
			foreach ($this->settings[$model->alias]['addEmbedTagsValues'] as $embedTag => $value) {
				$mail->assignTag($embedTag, $value);
			}
		}

		// 埋め込みタグ変換：メール定型文の埋め込みタグを変換して、メール生文にする
		$mail->assignTagReplace();

		return $mail;
	}
}
