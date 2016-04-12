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
App::uses('ConvertHtml', 'Mails.Utility');
App::uses('SiteSetting', 'SiteManager.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');

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
 * @var int メール本文の1行の最大文字数
 */
	const MAX_LINE_LENGTH = 300;

/**
 * SiteSettingの定型文の種類
 *
 * @var string 承認依頼通知
 * @var string 差戻し通知
 * @var string 承認完了通知
 */
	const
		SITE_SETTING_FIXED_PHRASE_APPROVAL = 'approval',
		SITE_SETTING_FIXED_PHRASE_DISAPPROVAL = 'disapproval',
		SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION = 'approval_completion';

/**
 * @var string 件名(定型文)
 */
	public $subject = null;

/**
 * @var string|array 本文(定型文)
 */
	public $body = null;

/**
 * @var array 埋め込みタグ
 */
	public $assignTags = array();

/**
 * @var array SiteSetting model data
 */
	public $siteSetting = null;

/**
 * Constructor
 *
 * @param array|string $config Array of configs, or string to load configs from email.php
 * @see CakeEmail::__construct()
 */
	public function __construct($config = null) {
		parent::__construct($config);

		$this->SiteSetting = ClassRegistry::init('SiteManager.SiteSetting');
		$this->MailSetting = ClassRegistry::init('Mails.MailSetting');
		$this->RoomsLanguage = ClassRegistry::init('Rooms.RoomsLanguage');
		$this->RolesRoomsUser = ClassRegistry::init('Rooms.RolesRoomsUser');
		$this->User = ClassRegistry::init('Users.user');
	}

/**
 * 初期設定 プラグイン用
 *
 * @param int $languageId 言語ID
 * @param string $pluginName プラグイン名
 * @return void
 * @see CakeEmail::$charset
 */
	public function initPlugin($languageId, $pluginName = null) {
		// SiteSettingからメール設定を取得する
		$this->siteSetting = $this->SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array(
				'Mail.from',
				'Mail.from_name',
				'Mail.messageType',
				'Mail.transport',
				'Mail.smtp.host',
				'Mail.smtp.port',
				'Mail.smtp.user',
				'Mail.smtp.pass',
				'App.site_name',
				'Workflow.approval_mail_subject',
				'Workflow.approval_mail_body',
				'Workflow.disapproval_mail_subject',
				'Workflow.disapproval_mail_body',
				'Workflow.approval_completion_mail_subject',
				'Workflow.approval_completion_mail_body',
				'Mail.body_header',
				'Mail.signature',
			)
		));

		$this->__initConfig();
		$this->__setTags($languageId, $pluginName);
	}

/**
 * 初期設定 Shell用
 *
 * @param array $siteSetting サイト設定データ
 * @param array $mailQueue メールキューデータ
 * @return void
 */
	public function initShell($siteSetting, $mailQueue) {
		$this->siteSetting = $siteSetting;
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
		$transport = Hash::get($this->siteSetting['Mail.transport'], '0.value');

		// SMTP, SMTPAuth
		if ($transport == SiteSetting::MAIL_TRANSPORT_SMTP) {
			$smtpHost = Hash::get($this->siteSetting['Mail.smtp.host'], '0.value');
			$smtpPort = Hash::get($this->siteSetting['Mail.smtp.port'], '0.value');
			$smtpUser = Hash::get($this->siteSetting['Mail.smtp.user'], '0.value');
			$smtpPass = Hash::get($this->siteSetting['Mail.smtp.pass'], '0.value');

			$config['transport'] = 'Smtp';
			$config['host'] = $smtpHost;
			$config['port'] = $smtpPort;

			// 値が無ければ：SMTP
			// 値があれば  ：SMTPAuth。なのでユーザ、パス設定
			if (!empty($smtpUser) && !empty($smtpPass)) {
				$config['username'] = $smtpUser;
				$config['password'] = $smtpPass;
			}

			// phpmail
		} elseif ($transport == SiteSetting::MAIL_TRANSPORT_PHPMAIL) {
			$config['transport'] = 'Mail';
		}

		parent::config($config);

		// html or text
		$messageType = Hash::get($this->siteSetting['Mail.messageType'], '0.value');
		parent::emailFormat($messageType);
	}

/**
 * From セット
 *
 * @param int $languageId 言語ID
 * @return void
 */
	public function setFrom($languageId) {
		$from = Hash::get($this->siteSetting['Mail.from'], '0.value');
		$fromName = Hash::get($this->siteSetting['Mail.from_name'], $languageId . '.value');
		parent::from($from, $fromName);

		// Return-Path
		$config = $this->config();
		$config['additionalParameters'] = '-f ' . $from;
		$this->config($config);
	}

