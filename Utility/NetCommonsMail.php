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
 * @var string 差戻し通知
 * @var string 承認完了通知
 */
	const
		//SITE_SETTING_FIXED_PHRASE_APPROVAL = 'approval',
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

	///**
	// * @var int メールで通知する
	// */
	//	public $isMailSend = null;

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
				//'Workflow.approval_mail_subject',
				//'Workflow.approval_mail_body',
				'Workflow.disapproval_mail_subject',
				'Workflow.disapproval_mail_body',
				'Workflow.approval_completion_mail_subject',
				'Workflow.approval_completion_mail_body',
				'Mail.body_header',		// まだ対応処理書いてない
				'Mail.signature',		// まだ対応処理書いてない
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
		//$workflowComment = Hash::get($data, 'WorkflowComment.comment');

		$this->assignTag('X-FROM_EMAIL', $from);
		$this->assignTag('X-FROM_NAME', htmlspecialchars($fromName));
		$this->assignTag('X-SITE_NAME', htmlspecialchars($siteName));
		$this->assignTag('X-SITE_URL', Router::fullbaseUrl());
		$this->assignTag('X-PLUGIN_NAME', htmlspecialchars($pluginName));
		$this->assignTag('X-BLOCK_NAME', htmlspecialchars(Current::read('Block.name')));
		$this->assignTag('X-USER', htmlspecialchars(AuthComponent::user('handlename')));
		$this->assignTag('X-TO_DATE', date('Y/m/d H:i:s'));
		//$this->assignTag('X-WORKFLOW_COMMENT', $workflowComment);

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
 * @return void
 */
	public function setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType) {
		$subject = Hash::get($this->siteSetting['Workflow.' . $fixedPhraseType . '_mail_subject'], $languageId . '.value');
		$body = Hash::get($this->siteSetting['Workflow.' . $fixedPhraseType . '_mail_body'], $languageId . '.value');

		// 定型文をセット
		$this->setSubject($subject);
		$this->setBody($body);
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

	///**
	// * メール送信する定型文をセット(システム管理系)
	// *
	// * @param string $typeKey メールの種類
	// * @return void
	// */
	//	private function __setMailSettingSystem($typeKey) {
	//		// TODOO ここ見直し？。sitesettingから取得するfunction必要
	//		$mailSetting = $this->MailSetting->getMailSettingSystem($typeKey);
	//		//$this->__setMailSetting($mailSetting);
	//	}

	///**
	// * メール送信する定型文をセット
	// *
	// * @param array $mailSetting メール設定データ
	// * @return void
	// */
	//	private function __setMailSetting($mailSetting) {
	//		//public function setSendMailSetting($blockKey = null, $pluginKey = null, $typeKey = 'contents') {
	//		//public function setSendMailSetting($blockKey, $typeKey = 'contents') {
	//		if (empty($mailSetting)) {
	//			return;
	//		}
	//
	//		// メール通知フラグをセット
	//		//$this->isMailSend = Hash::get($mailSetting, 'MailSetting.is_mail_send');
	//
	//		$subject = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_subject');
	//		$body = Hash::get($mailSetting, 'MailSetting.mail_fixed_phrase_body');
	//		$replyTo = Hash::get($mailSetting, 'MailSetting.replay_to');
	//
	//		// 定型文をセット
	//		$this->setSubject($subject);
	//		$this->setBody($body);
	//
	//		// 返信先アドレス
	//		if (! empty($replyTo)) {
	//			parent::replyTo($replyTo);
	//		}
	//	}

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

	//	/**
	//	 * 重要度をセットする
	//	 *
	//	 * @param	string	$value	重要度
	//	 *
	//	 * @access	public
	//	 */
	//	function setPriority($value)
	//	{
	//		$this->priority = trim($value);
	//	}

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
		//$this->body = str_replace('\n', '<br />', $this->body). '<br />';

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
		//public function assignTagReplace($body, $subject) {
		$convertHtml = new ConvertHtml();

		foreach ($this->assignTags as $key => $value) {
			if (substr($value, 0, 4) == 'X-TO' || $key == 'X-URL') {
				continue;
			}
			$this->body = str_replace('{' . $key . '}', $value, $this->body);
			$this->subject = str_replace('{' . $key . '}', $convertHtml->convertHtmlToText($value), $this->subject);
		}

		$this->body = str_replace('\r\n', '\n', $this->body);
		$this->body = str_replace('\r', '\n', $this->body);
		//$this->body = str_replace('\n', $this->_LE, $this->body);
		$this->body = $this->insertNewLine($this->body);

		//		if(isset($this->assignTags['X-URL'])) {
		//			$this->body = str_replace('{X-URL}', '<a href=\''. $this->assignTags['X-URL']. '\'>'. $this->assignTags['X-URL']. '</a>', $this->body);
		//			$mobile_body = str_replace('{X-URL}', $this->assignTags['X-URL'], $this->body);
		//			unset($this->assignTags['X-URL']);
		//		} else {
		//			$mobile_body = $this->body;
		//		}
		//		$mobile_body = $convertHtml->convertHtmlToText($mobile_body);
		//		$mobile_body = $this->insertNewLine($mobile_body);

		if (Hash::get($this->assignTags, 'X-URL')) {
			if (parent::emailFormat() == 'text') {
				$this->body = str_replace('{X-URL}', $this->assignTags['X-URL'], $this->body);
			} else {
				$this->body = str_replace('{X-URL}', '<a href=\'' . $this->assignTags['X-URL'] . '\'>' . $this->assignTags['X-URL'] . '</a>', $this->body);
			}
		}

		// URLの置換は一度きり
		//unset($this->assignTags['X-URL']);
	}

