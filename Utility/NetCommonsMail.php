<?php
/**
 * NetCommonsメール Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji Masukawa
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CakeEmail', 'Network/Email');
App::uses('SiteSetting', 'SiteManager.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');
App::uses('NetCommonsMailAssignTag', 'Mails.Utility');

/**
 * NetCommonsメール Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 * @property SiteSetting $SiteSetting
 * @property MailSetting $MailSetting
 * @property RoomsLanguage $RoomsLanguage
 * @property RolesRoomsUser $RolesRoomsUser
 * @property User $User
 * @property Workflow $Workflow
 */
class NetCommonsMail extends CakeEmail {

/**
 * @var string 件名
 */
	public $subject = null;

/**
 * @var string|array 本文
 */
	public $body = null;

/**
 * @var array SiteSetting model data
 */
	public $siteSetting = null;

/**
 * @var NetCommonsMailAssignTag 埋め込みタグ Utility
 */
	public $mailAssignTag = null;

/**
 * Constructor
 *
 * @param array|string $config Array of configs, or string to load configs from email.php
 * @see CakeEmail::__construct()
 */
	public function __construct($config = null) {
		parent::__construct($config);

		$this->MailSetting = ClassRegistry::init('Mails.MailSetting');
		$this->RoomsLanguage = ClassRegistry::init('Rooms.RoomsLanguage');
		$this->RolesRoomsUser = ClassRegistry::init('Rooms.RolesRoomsUser');
		$this->User = ClassRegistry::init('Users.User');

		$this->mailAssignTag = new NetCommonsMailAssignTag();
	}

/**
 * 初期設定 プラグイン用
 *
 * @param int $languageId 言語ID
 * @param string $pluginName プラグイン名
 * @return void
 * @see CakeEmail::$charset default=utf-8
 * @see CakeEmail::$headerCharset default=utf-8
 */
	public function initPlugin($languageId, $pluginName = null) {
		// SiteSettingからメール設定を取得する
		SiteSettingUtil::setup(array(
			'Mail.from',
			'Mail.from_name',
			'Mail.messageType',
			'Mail.transport',
			'Mail.smtp.host',
			'Mail.smtp.port',
			'Mail.smtp.user',
			'Mail.smtp.pass',
		));
		$this->__initConfig();
		//$this->__setTags($languageId, $pluginName);
		$this->mailAssignTag->initTags($languageId, $pluginName);
	}

/**
 * 初期設定 Shell用
 *
 * @param array $mailQueue メールキューデータ
 * @return void
 */
	public function initShell($mailQueue) {
		$this->__initConfig();
		$this->__setMailSettingQueue($mailQueue);
	}

/**
 * 初期設定 メールのコンフィグ
 *
 * @return void
 */
	private function __initConfig() {
		$config = array();
		$transport = SiteSettingUtil::read('Mail.transport');

		// SMTP, SMTPAuth
		if ($transport == SiteSetting::MAIL_TRANSPORT_SMTP) {
			$smtpHost = SiteSettingUtil::read('Mail.smtp.host');
			$smtpPort = SiteSettingUtil::read('Mail.smtp.port');
			$smtpUser = SiteSettingUtil::read('Mail.smtp.user');
			$smtpPass = SiteSettingUtil::read('Mail.smtp.pass');

			$config['transport'] = 'Smtp';
			$config['host'] = $smtpHost;
			$config['port'] = $smtpPort;

			// 値が無ければ：SMTP
			// 値があれば  ：SMTPAuth。なのでユーザ、パス設定
			if (!empty($smtpUser) && !empty($smtpPass)) {
				$config['username'] = $smtpUser;
				$config['password'] = $smtpPass;
			}

		} elseif ($transport == SiteSetting::MAIL_TRANSPORT_PHPMAIL) {
			// phpmail
			$config['transport'] = 'Mail';

		} elseif ($transport == 'Debug') {
			// テスト用
			$config['transport'] = 'Debug';
		}

		parent::config($config);

		// html or text
		$messageType = SiteSettingUtil::read('Mail.messageType');
		parent::emailFormat($messageType);
	}

/**
 * From セット
 *
 * @param int $languageId 言語ID
 * @return void
 */
	public function setFrom($languageId) {
		$from = SiteSettingUtil::read('Mail.from');
		$fromName = SiteSettingUtil::read('Mail.from_name', null, $languageId);
		$this->__setFrom($from, $fromName);
	}

/**
 * From 各種セット
 *
 * @param string $from 送信者メールアドレス
 * @param string $fromName 送信者名
 * @return void
 */
	private function __setFrom($from, $fromName = null) {
		parent::from($from, $fromName);
		// 通称envelope-fromセット(正式名reverse-path RFC 5321)
		parent::sender($from, $fromName);

		// Return-Path(RFC 5322)セット - config['transport' => 'Mail']用
		$config = $this->config();
		$config['additionalParameters'] = '-f' . $from;
		$this->config($config);
	}

/**
 * メール送信する件名、本文をセット
 *
 * @param array $mailQueue メールキューデータ
 * @return void
 */
	private function __setMailSettingQueue($mailQueue) {
		if (empty($mailQueue)) {
			return;
		}

		$subject = Hash::get($mailQueue, 'MailQueue.mail_subject');
		$body = Hash::get($mailQueue, 'MailQueue.mail_body');
		$replyTo = Hash::get($mailQueue, 'MailQueue.reply_to');

		// 生文
		$this->setSubject($subject);
		$this->setBody($body);

		// 返信先アドレス
		$this->setReplyTo($replyTo);
	}

/**
 * 件名をセットする
 *
 * @param string $subject 件名
 * @return void
 */
	public function setSubject($subject) {
		$this->subject = trim($subject);
	}

/**
 * 本文をセットする
 *
 * @param string $body 本文
 * @return void
 */
	public function setBody($body) {
		$this->body = trim($body);
	}

/**
 * 返信先アドレス セット
 *
 * @param string $replyTo 返信先アドレス
 * @return void
 */
	public function setReplyTo($replyTo) {
		if (! empty($replyTo)) {
			parent::replyTo($replyTo);
		}
	}

/**
 * 埋め込みタグの追加
 *
 * @param string $tag タグ
 * @param string $value 変換する値
 * @return array タグ
 */
	public function assignTag($tag, $value = null) {
		$this->mailAssignTag->assignTag($tag, $value);
	}

/**
 * 埋め込みタグを配列で追加
 *
 * @param array $tags タグ配列
 * @return void
 */
	public function assignTags($tags) {
		$this->mailAssignTag->assignTags($tags);
	}

/**
 * 改行対応
 *
 * @return void
 */
	public function brReplace() {
		if (parent::emailFormat() == 'text') {
			// text形式は配列にすると改行される
			$this->body = explode("\n", $this->body);
		} else {
			$this->body = str_replace("\n", '<br />', $this->body);
		}
	}

/**
 * キューからメール送信
 *
 * @param array $mailQueueUser メール配信先データ
 * @param int $mailQueueLanguageId キューの言語ID
 * @return bool|string|array false:エラー|送信メール文|送信メール文配列
 */
	public function sendQueueMail($mailQueueUser, $mailQueueLanguageId) {
		if ($this->body == '') {
			$logMessage = 'Mail body is empty.';
			LogError($logMessage . ' [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 改行対応
		$this->brReplace();

		// --- 3パターン対応
		$roomId = Hash::get($mailQueueUser, 'room_id');
		$userId = Hash::get($mailQueueUser, 'user_id');
		$toAddress = Hash::get($mailQueueUser, 'to_address');
		if ($roomId === null && $userId === null && $toAddress === null) {
			$logMessage = 'Mail delivery destination is empty.';
			LogError($logMessage . ' [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$messages = null;
		if (isset($roomId)) {
			// --- ルーム単位でメール配信
			$blockKey = $mailQueueUser['block_key'];
			$sendRoomPermission = $mailQueueUser['send_room_permission'];
			$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
			$permissions = $WorkflowComponent->getBlockRolePermissions(array($sendRoomPermission),
				$roomId, $blockKey);

			$roleKeys = array_keys($permissions['BlockRolePermissions'][$sendRoomPermission]);
			$conditions = array(
				'Room.id' => $roomId,
				'RolesRoom.role_key' => $roleKeys,
			);
			$rolesRoomsUsers = $this->RolesRoomsUser->getRolesRoomsUsers($conditions);
			$rolesRoomsUserIds = Hash::extract($rolesRoomsUsers, '{n}.RolesRoomsUser.roles_room_id');

			// 送らないユーザIDをルーム配信ユーザIDから排除
			$notSendRoomUserIds = Hash::get($mailQueueUser, 'not_send_room_user_ids');
			$notSendRoomUserIds = explode('|', $notSendRoomUserIds);
			$userIds = array_diff($rolesRoomsUserIds, $notSendRoomUserIds);

			$users = $this->User->find('all', array(
				'recursive' => -1,
				'conditions' => array('id' => $userIds),
				'callbacks' => false,
			));

			$messages = $this->__sendUserEmails($users, $mailQueueLanguageId);

		} elseif (isset($userId)) {
			// --- user単位でメール配信
			$user = $this->User->find('first', array(
				'recursive' => -1,
				'conditions' => array('id' => $userId),
				'callbacks' => false,
			));

			$messages = $this->__sendUserEmails(array($user), $mailQueueLanguageId);

		} elseif (isset($toAddress)) {
			// --- メールアドレス単位でメール配信
			$this->setFrom($mailQueueLanguageId);
			parent::to($toAddress);
			parent::subject($this->subject);
			$messages = parent::send($this->body);
		}

		return $messages;
	}

/**
 * ユーザメールで送信
 *
 * @param array $users ユーザ 配列
 * @param int $mailQueueLanguageId キューの言語ID
 * @return array 送信メール文配列
 */
	private function __sendUserEmails($users, $mailQueueLanguageId) {
		$messages = null;

		foreach ($users as $user) {
			$userEmails = array(
				array(
					'email' => Hash::get($user, 'User.email'),
					'is_email_reception' => Hash::get($user, 'User.is_email_reception'),
				),
				array(
					'email' => Hash::get($user, 'User.moblie_mail'),
					'is_email_reception' => Hash::get($user, 'User.is_moblie_mail_reception'),
				),
			);

			foreach ($userEmails as $userEmail) {
				// 個人のメール受け取らない
				if (!$userEmail['is_email_reception']) {
					continue;
				}
				if (empty($userEmail['email'])) {
					$userId = Hash::get($user, 'User.id');
					$logMessage = "Email is empty. userId=$userId";
					CakeLog::debug($logMessage . ' [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
					continue;
				}
				$this->setFrom($mailQueueLanguageId);
				parent::to($userEmail['email']);
				parent::subject($this->subject);
				$messages[] = parent::send($this->body);
			}
		}
		return $messages;
	}

/**
 * メールを直送信
 *
 * @return bool 成功 or 失敗
 */
	public function sendMailDirect() {
		// 埋め込みタグ変換：定型文の埋め込みタグを変換して、メール生文にする
		$this->mailAssignTag->assignTagReplace();
		$this->body = $this->mailAssignTag->fixedPhraseBody;
		$this->subject = $this->mailAssignTag->fixedPhraseSubject;

		if ($this->body == '') {
			$logMessage = 'Mail body is empty.';
			LogError($logMessage . ' [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// メール本文の共通ヘッダー文、署名追加
		$this->body = $this->mailAssignTag->addHeaderAndSignature($this->body);

		// 改行対応
		$this->brReplace();

		parent::subject($this->subject);
		$messages = parent::send($this->body);
		return $messages;
	}
}