/**
 * 初期設定 タグ
 *
 * @param int $languageId 言語ID
 * @param string $pluginName プラグイン名
 * @return void
 */
	private function __setTags($languageId, $pluginName = null) {
		if ($pluginName === null) {
			$pluginName = Current::read('Plugin.name');
		}
		$from = Hash::get($this->siteSetting['Mail.from'], '0.value');
		$fromName = Hash::get($this->siteSetting['Mail.from_name'], $languageId . '.value');
		$siteName = Hash::get($this->siteSetting['App.site_name'], $languageId . '.value');
		$bodyHeader = Hash::get($this->siteSetting['Mail.body_header'], $languageId . '.value');
		$signature = Hash::get($this->siteSetting['Mail.signature'], $languageId . '.value');

		$netCommonsTime = new NetCommonsTime();
		$siteimezone = $netCommonsTime->getSiteTimezone();
		$now = NetCommonsTime::getNowDatetime();
		$date = new DateTime($now);
		$date->setTimezone(new DateTimeZone($siteimezone));
		$siteNow = $date->format('Y/m/d H:i:s');

		$this->assignTag('X-FROM_EMAIL', $from);
		$this->assignTag('X-FROM_NAME', htmlspecialchars($fromName));
		$this->assignTag('X-SITE_NAME', htmlspecialchars($siteName));
		$this->assignTag('X-SITE_URL', Router::fullbaseUrl());
		$this->assignTag('X-PLUGIN_NAME', htmlspecialchars($pluginName));
		$this->assignTag('X-BLOCK_NAME', htmlspecialchars(Current::read('Block.name')));
		$this->assignTag('X-USER', htmlspecialchars(Current::read('User.handlename')));
		$this->assignTag('X-TO_DATE', $siteNow);
		$this->assignTag('X-BODY_HEADER', $bodyHeader);
		$this->assignTag('X-SIGNATURE', $signature);

		// X-ROOMタグ
		$roomId = Current::read('Room.id');
		$roomsLanguage = $this->RoomsLanguage->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'room_id' => $roomId,
				'language_id' => $languageId,
			),
			'callbacks' => false,
		));
		$roomName = Hash::get($roomsLanguage, 'RoomsLanguage.name');
		$this->assignTag('X-ROOM', htmlspecialchars($roomName));
	}

/**
 * プラグインの定型文 セット
 *
 * @param array $mailSetting メール設定データ
 * @return void
 */
	public function setMailFixedPhrasePlugin($mailSetting) {
		$subject = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_subject');
		$body = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_body');
		$replyTo = Hash::get($mailSetting, 'MailSetting.replay_to');

		// 定型文をセット
		$this->setSubject($subject);
		$this->setBody($body);

		$this->setReplyTo($replyTo);
	}

/**
 * サイト設定の定型文 セット
 *
 * @param int $languageId 言語ID
 * @param string $fixedPhraseType 定型文の種類
 * @param array $mailSetting プラグイン側のメール設定データ
 * @return void
 */
	public function setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType, $mailSetting = null) {
		$subject = Hash::get($this->siteSetting['Workflow.' . $fixedPhraseType . '_mail_subject'], $languageId . '.value');
		$body = Hash::get($this->siteSetting['Workflow.' . $fixedPhraseType . '_mail_body'], $languageId . '.value');

		// 定型文をセット
		$this->setSubject($subject);
		$this->setBody($body);

		if ($mailSetting === null) {
			return;
		}

		$pluginSubject = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_subject');
		$pluginBody = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_body');
		$this->assignTag('X-PLUGIN_MAIL_SUBJECT', $pluginSubject);
		$this->assignTag('X-PLUGIN_MAIL_BODY', $pluginBody);
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
		$replyTo = Hash::get($mailQueue, 'MailQueue.replay_to');

		// 定型文をセット
		$this->setSubject($subject);
		$this->setBody($body);

		// 返信先アドレス
		if (! empty($replyTo)) {
			parent::replyTo($replyTo);
		}
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
		//$this->body = str_replace("\n", '<br />', $this->body). '<br />';

		//		$container =& DIContainerFactory::getContainer();
		//		$commonMain =& $container->getComponent('commonMain');
		//		$escapeText =& $commonMain->registerClass(WEBAPP_DIR . '/components/escape/Text.class.php', 'Escape_Text', 'escapeText');
		//
		//		$this->body = $escapeText->escapeWysiwyg($this->body);
	}

/**
 * 埋め込みタグの追加
 *
 * @param string $tag タグ
 * @param string $value 変換する値
 * @return array タグ
 */
	public function assignTag($tag, $value = null) {
		if (empty($tag)) {
			return;
		}
		// $tagあり、$valueなしで、タグの値取得
		if ($value === null) {
			return Hash::get($this->assignTags, $tag);
		}
		// タグの両端空白なくして、大文字に変換
		$tag = strtoupper(trim($tag));

		// 頭に X- 付タグならセット
		if (substr($tag, 0, 2) == 'X-') {
			$this->assignTags[$tag] = $value;
		}
	}