/**
 * 改行対応
 *
 * @return void
 */
	public function brReplace() {
		if (parent::emailFormat() == 'text') {
			// text形式は配列にすると改行される
			$this->body = explode('\n', $this->body);
		} else {
			$this->body = str_replace('\n', '<br />', $this->body);
		}
	}

/**
 * 1行の最大文字数で、改行入れて本文整形
 *
 * @param string $body 本文
 * @return string 整形した本文
 */
	public function insertNewLine($body) {
		//$lines = explode($this->_LE, $body);
		$lines = explode('\n', $body);
		//$pos = 0;
		//$max_line_length = 300;
		$linesOut = array();

		while (list(, $line) = each($lines)) {
			// 1行が300文字以下になったら抜ける
			while (mb_strlen($line) > $this::MAX_LINE_LENGTH) {
				// 1行300文字で改行。なので配列にセット。
				// 1行300文字まで取得、< があるか
				$pos = strrpos(mb_substr($line, 0, $this::MAX_LINE_LENGTH), '<');
				// 1行300文字の中に '<' ありなら、途中で改行
				if ($pos > 0) {
					$linesOut[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					$linesOut[] = mb_substr($line, 0, $this::MAX_LINE_LENGTH);
					$line = mb_substr($line, $this::MAX_LINE_LENGTH);
				}
			}
			$linesOut[] = $line;
		}
		//return implode($this->_LE, $linesOut);
		return implode('\n', $linesOut);
	}

/**
 * キューからメール送信
 *
 * @param array $mailQueueUser メール配信先データ
 * @param int $mailQueueLanguageId キューの言語ID
 * @return bool true:正常,false:エラー
 */
	public function sendQueueMail($mailQueueUser, $mailQueueLanguageId) {
		if (empty($this->siteSetting)) {
			LogError('SiteSetting Data is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}
		if ($this->body == '') {
			LogError('Mail body is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			//CakeLog::debug('MailQueueUser - ' . print_r($mailQueueUser, true));
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
			//CakeLog::debug('MailQueueUser - ' . print_r($mailQueueUser, true));
			return false;
		}

		if (isset($roomId)) {
			// --- ルーム単位でメール配信
			$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
			$permissions = $WorkflowComponent->getBlockRolePermissions(array('mail_content_receivable'));

			$roleKeys = array_keys($permissions['BlockRolePermissions']['mail_content_receivable']);
			$conditions = array(
				'Room.id' => $roomId,
				'RolesRoom.role_key' => $roleKeys,
			);
			$rolesRoomsUsers = $this->RolesRoomsUser->getRolesRoomsUsers($conditions);
			$rolesRoomsUserIds = Hash::extract($rolesRoomsUsers, '{n}.RolesRoomsUser.roles_room_id');

			$users = $this->User->find('all', array(
				'recursive' => -1,
				'conditions' => array('id' => $rolesRoomsUserIds),
				'callbacks' => false,
			));

			foreach ($users as $user) {
				$userEmail = Hash::get($user, 'user.email');
				if (empty($userEmail)) {
					CakeLog::debug("Email is empty. [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
					continue;
				}
				$this->setFrom($mailQueueLanguageId);
				parent::to($userEmail);
				parent::subject($this->subject);
				$messages[] = parent::send($this->body);
			}

		} elseif (isset($userId)) {
			// --- user単位でメール配信
			$user = $this->User->find('first', array(
				'recursive' => -1,
				'conditions' => array('id' => $userId),
				'callbacks' => false,
			));

			$userEmail = Hash::get($user, 'user.email');
			if (empty($userEmail)) {
				CakeLog::debug("Email is empty. userId=$userId [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				//CakeLog::debug('MailQueueUser - ' . print_r($mailQueueUser, true));
				return false;
			}
			$this->setFrom($mailQueueLanguageId);
			parent::to($userEmail);
			parent::subject($this->subject);
			$messages = parent::send($this->body);

		} elseif (isset($toAddress)) {
			// --- メールアドレス単位でメール配信
			$this->setFrom($mailQueueLanguageId);
			parent::to($toAddress);
			parent::subject($this->subject);
			$messages = parent::send($this->body);
		}

		return $messages;

		// 重要度セット
		//		if (!empty($this->priority)) {
		//			$this->headers[] = 'X-Priority: '. $this->priority;
		//		}
		//		$this->headers[] = 'X-Mailer: PHP/'. phpversion();
		//		$this->headers[] = 'Return-Path: '. $this->fromEmail;

		// タグセット【済】
		//		$container =& DIContainerFactory::getContainer();
		//		$configView =& $container->getComponent('configView');
		//		$this->assign('X-FROM_EMAIL', $this->fromEmail);
		//		$this->assign('X-FROM_NAME', htmlspecialchars($this->fromName));
		//		$confs = $configView->getConfigByConfname(_SYS_CONF_MODID, 'sitename');
		//		$this->assign('X-SITE_NAME', htmlspecialchars($confs['conf_value']));
		//		$this->assign('X-SITE_URL', BASE_URL.INDEX_FILE_NAME);
		//
		//		$session =& $container->getComponent('Session');
		//		if (!isset($this->_assignedTags['X-ROOM'])) {
		//			$request =& $container->getComponent('Request');
		//			$pageView =& $container->getComponent('pagesView');
		//			$roomId = $request->getParameter('room_id');
		//			$pages = $pageView->getPageById($roomId);
		//
		//			$this->assign('X-ROOM', htmlspecialchars($pages['page_name']));
		//		}
		//		if (!isset($this->_assignedTags['X-USER'])) {
		//			$this->assign('X-USER', htmlspecialchars($session->getParameter('_handle')));
		//		}

		// タグ置換【済】
		//		$commonMain =& $container->getComponent('commonMain');
		//		$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', 'Convert_Html', 'convertHtml');
		//		foreach ($this->_assignedTags as $k => $v) {
		//			if (substr($k, 0, 4) == 'X-TO' || $k == 'X-URL') {
		//				continue;
		//			}
		//
		//			$this->body = str_replace('{'.$k.'}', $v, $this->body);
		//			$this->subject = str_replace('{'.$k.'}', $convertHtml->convertHtmlToText($v), $this->subject);
		//		}
		//		$this->body = str_replace('\r\n', '\n', $this->body);
		//		$this->body = str_replace('\r', '\n', $this->body);
		//		$this->body = str_replace('\n', $this->_LE, $this->body);
		//		$this->body = $this->_insertNewLine($this->body);
		//	if(isset($this->_assignedTags['X-URL'])) {
		//			$this->body = str_replace('{X-URL}', '<a href=\''. $this->_assignedTags['X-URL']. '\'>'. $this->_assignedTags['X-URL']. '</a>', $this->body);
		//			$mobile_body = str_replace('{X-URL}', $this->_assignedTags['X-URL'], $this->body);
		//			unset($this->_assignedTags['X-URL']);
		//		} else {
		//			$mobile_body = $this->body;
		//		}
		//		$mobile_body = $convertHtml->convertHtmlToText($mobile_body);
		//		$mobile_body = $this->_insertNewLine($mobile_body);

		//		if(count($this->toUsers) > 0) {
		//			foreach ($this->toUsers as $user) {

		// ループ内：タグ置換
		//				$email = $user['email'];
		//				if (empty($email)) {
		//					continue;
		//				}
		//				if(isset($this->_assignedTags['X-TO_DATE'])) {
		//					$date = timezone_date_format($this->_assignedTags['X-TO_DATE'], _FULL_DATE_FORMAT);
		//				} else {
		//					$date = '';
		//				}
		//				if(!isset($user['handle'])) {
		//					$user['handle'] = '';
		//				}
		//
		//				// type (html(email) or text(mobile_email))
		//				if(!isset($user['type'])) {
		//					$user['type'] = 'html';
		//				}
		//				if(empty($user['lang_dirname'])) {
		//					$user['lang_dirname'] = $session->getParameter('_lang');
		//					if(!isset($user['lang_dirname']) || $user['lang_dirname'] == '') {
		//						$user['lang_dirname'] = 'japanese';
		//					}
		//				}
		//				$subject = $this->subject;
		//				if($this->isHTML == true && ($user['type'] == 'html' || $user['type'] == 'email')) {
		//					// htmlメール
		//					$this->_mailer->IsHTML(true);
		//					$body = $this->body;
		//					$body = str_replace('{X-TO_HANDLE}', htmlspecialchars($user['handle']), $body);
		//				} else {
		//					// テキストメール
		//					$this->_mailer->IsHTML(false);
		//					$body = $mobile_body;
		//					$body = str_replace('{X-TO_HANDLE}', $user['handle'], $body);
		//				}
		//
		//				$subject = str_replace('{X-TO_HANDLE}', $user['handle'], $subject);
		//				$subject = str_replace('{X-TO_EMAIL}', $email, $subject);
		//				$subject = str_replace('{X-TO_DATE}', $date, $subject);
		//				$body = str_replace('{X-TO_EMAIL}', $email, $body);
		//				$body = str_replace('{X-TO_DATE}', $date, $body);

		// ループ内：本文、件名等セット
		//				$localFilePath = WEBAPP_DIR. '/language/'. strtolower($user['lang_dirname']). '/Mailer_Local.php';
		//				if (file_exists($localFilePath)) {
		//					require_once($localFilePath);
		//
		//					$className = 'Mailer_Local_' . ucfirst(strtolower($user['lang_dirname']));
		//					$local =& new $className();
		//
		//					$this->_mailer->CharSet = $local->charSet;
		//					$this->_mailer->Encoding = $local->encoding;
		//					if (!empty($this->fromName)) {
		//						$this->_mailer->FromName = $local->encodeFromName($this->fromName);
		//					}
		//					$this->_mailer->Subject = $local->encodeSubject($subject);
		//					$this->_mailer->Body = $local->encodeBody($body);
		//				} else {
		//					$this->_mailer->CharSet = $this->charSet;
		//					$this->_mailer->Encoding = $this->encoding;
		//					if (!empty($this->fromName)) {
		//						$this->_mailer->FromName = $this->fromName;
		//					}
		//					$this->_mailer->Subject = $subject;
		//					$this->_mailer->Body = $body;
		//				}
		//
		//				$this->_mailer->ClearAllRecipients();
		//				$this->_mailer->AddAddress($email);
		//				if (!empty($this->fromEmail)) {
		//					$this->_mailer->From = $this->fromEmail;
		//				}
		//				$this->_mailer->ClearCustomHeaders();
		//				foreach ($this->headers as $header) {
		//					$this->_mailer->AddCustomHeader($header);
		//				}

		// ループ内：メール送信
		//				if (!$this->_mailer->Send()) {
		//					$this->_log->warn($email. '宛にメールを送信できませんでした/'. $this->_mailer->ErrorInfo, 'Mailer#send');
		//				} else {
		//					$this->_log->trace($email. '宛にメールを送信しました', 'Mailer#send');
		//				}
		//
		//				//flush();	// ob_contentが送られてしまうためコメント

		//			}
		//		}
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
