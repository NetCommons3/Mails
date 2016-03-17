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
 * 複数人パターン でキューに保存する
 * ・room_id + ロール（block_role_permission）　：　複数人パターン
 *
 * @param string $contentKey コンテンツキー
 * @param string $typeKey メール定型文の種類
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueByRoomId($contentKey, $typeKey = 'contents', $sendTime = null) {
		$roomId = Current::read('Room.id');
		return $this->__saveQueue($contentKey, $typeKey, $roomId, null, null, $sendTime);
	}

/**
 * 個別パターン1 でキューに保存する
 * ・user_id 　　：　個別パターン1。パスワード再発行等 (NCにいる人イメージ)
 *
 * @param string $contentKey コンテンツキー
 * @param int $userId ユーザーID
 * @param string $typeKey メール定型文の種類
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueByUserId($contentKey, $userId, $typeKey = 'contents', $sendTime = null) {
		return $this->__saveQueue($contentKey, $typeKey, null, $userId, null, $sendTime);
	}

/**
 * キューに保存する
 * ・メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
 * ・メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
 *
 * @param string $contentKey コンテンツキー
 * @param string $typeKey メール定型文の種類
 * @param int $roomId ルームID
 * @param int $userId ユーザーID
 * @param string $toAddress 送信先メールアドレス
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	private function __saveQueue($contentKey, $typeKey, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		//private function __saveQueue(NetCommonsMail $mail, $contentKey, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		if (isset($sendTime)) {
			// ここに、クーロン設定なし：未来日メール送信しない 処理を記述
		}

		// --- メール文を多言語するなら、ここからメールキュー保存まで、言語毎にループ
		//		$mail = new NetCommonsMail();
		//		$languageId = Current::read('Language.id');
		//		$mail->initPlugin($languageId, $typeKey);
		//		$mail->assignTags($this->tags);

		// タグ変換：メール定型文をタグ変換して、生文に変換する
		//$mail->assignTagReplace();

		// dataの準備
		//$data = $this->__readyData($mail, $contentKey, $languageId, $roomId, $userId, $toAddress, $sendTime);

		// メールキューテーブル(mail_queues)保存 - （メール生文を）
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueue = $this->_controller->MailQueue->saveMailQueue($data)) {
			$this->_controller->NetCommons->handleValidationError($this->_controller->MailQueue->validationErrors);
			return false;
		}

		// ※ mail_queue_users 値をセットするパターンが３つある。いずれかをセットする
		// ※ 通知する権限は、block_role_permissionにもつ想定
		// 　　・room_id + ロール（block_role_permission）　：　複数人パターン
		// 　　　　⇒ $roomId 引数で取得, $blockKeyでロール取得
		// 　　・user_id 　　：　個別パターン1。パスワード再発行等
		// 　　　　⇒ $this->toUsersに情報あるだろう。
		// 　　・to_address　：　個別パターン2。その他に通知するメールアドレス
		// 　　　　⇒ $this->toUsersにセットしてる

		if (isset($roomId) || isset($userId)) {
			// room_id, user_idは、各ユーザ毎のlanguage_idで、対応するメールを送る
			$data['MailQueueUser']['mail_queue_key'] = $mailQueue['MailQueue']['key'];
		} elseif (isset($toAddress)) {
			// メールアドレスは、MailQueueのIDで指定された言語で送る
			$data['MailQueueUser']['mail_queue_id'] = $mailQueue['MailQueue']['id'];
		}

		// メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $mailQueueUser = $this->_controller->MailQueueUser->saveMailQueueUser($data)) {
			$this->_controller->NetCommons->handleValidationError($this->_controller->MailQueueUser->validationErrors);
			return false;
		}

		return true;
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
