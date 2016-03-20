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
 * @var string 使用しない
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
		//$this->settings[$model->alias]['addToAddresses'] = null;

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
		$sendTime = $this->__getSendTime($model);
		return $this->saveQueueBehavior($model, array($sendTime));
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
 * ビヘイビアでキュー保存
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時
 * @return bool
 */
	public function saveQueueBehavior(Model $model, $sendTimes) {
		// --- メールを送るかどうか
		if (! $this->isMailSend($model)) {
			return true;
		}

		$languageId = Current::read('Language.id');
		//$contentKey = $model->data[$model->alias]['key'];
		$createdUserId = $model->data[$model->alias]['created_user'];
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		$status = Hash::get($model->data, $model->alias . '.status');

		// 暫定対応
		//$sendTime = $this->__getSendTime($model);
		$sendTime = $sendTimes[0];

		//$MailSetting = ClassRegistry::init('Mails.MailSetting');
		//$MailQueue = ClassRegistry::init('Mails.MailQueue');
		///** @see MailSetting::getMailSettingPlugin() */
		//$mailSetting = $MailSetting->getMailSettingPlugin($languageId);
		//$replyTo = Hash::get($mailSetting, 'MailSetting.replay_to');

		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信 - メールキューSave
				$this->__saveQueuePostMail($model, $languageId, $sendTime);

				// 暫定対応：3/20現時点では、承認機能=ON, OFFでも投稿者に承認完了通知メールを送る。今後見直し予定
				// 承認完了通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION, $createdUserId);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				$this->__saveQueueApprovalMail($model, $languageId, $sendTime, $createdUserId, 'content_publishable');

			} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
				// --- 差戻し
				// 差戻し通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL, $createdUserId);
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメントの承認時の処理
			// 対応必要：title空のまま
			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// --- 公開
				// 投稿メール - ルーム配信 - メールキューSave
				$this->__saveQueuePostMail($model, $languageId, $sendTime);

				// 暫定対応：3/20現時点では、承認機能=ON, OFFでも投稿者に承認完了通知メールを送る。今後見直し予定
				// 承認完了通知メール - 登録者に配信 - メールキューSave
				$this->__saveQueueNoticeMail($model, $languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION, $createdUserId);

			} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
				// --- 承認依頼
				$this->__saveQueueApprovalMail($model, $languageId, $sendTime, $createdUserId, 'content_comment_publishable');
			}

		} elseif ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE) {
			// --- ワークフローの機能自体、使ってないプラグインの処理
			// --- 公開
			// 投稿メール - メールキューSave
			$this->__saveQueuePostMail($model, $languageId, $sendTime);
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
 * メール送信日時 ゲット
 *
 * @param Model $model モデル
 * @return date 送信日時
 */
	private function __getSendTime(Model $model) {
		// DBに項目があり期限付き公開の時のみ、日時を取得する（ブログを想定）。その後、未来日メール送られる
		if ($model->hasField(['public_type', 'publish_start']) && $model->data[$model->alias]['public_type'] == WorkflowBehavior::PUBLIC_TYPE_LIMITED) {
			return $model->data[$model->alias]['publish_start'];
		}
		return null;
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
 * メールを送るかどうか
 *
 * @param Model $model モデル
 * @return bool
 */
	public function isMailSend(Model $model) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSetting = $MailSetting->getMailSettingPlugin();
		$isMailSend = Hash::get($mailSetting, 'MailSetting.is_mail_send');

		// プラグイン設定でメール通知を使わないなら、メール送らない
		if (! $isMailSend) {
			return false;
		}

		$sendTime = $this->__getSendTime($model);
		//if (isset($this->settings[$model->alias]['mailSendTime'])) {
		if (isset($sendTime)) {
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
			if (empty($useCron) && strtotime($now) >= strtotime($sendTime)) {
				return false;
			}
		}

		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		// ここまで処理して承認フローを使わないなら、メール送る
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
 * 投稿メール - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param date $sendTime メール送信日時
 * @param int $createdUserId 登録ユーザID
 * @param string $toAddresses 送信先メールアドレス
 * @return NetCommonsMail
 */
	private function __saveQueuePostMail(Model $model, $languageId, $sendTime, $createdUserId = null, $toAddresses = null) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $MailSetting->getMailSettingPlugin($languageId);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		$contentKey = $model->data[$model->alias]['key'];

		// --- 公開
		// 投稿メール - メールキューSave
		$postMail = new NetCommonsMail();
		$postMail->initPlugin($languageId);
		$postMail->setMailFixedPhrasePlugin($languageId);
		$postMail->setReplyTo($replyTo);
		$postMail = $this->__convertPlainText($model, $postMail);
		if (isset($createdUserId)) {
			//登録者に配信
			/** @see MailQueue::saveQueueByUserId() */
			$MailQueue->saveQueueByUserId($postMail, $contentKey, $languageId, $createdUserId);
		} elseif (isset($toAddresses)) {
			//メールアドレスに配信
			foreach ($toAddresses as $toAddress) {
				/** @see MailQueue::saveQueueByToAddress() */
				$MailQueue->saveQueueByToAddress($postMail, $contentKey, $languageId, $toAddress);
			}
		} else {
			// ルーム配信
			/** @see MailQueue::saveQueueByRoomId() */
			$MailQueue->saveQueueByRoomId($postMail, $contentKey, $languageId, $sendTime);
		}
		return $postMail;
	}

/**
 * 通知メール - 登録者に配信 - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $fixedPhraseType 定型文の種類
 * @param int $createdUserId 登録ユーザID
 * @return void
 */
	private function __saveQueueNoticeMail(Model $model, $languageId, $fixedPhraseType, $createdUserId) {
		$MailSetting = ClassRegistry::init('Mails.MailSetting');
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettings = $MailSetting->getMailSettingPlugin($languageId);

		$replyTo = Hash::get($mailSettings, 'MailSetting.replay_to');
		$contentKey = $model->data[$model->alias]['key'];

		// 通知メール（承認完了、差戻し） - メールキューSave
		$noticeMail = new NetCommonsMail();
		$noticeMail->initPlugin($languageId);
		//$completedMail->setMailFixedPhraseSiteSetting($languageId, NetCommonsMail::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION);
		$noticeMail->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType);
		$noticeMail->setReplyTo($replyTo);
		$noticeMail = $this->__convertPlainText($model, $noticeMail);
		//登録者に配信
		/** @see MailQueue::saveQueueByUserId() */
		$MailQueue->saveQueueByUserId($noticeMail, $contentKey, $languageId, $createdUserId);
	}