/**
 * 埋め込みタグを配列で追加
 *
 * @param array $tags タグ配列
 * @return void
 */
	public function assignTags($tags) {
		foreach ($tags as $key => $value) {
			$this->assignTag($key, $value);
		}
	}

/**
 * 埋め込みタグ変換：定型文の埋め込みタグを変換して、メール生文にする
 *
 * @return array タグ
 */
	public function assignTagReplace() {
		$convertHtml = new ConvertHtml();

		// 承認系メールのタグは先に置換
		if (isset($this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->assignTags['X-PLUGIN_MAIL_BODY'])) {
			$this->body = str_replace('{X-PLUGIN_MAIL_BODY}', $this->assignTags['X-PLUGIN_MAIL_BODY'], $this->body);
			$this->subject = str_replace('{X-PLUGIN_MAIL_SUBJECT}', $this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->subject);
			unset($this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->assignTags['X-PLUGIN_MAIL_BODY']);
		}

		// メール本文の共通ヘッダー文、署名追加
		$this->body = $this->assignTags['X-BODY_HEADER'] . "\n" . $this->body . "\n" . $this->assignTags['X-SIGNATURE'];
		unset($this->assignTags['X-BODY_HEADER'], $this->assignTags['X-SIGNATURE']);

		foreach ($this->assignTags as $key => $value) {
			if (substr($value, 0, 4) == 'X-TO' || $key == 'X-URL') {
				continue;
			}

			if (parent::emailFormat() == 'text') {
				$this->body = str_replace('{' . $key . '}', $convertHtml->convertHtmlToText($value), $this->body);
			} else {
				$this->body = str_replace('{' . $key . '}', $value, $this->body);
			}
			$this->subject = str_replace('{' . $key . '}', $convertHtml->convertHtmlToText($value), $this->subject);
		}

		$this->body = str_replace("\r\n", "\n", $this->body);
		$this->body = str_replace("\r", "\n", $this->body);
		//$this->body = $this->insertNewLine($this->body);
		// テキストのブロックを決められた幅や折り返す
		/** @link http://book.cakephp.org/2.0/ja/core-utility-libraries/string.html#CakeText::wrap */
		$this->body = CakeText::wrap($this->body, $this::MAX_LINE_LENGTH);

		if (Hash::get($this->assignTags, 'X-URL')) {
			if (parent::emailFormat() == 'text') {
				$this->body = str_replace('{X-URL}', $this->assignTags['X-URL'], $this->body);
			} else {
				$this->body = str_replace('{X-URL}', '<a href=\'' . $this->assignTags['X-URL'] . '\'>' . $this->assignTags['X-URL'] . '</a>', $this->body);
			}
		}
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
		if (empty($this->siteSetting)) {
			LogError('SiteSetting Data is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}
		if ($this->body == '') {
			LogError('Mail body is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 改行対応
		$this->brReplace();

		// --- 3パターン対応
		$roomId = Hash::get($mailQueueUser, 'room_id');
		$userId = Hash::get($mailQueueUser, 'user_id');
		$toAddress = Hash::get($mailQueueUser, 'to_address');
		if ($roomId === null && $userId === null && $toAddress === null) {
			LogError('Mail delivery destination is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$messages = null;
		if (isset($roomId)) {
			// --- ルーム単位でメール配信
			$blockKey = $mailQueueUser['block_key'];
			$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
			$permissions = $WorkflowComponent->getBlockRolePermissions(array('mail_content_receivable'), $roomId, $blockKey);

			$roleKeys = array_keys($permissions['BlockRolePermissions']['mail_content_receivable']);
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
			// shell直だと モデル名 user, コントローラーからexec呼出だと Userだった。aliasで取得
			$userEmails = array(
				array(
					'email' => Hash::get($user, $this->User->alias . '.email'),
					'is_email_reception' => Hash::get($user, $this->User->alias . '.is_email_reception'),
				),
				array(
					'email' => Hash::get($user, $this->User->alias . '.moblie_mail'),
					'is_email_reception' => Hash::get($user, $this->User->alias . '.is_moblie_mail_reception'),
				),
			);

			foreach ($userEmails as $userEmail) {
				// 個人のメール受け取らない
				if (!$userEmail['is_email_reception']) {
					continue;
				}
				if (empty($userEmail['email'])) {
					$userId = Hash::get($user, $this->User->alias . '.id');
					CakeLog::debug("Email is empty. userId=$userId [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
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
		if (empty($this->siteSetting)) {
			LogError('SiteSetting Data is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}
		if ($this->body == '') {
			LogError('Mail body is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 埋め込みタグ変換：定型文の埋め込みタグを変換して、メール生文にする
		$this->assignTagReplace();

		// 改行対応
		$this->brReplace();

		parent::subject($this->subject);
		$messages = parent::send($this->body);
		return $messages;
	}
}
