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
 */
	const
		MAIL_QUEUE_WORKFLOW_TYPE_NONE = 'none',
		MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW = 'workflow',
		MAIL_QUEUE_WORKFLOW_TYPE_COMMENT = 'contentComment';

/**
 * @var bool 削除済み
 */
	private $__isDeleted = null;

/**
 * setup
 *
 * @param Model $model モデル
 * @param array $settings 設定値
 * @return void
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::setup
 */
	public function setup(Model $model, $settings = array()) {
		$this->settings[$model->alias] = $settings;

		//		// --- 設定ないパラメータの処理
		//		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		//		if ($workflowType === null) {
		//			// --- ワークフローのstatusによって送信内容を変える
		//			if ($model->Behaviors->loaded('Workflow.Workflow')) {
		//				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW;
		//			} else {
		//				$this->settings[$model->alias]['workflowType'] = self::MAIL_QUEUE_WORKFLOW_TYPE_NONE;
		//			}
		//		}
		//$this->settings[$model->alias]['mailSendTime'] = null;
		$this->settings[$model->alias]['addEmbedTagsValues'] = null;
		//$this->settings[$model->alias]['addToAddresses'] = null;
		$this->settings[$model->alias]['addUserIds'] = null;

		$this->__isDeleted = false;
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
		$sendTime = $this->__getSendTimePublish($model);
		return $this->__saveQueue($model, array($sendTime), false);
	}

/**
 * リマインダーでキュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @return bool
 */
	public function saveQueueReminder(Model $model, $sendTimes) {
		return $this->__saveQueue($model, $sendTimes, true);
	}

