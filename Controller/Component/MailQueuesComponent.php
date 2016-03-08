<?php
/**
 * Queue Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');
App::uses('NetCommonsMail', 'Mails.Utility');

/**
 * Queue Component
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Controller\Component
 */
class MailQueuesComponent extends Component {

/**
 * @var Controller コントローラ
 */
	protected $_controller = null;

/**
 * Called before the Controller::beforeFilter().
 *
 * @param Controller $controller Instantiating controller
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::initialize
 */
	public function initialize(Controller $controller) {
		// どのファンクションでも $controller にアクセスできるようにクラス内変数に保持する
		$this->_controller = $controller;
	}

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::startup
 */
	public function startup(Controller $controller) {
		$controller->MailQueue = ClassRegistry::init('Mails.MailQueue');
		$controller->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser');
	}

/**
 * 複数人パターン でキューに保存する
 * ・room_id + ロール（block_role_permission）　：　複数人パターン
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueRoomId(NetCommonsMail $mail, $contentKey, $sendTime = null) {
		$roomId = Current::read('Room.id');
		return $this->__saveQueue($mail, $contentKey, $roomId, null, null, $sendTime);
	}

/**
 * 個別パターン1 でキューに保存する
 * ・user_id 　　：　個別パターン1。パスワード再発行等 (NCにいる人イメージ)
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $userId ユーザーID
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueUserId(NetCommonsMail $mail, $contentKey, $userId, $sendTime = null) {
		return $this->__saveQueue($mail, $contentKey, null, $userId, null, $sendTime);
	}

/**
 * 個別パターン2 でキューに保存する
 * ・to_address　：　個別パターン2。メールアドレスのみで通知する (NCにいない人イメージ)
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param string $toAddress 送信先メールアドレス
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	public function saveQueueToAddress(NetCommonsMail $mail, $contentKey, $toAddress, $sendTime = null) {
		return $this->__saveQueue($mail, $contentKey, null, null, $toAddress, $sendTime);
	}

/**
 * キューに保存する
 * ・メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
 * ・メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $roomId ルームID
 * @param int $userId ユーザーID
 * @param string $toAddress 送信先メールアドレス
 * @param date $sendTime 送信日時
 * @return bool 成功 or 失敗
 */
	private function __saveQueue(NetCommonsMail $mail, $contentKey, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		// タグ変換
		// メール定型文をタグ変換して、生文に変換する
		$mail->assignTagReplace();

		// dataの準備
		$data = $this->__readyData($mail, $contentKey, $roomId, $userId, $toAddress, $sendTime);

		// メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueue = $this->_controller->MailQueue->saveMailQueue($data)) {
			// エラー
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

		$data['MailQueueUser']['mail_queue_id'] = $mailQueue['MailQueue']['id'];

		// メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $mailQueueUser = $this->_controller->MailQueueUser->saveMailQueueUser($data)) {
			// エラー
			$this->_controller->NetCommons->handleValidationError($this->_controller->MailQueueUser->validationErrors);
			return false;
		}

		return true;
	}

/**
 * dataの準備
 *
 * @param NetCommonsMail $mail NetCommonsメール
 * @param string $contentKey コンテンツキー
 * @param int $roomId ルームID
 * @param int $userId ユーザーID
 * @param string $toAddress 送信先メールアドレス
 * @param date $sendTime 送信日時
 * @return array data
 */
	private function __readyData(NetCommonsMail $mail, $contentKey, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		if ($sendTime === null) {
			$sendTime = NetCommonsTime::getNowDatetime();
		}

		$blockKey = Current::read('Block.key');
		$pluginKey = Current::read('Plugin.key');
		$replyTo = key($mail->replyTo());

		$data = array(
			'MailQueue' => array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'replay_to' => $replyTo,
				'content_key' => $contentKey,
				'mail_subject' => $mail->subject,
				'mail_body' => $mail->body,
				'send_time' => $sendTime,
			),
			'MailQueueUser' => array(
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'user_id' => $userId,
				'room_id' => $roomId,
				'to_address' => $toAddress,
			)
		);

		return $data;
	}
}
