<?php
/**
 * MailQueue Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailsAppModel', 'Mails.Model');

/**
 * メールキュー
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailQueue extends MailsAppModel {

/**
 * use behaviors
 *
 * @var array
 * @see OriginalKeyBehavior::beforeSave()
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',		// 自動でkeyセット
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * beforeValidate
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'plugin_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'block_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'replay_to' => array(
				'email' => array(
					'rule' => array('email'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => true,
				),
			),
			'mail_subject' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'mail_body' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'send_time' => array(
				'datetime' => array(
					'rule' => array('datetime'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'MailQueueUser',
	);

/**
 * 複数人パターン でキューに保存する
 * ・room_id + ロール（block_role_permission）　：　複数人パターン
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $languageId 言語ID
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueByRoomId(NetCommonsMail $mail, $contentKey, $languageId, $sendTime = null) {
		//public function saveQueueByRoomId(Model $model, NetCommonsMail $mail, $languageId, $sendTime = null) {
		//private function __readyDataByRoomId(Model $model, NetCommonsMail $mail, $contentKey, $languageId) {
		$roomId = Current::read('Room.id');
		$data = $this->__readyData($mail, $contentKey, $languageId, $roomId, null, null, $sendTime);
		return $this->__saveQueue($data);
	}

/**
 * 個別パターン1 でキューに保存する
 * ・user_id 　　：　個別パターン1。パスワード再発行等 (NCにいる人イメージ)
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $languageId 言語ID
 * @param int $userId ユーザーID
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueByUserId(NetCommonsMail $mail, $contentKey, $languageId, $userId, $sendTime = null) {
		//public function saveQueueByUserId(Model $model, NetCommonsMail $mail, $languageId, $userId) {
		//return $this->__readyData($model, $contentKey, $typeKey, null, $userId, null, $sendTime);
		$data = $this->__readyData($mail, $contentKey, $languageId, null, $userId, null, $sendTime);
		return $this->__saveQueue($data);
	}

/**
 * dataの準備
 * mail_queue_users 値をセットするパターンが３つある。いずれかをセットする
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $languageId 言語ID
 * @param int $roomId ルームID - 複数人パターン。ルーム配信
 * @param int $userId ユーザーID - 個別パターン1。承認フローでの投稿、差戻し、承認完了通知、パスワード再発行等
 * @param string $toAddress 送信先メールアドレス - 個別パターン2。登録フォームの投稿者
 * @param date $sendTime 送信日時
 * @return array data
 */
	private function __readyData(NetCommonsMail $mail, $contentKey, $languageId, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		//private function __readyData(NetCommonsMail $mail, $contentKey, $languageId, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		//private function __readyData(Model $model, NetCommonsMail $mail, $contentKey, $languageId, $roomId = null, $userId = null, $toAddress = null) {
		//private function __readyData(Model $model, NetCommonsMail $mail, $languageId, $roomId = null, $userId = null, $toAddress = null) {

		//$mailSendTime = isset($this->settings[$model->alias]['mailSendTime']) ? $this->settings[$model->alias]['mailSendTime'] : NetCommonsTime::getNowDatetime();
		$sendTime = isset($sendTime) ? $sendTime : NetCommonsTime::getNowDatetime();
		$blockKey = Current::read('Block.key');
		$pluginKey = Current::read('Plugin.key');
		//$languageId = Current::read('Language.id');
		$replyTo = key($mail->replyTo());
		//$replyTo = empty($this->replyTo()) ? $this->replyTo() : null;
		//$contentKey = $model->data[$model->alias]['key'];

		$data = array(
			'MailQueue' => array(
				'language_id' => $languageId,
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'replay_to' => $replyTo,
				'mail_subject' => $mail->subject,
				'mail_body' => $mail->body,
				'send_time' => $sendTime,
			),
			'MailQueueUser' => array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'user_id' => $userId,
				'room_id' => $roomId,
				'to_address' => $toAddress,
			)
		);

		return $data;
	}

/**
 * キューに保存する
 * ・メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
 * ・メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
 *
 * @param array $data データ
 * @return bool 成功 or 失敗
 */
	private function __saveQueue($data) {
		$this->loadModels(array(
			'MailQueueUser' => 'Mails.MailQueueUser',
		));

		// メールキューテーブル(mail_queues)保存 - （メール生文を）
		if (! $mailQueue = $this->saveMailQueue($data)) {
			return false;
		}

		$data['MailQueueUser']['mail_queue_id'] = $mailQueue['MailQueue']['id'];

		// メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
		if (! $mailQueueUser = $this->MailQueueUser->saveMailQueueUser($data)) {
			return false;
		}

		return true;
	}

/**
 * メールキューデータ保存
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailQueue($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			$this->rollback();
			return false;
		}

		try {
			// 保存
			if (! $mailQueue = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailQueue;
	}

/**
 * メールキューデータ削除
 *
 * @param int $id ID
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function deleteMailQueue($id) {
		if (empty($id)) {
			return false;
		}

		//トランザクションBegin
		$this->begin();

		try {
			if (! $this->delete($id, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return true;
	}
}