/**
 * 承認依頼メール - 登録者と承認者に配信 - メールキューSave
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param date $sendTime メール送信日時
 * @param int $createdUserId 登録ユーザID
 * @param string $publishablePermission 公開パーミッション content_publishable or content_comment_publishable
 * @return void
 */
	private function __saveQueueApprovalMail(Model $model, $languageId, $sendTime, $createdUserId, $publishablePermission) {
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		$contentKey = $model->data[$model->alias]['key'];

		// --- 承認依頼
		// 投稿メール - 登録者に配信 - メールキューSave
		$postMail = $this->__saveQueuePostMail($model, $languageId, $sendTime, $createdUserId);

		// ルーム内の承認者達にメールを送る
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission($publishablePermission);
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			/** @see MailQueue::saveQueueByUserId() */
			$MailQueue->saveQueueByUserId($postMail, $contentKey, $languageId, $rolesRoomsUser['RolesRoomsUser']['user_id']);
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
 * 投稿メール - メールアドレスに配信 - メールキューSave
 * 登録フォームの投稿を想定
 *
 * @param Model $model モデル
 * @param string $toAddresses 送信先メールアドレス
 * @param int $languageId 言語ID
 * @param date $sendTime メール送信日時
 * @return void
 */
	public function saveQueuePostMailByToAddress(Model $model, $toAddresses, $languageId = null, $sendTime = null) {
		if ($languageId === null) {
			$languageId = Current::read('Language.id');
		}

		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		$contentKey = $model->data[$model->alias]['key'];

		// --- 承認依頼
		// 投稿メール - メールアドレスに配信 - メールキューSave
		$postMail = $this->__saveQueuePostMail($model, $languageId, $sendTime, null, $toAddresses);

		// ルーム内の承認者達にメールを送る
		// 送信者データ取得
		$rolesRoomsUsers = $this->__getRolesRoomsUsersByPermission('content_publishable');
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			/** @see MailQueue::saveQueueByUserId() */
			$MailQueue->saveQueueByUserId($postMail, $contentKey, $languageId, $rolesRoomsUser['RolesRoomsUser']['user_id']);
		}
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

		// --- 定型文の埋め込みタグをセット
		//$this->settings[$model->alias];
		$embedTags = Hash::get($this->settings, $model->alias . '.embedTags');
		foreach ($embedTags as $embedTag => $dataKey) {
			$dataValue = Hash::get($model->data, $dataKey);
			$mail->assignTag($embedTag, $dataValue);
		}

		$contentKey = $model->data[$model->alias]['key'];

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