/**
 * 投稿メール - メールアドレスに配信(即時) - メールキューSave
 * 登録フォームの投稿を想定
 *
 * @param Model $model モデル
 * @param string $toAddresses 送信先メールアドレス
 * @return bool
 * @throws InternalErrorException
 */
	public function saveQueuePostMailByToAddress(Model $model, $toAddresses) {
		//public function saveQueuePostMailByToAddress(Model $model, $toAddresses, $languageId = null, $sendTimeFuture = null) {
		// --- メールを送るかどうか
		if (! $this->isMailSend($model, false)) {
			return true;
		}

		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		//$contentKey = $model->data[$model->alias]['key'];
		$languageId = Current::read('Language.id');

		// 投稿メール - メールアドレスに配信(即時) - メールキューSave
		$data = $this->__saveQueuePostMail($model, $languageId, null, null, $toAddresses);

		// ルーム内の承認者達にメールを送る(即時)
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission('content_publishable');
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$data['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];

			/** @see MailQueue::saveQueue() */
			if (! $MailQueue->saveQueue($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * beforeDelete
 * コンテンツが削除されたら、キューに残っているメールも削除
 *
 * @param Model $model モデル
 * @param bool $cascade If true records that depend on this record will also be deleted
 * @return mixed False if the operation should abort. Any other result will continue.
 * @throws InternalErrorException
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::beforedelete
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforedelete
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function beforeDelete(Model $model, $cascade = true) {
		// 多言語のコンテンツを key を使って、Model::deleteAll() で削除した場合を想定
		// 削除済みなら、もう処理をしない
		if ($this->__isDeleted) {
			return;
		}

		// コンテンツ取得
		$content = $model->find('first', array(
			'conditions' => array($model->alias . '.id' => $model->id)
		));

		$model->loadModels([
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
		]);

		// キューの配信先 削除
		if (! $model->MailQueueUser->deleteAll(array($model->MailQueueUser->alias . '.content_key' => $content[$model->alias]['key']), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// キュー 削除
		if (! $model->MailQueue->deleteAll(array($model->MailQueue->alias . '.content_key' => $content[$model->alias]['key']), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->__isDeleted = true;
		return true;
	}

/**
 * キュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @param bool $useReminder リマインダー使う
 * @return bool
 */
	private function __saveQueue(Model $model, $sendTimes, $useReminder) {
		// --- メールを送るかどうか
		if (! $this->isMailSend($model, $useReminder, $sendTimes)) {
			return true;
		}

		$languageId = Current::read('Language.id');
		$createdUserId = $model->data[$model->alias]['created_user'];
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$status = Hash::get($model->data, $model->alias . '.status');

		if ($useReminder) {
			$now = NetCommonsTime::getNowDatetime();
			foreach ($sendTimes as $key => $sendTime) {
				// リマインダーで日時が過ぎてたら、メール送らないので、除外する
				// isMailSendでリマインダーの複数日全てが、日時過ぎている場合は、この処理まで到達しないので、$sendTimesは空にならない想定
				if (strtotime($now) > strtotime($sendTime)) {
					unset($sendTimes[$key]);
				}
			}
		}

		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信 - メールキューSave
				$this->__saveQueuePostMail($model, $languageId, $sendTimes);

				// 暫定対応：3/20現時点では、承認機能=ON, OFFでも投稿者に承認完了通知メールを送る。今後見直し予定
				// 承認完了通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION, $createdUserId);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				// 承認依頼メール - 登録者と承認者に配信(即時) - メールキューSave
				//$this->__saveQueueApprovalMail($model, $languageId, $sendTime, $createdUserId, 'content_publishable');
				$this->__saveQueueApprovalMail($model, $languageId, $createdUserId, 'content_publishable');

			} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
				// --- 差戻し
				// 差戻し通知メール - 登録者に配信(即時) - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL, $createdUserId);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメントの承認時の処理
			// コンテンツコメントのみ、content_keyとcontent_keyが、通常と違う
			$contentKey = $model->data['ContentComment']['content_key'];
			$pluginKey = $model->data['ContentComment']['plugin_key'];

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信 - メールキューSave
				$this->__saveQueuePostMail($model, $languageId, $sendTimes, null, null, $contentKey, $pluginKey);

				// 暫定対応：3/20現時点では、承認機能=ON, OFFでも投稿者に承認完了通知メールを送る。今後見直し予定
				// 承認完了通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION, $createdUserId, $contentKey, $pluginKey);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				//$this->__saveQueueApprovalMail($model, $languageId, $sendTime, $createdUserId, 'content_comment_publishable');
				$this->__saveQueueApprovalMail($model, $languageId, $createdUserId, 'content_comment_publishable', $contentKey, $pluginKey);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			// --- ワークフローの機能自体、使ってないプラグインの処理
			// --- 公開
			// 投稿メール - ルーム配信 - メールキューSave
			$this->__saveQueuePostMail($model, $languageId, $sendTimes);
		}

		return true;
	}

	///**
	// * メール送信日時 セット
	// *
	// * @param Model $model モデル
	// * @param date $mailSendTime 送信日時
	// * @return void
	// */
	//	public function setMailSendTime(Model $model, $mailSendTime) {
	//		$this->settings[$model->alias]['mailSendTime'] = $mailSendTime;
	//		//$this->__mailSendTime = $mailSendTime;
	//	}

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

	///**
	// * 追加で送信するメールアドレス セット
	// *
	// * @param Model $model モデル
	// * @param array $toAddresses 埋め込みタグ
	// * @return void
	// */
	//	public function setAddToAddresses(Model $model, $toAddresses) {
	//		$this->settings[$model->alias]['addToAddresses'] = $toAddresses;
	//	}

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
 * メールを送るかどうか
 *
 * @param Model $model モデル
 * @param bool $useReminder リマインダー使う
 * @param arrya $sendTimeReminders リマインダー送信日時
 * @return bool
 */
	public function isMailSend(Model $model, $useReminder, $sendTimeReminders = null) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSetting = $MailSetting->getMailSettingPlugin();
		$isMailSend = Hash::get($mailSetting, 'MailSetting.is_mail_send');

		// プラグイン設定でメール通知を使わないなら、メール送らない
		if (! $isMailSend) {
			return false;
		}

		if ($useReminder) {
			// --- リマインダー
			// リマインダーが複数日あって、全て日時が過ぎてたら、メール送らない
			$isMailSendReminder = false;
			foreach ($sendTimeReminders as $sendTime) {
				if ($this->__isMailSendTime($useReminder, $sendTime)) {
					$isMailSendReminder = true;
				}
			}
			if (! $isMailSendReminder) {
				return false;
			}

		} else {
			// --- 通常の投稿
			// 公開日時 ゲット
			$sendTime = $this->__getSendTimePublish($model);

			if (! $this->__isMailSendTime($useReminder, $sendTime)) {
				return false;
			}
		}

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		// ここまで処理して承認機能なしなら、メール送る
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			return true;
		}

		$status = Hash::get($model->data, $model->alias . '.status');
		// 一時保存はメール送らない
		if ($status == WorkflowComponent::STATUS_IN_DRAFT) {
			return false;
		}

		return true;
	}

/**
 * メール送信日時で送るかどうか
 *
 * @param bool $useReminder リマインダー使う
 * @param date $sendTime メール送信日時
 * @return bool
 */
	private function __isMailSendTime($useReminder, $sendTime) {
		if ($sendTime === null) {
			return true;
		}

		$SiteSetting = ClassRegistry::init('SiteManager.SiteSetting');
		// SiteSettingからメール設定を取得する
		$siteSetting = $SiteSetting->getSiteSettingForEdit(array(
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
 * 投稿メール - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param array $sendTimes メール送信日時 配列
 * @param int $createdUserId 登録ユーザID
 * @param string $toAddresses 送信先メールアドレス
 * @param string $contentKey コンテンツキー
 * @param string $pluginKey プラグインキー
 * @return array メールキューデータ
 * @throws InternalErrorException
 */
	private function __saveQueuePostMail(Model $model, $languageId, $sendTimes = null, $createdUserId = null, $toAddresses = null, $contentKey = null, $pluginKey = null) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $MailSetting->getMailSettingPlugin($languageId);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		if ($contentKey === null) {
			$contentKey = $model->data[$model->alias]['key'];
		}
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}

		// 投稿メール - メールキューSave
		$postMail = new NetCommonsMail();
		$postMail->initPlugin($languageId);
		$postMail->setMailFixedPhrasePlugin($mailSettings);
		$postMail->setReplyTo($replyTo);
		$postMail = $this->__convertPlainText($model, $postMail);

		$replyTo = key($postMail->replyTo());
		$blockKey = Current::read('Block.key');
		$data = array(
			'MailQueue' => array(
				'language_id' => $languageId,
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'replay_to' => $replyTo,
				'mail_subject' => $postMail->subject,
				'mail_body' => $postMail->body,
				'send_time' => null,
			),
			'MailQueueUser' => array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'user_id' => null,
				'room_id' => null,
				'to_address' => null,
			)
		);

		if (isset($createdUserId)) {
			// 登録者に配信
			// ここを実行する時は、承認依頼時を想定
			$data['MailQueue']['send_time'] = NetCommonsTime::getNowDatetime();
			$data['MailQueueUser']['user_id'] = $createdUserId;

			/** @see MailQueue::saveQueue() */
			if (! $MailQueue->saveQueue($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

		} elseif (isset($toAddresses)) {
			// メールアドレスに配信
			// ここを実行する時は、公開時を想定
			$data['MailQueue']['send_time'] = NetCommonsTime::getNowDatetime();

			foreach ($toAddresses as $toAddress) {
				$data['MailQueueUser']['to_address'] = $toAddress;
				if (! $MailQueue->saveQueue($data)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

		} else {
			foreach ($sendTimes as $sendTime) {
				// ルーム配信
				// ここを実行する時は、公開時を想定
				$roomId = Current::read('Room.id');
				$data['MailQueue']['send_time'] = $this->__getSaveSendTime($sendTime);
				$data['MailQueueUser']['room_id'] = $roomId;

				if (! $MailQueue->saveQueue($data)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
				// ルームIDをクリア
				$data['MailQueueUser']['room_id'] = null;

				// 追加のユーザ達に配信
				if (isset($this->settings[$model->alias]['addUserIds'])) {
					foreach ($this->settings[$model->alias]['addUserIds'] as $addUserId) {
						$data['MailQueueUser']['to_address'] = $addUserId;

						if (! $MailQueue->saveQueue($data)) {
							throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
						}
					}
				}
			}
		}

		// $dataを使いまわすため、3パターンの値クリア
		$data['MailQueueUser']['user_id'] = null;
		$data['MailQueueUser']['room_id'] = null;
		$data['MailQueueUser']['to_address'] = null;

		return $data;
	}

/**
 * 通知メール - 登録者に配信(即時) - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $fixedPhraseType 定型文の種類
 * @param int $createdUserId 登録ユーザID
 * @param string $contentKey コンテンツキー
 * @param string $pluginKey プラグインキー
 * @return void
 * @throws InternalErrorException
 */
	private function __saveQueueNoticeMail(Model $model, $languageId, $fixedPhraseType, $createdUserId, $contentKey = null, $pluginKey = null) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $MailSetting->getMailSettingPlugin($languageId);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		if ($contentKey === null) {
			$contentKey = $model->data[$model->alias]['key'];
		}
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}

		// 通知メール - （承認完了、差戻し）(即時) - メールキューSave
		$noticeMail = new NetCommonsMail();
		$noticeMail->initPlugin($languageId);
		//$completedMail->setMailFixedPhraseSiteSetting($languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION);
		$noticeMail->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType);
		$noticeMail->setReplyTo($replyTo);
		$noticeMail = $this->__convertPlainText($model, $noticeMail);

		$replyTo = key($noticeMail->replyTo());
		$blockKey = Current::read('Block.key');
		$now = NetCommonsTime::getNowDatetime();
		$data = array(
			'MailQueue' => array(
				'language_id' => $languageId,
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'replay_to' => $replyTo,
				'mail_subject' => $noticeMail->subject,
				'mail_body' => $noticeMail->body,
				'send_time' => $now,
			),
			'MailQueueUser' => array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'user_id' => $createdUserId,
				'room_id' => null,
				'to_address' => null,
			)
		);

		// 登録者に配信
		/** @see MailQueue::saveQueue() */
		if (! $MailQueue->saveQueue($data)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * 承認依頼メール - 登録者と承認者に配信(即時) - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param int $createdUserId 登録ユーザID
 * @param string $publishablePermission 公開パーミッション content_publishable or content_comment_publishable
 * @param string $contentKey コンテンツキー
 * @param string $pluginKey プラグインキー
 * @return void
 * @throws InternalErrorException
 */
	private function __saveQueueApprovalMail(Model $model, $languageId, $createdUserId, $publishablePermission, $contentKey = null, $pluginKey = null) {
		//private function __saveQueueApprovalMail(Model $model, $languageId, $sendTime, $createdUserId, $publishablePermission) {
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		if ($contentKey === null) {
			$contentKey = $model->data[$model->alias]['key'];
		}

		// --- 承認依頼
		// 投稿メール - 登録者に配信(即時) - メールキューSave
		$data = $this->__saveQueuePostMail($model, $languageId, null, $createdUserId, null, $contentKey, $pluginKey);

		// ルーム内の承認者達にメールを送る(即時)
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($publishablePermission);
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$data['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];

			/** @see MailQueue::saveQueue() */
			if (! $MailQueue->saveQueue($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
	}

/**
 * ルーム内で該当パーミッションありのユーザ ゲット
 *
 * @param string $permission パーミッション
 * @return array
 */
	private function __getRolesRoomsUsersByPermission($permission) {
		// 暫定対応：DefaultRolePermission見てないけど、これで大丈夫？
		// 暫定対応：RolesRoomsUserモデルに、このfunction持っていきたいな。
		$RolesRoomsUser = ClassRegistry::init('Rooms.RolesRoomsUser');
		$conditions = array(
			'RolesRoomsUser.room_id' => Current::read('Room.id'),
			'RoomRolePermission.permission' => $permission,
			'RoomRolePermission.value' => 1,
		);
		$rolesRoomsUsers = $RolesRoomsUser->find('all', array(
			'recursive' => -1,
			'fields' => array(
				'RolesRoomsUser.*',
			),
			'joins' => array(
				array(
					'table' => 'room_role_permissions',
					'alias' => 'RoomRolePermission',
					'type' => 'INNER',
					'conditions' => array(
						'RolesRoomsUser.roles_room_id' . ' = RoomRolePermission.roles_room_id',
					),
				),
			),
			'conditions' => $conditions,
		));
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
		//private function __getNetCommonsMail(Model $model, $languageId, $typeKey = 'contents') {
		// --- 定型文をNetCommonsMailにセット
		//$mail = new NetCommonsMail();
		//$languageId = Current::read('Language.id');
		//$mail->initPlugin($languageId, $typeKey);
		//$mail->setMailSettingPlugin($languageId);
		//$mail->assignTags($this->tags);

		//$contentKey = $model->data[$model->alias]['key'];
		$contentKey = Hash::get($model->data, $model->alias . '.key');

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

		$workflowComment = Hash::get($model->data, 'WorkflowComment.comment');
		$mail->assignTag('X-APPROVAL_COMMENT', $workflowComment);

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
