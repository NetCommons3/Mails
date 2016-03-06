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
 */
	public function saveQueueRoomId(NetCommonsMail $mail, $contentKey, $roomId, $sendTime = null) {
//		CakeLog::debug('saveQueueRoomId');
		return $this->__saveQueue($mail, $contentKey, $roomId, null, null, $sendTime);
	}

/**
 * 個別パターン1 でキューに保存する
 * ・user_id 　　：　個別パターン1。パスワード再発行等 (NCにいる人イメージ)
 */
	public function saveQueueUserId(NetCommonsMail $mail, $contentKey, $userId, $sendTime = null) {
		return $this->__saveQueue($mail, $contentKey, null, $userId, null, $sendTime);
	}

/**
 * 個別パターン2 でキューに保存する
 * ・to_address　：　個別パターン2。メールアドレスのみで通知する (NCにいない人イメージ)
 */
	public function saveQueueToAddress(NetCommonsMail $mail, $contentKey, $toAddress, $sendTime = null) {
		return $this->__saveQueue($mail, $contentKey, null, null, $toAddress, $sendTime);
	}

/**
 * キューに保存する
 * ・メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
 * ・メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
 *
 * まだ仮
 */
	private function __saveQueue(NetCommonsMail $mail, $contentKey, $roomId = null, $userId = null, $toAddress = null, $sendTime = null) {
		//public function saveQueue($contentKey, $languageId, $roomId = null, $sendTime = null) {
//		if ($sendTime === null) {
//			$sendTime = NetCommonsTime::getNowDatetime();
//		}

		//$useComment = Hash::get($this->_controller->viewVars, $this->settings['viewVarsKey']['useComment']);
//CakeLog::debug(111);
		// タグ変換
		// メール定型文をタグ変換して、生文に変換する
		$mail->assignTagReplace();

		// dataの準備
		$data = $this->__readyData($mail, $contentKey, $roomId, $userId, $toAddress, $sendTime);

//		$blockKey = Current::read('Block.key');
//		$pluginKey = Current::read('Plugin.key');

		// 返信先アドレスを取得する
		//$replayTo = parent::replyTo();


		// 件名、本文を取得する
//CakeLog::debug(print_r($sendTime, true));
//CakeLog::debug(print_r(1111, true));
//debug($this->subject);
//debug($this->body);

		//$MailQueue = ClassRegistry::init('Mails.MailQueue', true);
//		$data = array(
//			'MailQueue' => array(
//				'plugin_key' => $pluginKey,
//				'block_key' => $blockKey,
//				'replay_to' => parent::replyTo(),
//				'content_key' => $contentKey,
//				'mail_subject' => $this->subject,
//				'mail_body' => $this->body,
//				'send_time' => $sendTime,
//			)
//		);
//CakeLog::debug(print_r($data, true));

		// メールキューの送信依頼テーブル(mail_queues)保存 - （メール生文を）
		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueue = $this->_controller->MailQueue->saveMailQueue($data)) {
			// エラー
			$this->_controller->NetCommons->handleValidationError($this->_controller->MailQueue->validationErrors);
			//CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ .' (line '. __LINE__ .')');
			//CakeLog::debug(print_r($this->_controller->MailQueue->validationErrors, true));
			return false;
		}
//CakeLog::debug(print_r($mailQueue, true));

		// ※ mail_queue_users 値をセットするパターンが３つある。いずれかをセットする
		// ※ 通知する権限は、block_role_permissionにもつ想定
		// 　　・room_id + ロール（block_role_permission）　：　複数人パターン
		// 　　　　⇒ $roomId 引数で取得, $blockKeyでロール取得
		// 　　・user_id 　　：　個別パターン1。パスワード再発行等
		// 　　　　⇒ $this->toUsersに情報あるだろう。
		// 　　・to_address　：　個別パターン2。その他に通知するメールアドレス
		// 　　　　⇒ $this->toUsersにセットしてる

		//$MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);
//		$data = array(
//			'MailQueueUser' => array(
//				'plugin_key' => $pluginKey,
//				'block_key' => $blockKey,
//				//'mail_queue_id' => $mailQueue['MailQueue']['id'],
//				'mail_queue_id' => 1,
//				'user_id' => $userId,
//				'room_id' => $roomId,
//				'to_address' => $toAddress,
//			)
//		);

		$data['MailQueueUser']['mail_queue_id'] = $mailQueue['MailQueue']['id'];

//		CakeLog::debug(print_r($data, true));

		// メールキュー送信先テーブル(mail_queue_users)保存 - （誰に）
		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $mailQueueUser = $this->_controller->MailQueueUser->saveMailQueueUser($data)) {
			// エラー
			$this->_controller->NetCommons->handleValidationError($this->_controller->MailQueueUser->validationErrors);
			//CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ .' (line '. __LINE__ .')');
			//CakeLog::debug(print_r($this->_controller->MailQueueUser->validationErrors, true));
			return false;
		}

//CakeLog::debug(2222);
		return true;
	}

///**
// * コメントする
// *
// * @return bool 成功 or 失敗
// */
//	public function comment() {
//		// パーミッションがあるかチェック
//		if (!$this->__checkPermission()) {
//			return false;
//		}
//
//		// 登録・編集・承認
//		if ($this->_controller->action == 'add' ||
//			$this->_controller->action == 'edit' ||
//			$this->_controller->action == 'approve') {
//
//			// dataの準備
//			$data = $this->__readyData();
//
//			// コンテンツコメントのデータ保存
//			if (!$this->_controller->ContentComment->saveContentComment($data)) {
//				$this->_controller->NetCommons->handleValidationError($this->_controller->ContentComment->validationErrors);
//
//				// 別プラグインにエラーメッセージとどの処理を送るため
//				/* @link http://skgckj.hateblo.jp/entry/2014/02/09/005111 */
//				$sessionValue = array(
//					'errors' => $this->_controller->ContentComment->validationErrors,
//					'requestData' => $this->_controller->request->data('ContentComment')
//				);
//				$this->Session->write('ContentComments.forRedirect', $sessionValue);
//			}
//
//			// 削除
//		} elseif ($this->_controller->action == 'delete') {
//			// コンテンツコメントの削除
//			if (!$this->_controller->ContentComment->deleteContentComment($this->_controller->request->data('ContentComment.id'))) {
//				return false;
//			}
//		}
//
//		return true;
//	}
//
///**
// * パーミッションがあるかチェック
// *
// * @return bool true:パーミッションあり or false:パーミッションなし
// */
//	private function __checkPermission() {
//		// 登録処理 and 投稿許可あり
//		if ($this->_controller->action == 'add' && Current::permission('content_comment_creatable')) {
//			return true;
//
//			// (編集処理 or 削除処理) and (編集許可あり or 自分で投稿したコメントなら、編集・削除可能)
//		} elseif (($this->_controller->action == 'edit' || $this->_controller->action == 'delete') && (
//				Current::permission('content_comment_editable') ||
//				$this->_controller->data['ContentComment']['created_user'] == (int)AuthComponent::user('id')
//		)) {
//			return true;
//
//			// 承認処理 and 承認許可あり
//		} elseif ($this->_controller->action == 'approve' && Current::permission('content_comment_publishable')) {
//			return true;
//
//		}
//		return false;
//	}

/**
 * dataの準備
 *
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
				//'mail_queue_id' => $mailQueue['MailQueue']['id'],
				//'mail_queue_id' => 1,
				'user_id' => $userId,
				'room_id' => $roomId,
				'to_address' => $toAddress,
			)
		);

		return $data;
	}
}
