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

/**
 * NetCommonsメール Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 */
class NetCommonsMail extends CakeEmail {

/**
 * @var int メール本文の1行の最大文字数
 */
	const MAX_LINE_LENGTH = 300;

/**
 * @var bool デバッグON
 */
	//const IS_DEBUG = false;
	const IS_DEBUG = true;

/**
 * @var string 件名(定型文)
 */
	public $subject = null;

/**
 * @var string|array 本文(定型文)
 */
	public $body = null;

/**
 * @var int メールで通知する
 */
	public $isMailSend = null;

/**
 * @var array 変換タグ
 */
	public $assignTags = array();

/**
 * @var array SiteSetting model data
 */
	public $siteSettingData = null;

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
		$this->Language = ClassRegistry::init('M17n.Language');

		// SiteSettingからメール設定を取得する
		/** @see SiteSetting::getSiteSettingForEdit() */
		$this->siteSettingData = $this->SiteSetting->getSiteSettingForEdit(array(
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
				'Config.language',
			)
		));
	}

/**
 * 初期設定
 *
 * @param array $data 投稿データ
 * @param string $typeKey メール定型文の種類
 * @return void
 * @see CakeEmail::$charset
 */
	public function initPlugin($data, $typeKey = 'contents') {
		$languageId = Current::read('Language.id');		//仮
		$this->__initConfig($languageId);

		$this->__initTags($data);
		$this->__setMailSettingPlugin($typeKey);
	}

/**
 * 初期設定
 * まだ仮
 *
 * @return void
 */
	public function initShell() {
		$languageCode = Hash::get($this->siteSettingData['Config.language'], '0.value');

		// Language.id取得
		$languageData = $this->Language->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'Language.code' => $languageCode,
			)
		));
		$languageId = Hash::get($languageData, 'Language.id');

		//$languageId = 2; 仮
		$this->__initConfig($languageId);
		//$this->__initTags($siteSettingData, $data);

		// 定型文をセット
//		$this->setSubject($subject);
//		$this->setBody($body);
//
//		// 返信先アドレス
//		if (! empty($replyTo)) {
//			parent::replyTo($replyTo);
//		}
	}

/**
 * 初期設定 メールのコンフィグ
 *
 * @return void
 */
	private function __initConfig($languageId) {
		//private function __initConfig($siteSettingData) {
		$from = Hash::get($this->siteSettingData['Mail.from'], '0.value');
		$fromName = Hash::get($this->siteSettingData['Mail.from_name'], $languageId . '.value');

		$config = array();
		$config['from'] = array($from => $fromName);

		$transport = Hash::get($this->siteSettingData['Mail.transport'], '0.value');

		// SMTP, SMTPAuth
		if ($transport == SiteSetting::MAIL_TRANSPORT_SMTP) {
			$smtpHost = Hash::get($this->siteSettingData['Mail.smtp.host'], '0.value');
			$smtpPort = Hash::get($this->siteSettingData['Mail.smtp.port'], '0.value');
			$smtpUser = Hash::get($this->siteSettingData['Mail.smtp.user'], '0.value');
			$smtpPass = Hash::get($this->siteSettingData['Mail.smtp.pass'], '0.value');

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

		if (self::IS_DEBUG) {
			//送信しない（デバッグ用）
			$config['transport'] = 'Debug';
		}
		//CakeLog::debug(print_r($config, true));
		parent::config($config);

		// html or text
		$messageType = Hash::get($this->siteSettingData['Mail.messageType'], '0.value');
		parent::emailFormat($messageType);
	}

/**
 * 初期設定 タグ
 *
 * @param array $data 投稿データ
 * @return void
 */
	private function __initTags($data) {
		//private function __initTags($siteSettingData, $data) {
		// シェルから呼び出した時に$languageIdとれない。要検討
		//$languageId = Current::read('Language.id');		//仮
		$languageId = 2;

		$from = Hash::get($this->siteSettingData['Mail.from'], '0.value');
		$fromName = Hash::get($this->siteSettingData['Mail.from_name'], $languageId . '.value');
		$siteName = Hash::get($this->siteSettingData['App.site_name'], $languageId . '.value');

		// タグセット
		$this->assignTag("X-FROM_EMAIL", $from);
		$this->assignTag("X-FROM_NAME", htmlspecialchars($fromName));
		$this->assignTag("X-SITE_NAME", htmlspecialchars($siteName));
		$this->assignTag("X-SITE_URL", Router::fullbaseUrl());
		$this->assignTag("X-PLUGIN_NAME", htmlspecialchars(Current::read('Plugin.name')));
		$this->assignTag("X-BLOCK_NAME", htmlspecialchars(Current::read('Block.name')));
		$this->assignTag("X-USER", htmlspecialchars(AuthComponent::user('handlename')));
		$this->assignTag("X-TO_DATE", date('Y/m/d H:i:s'));
		$this->assignTag("X-APPROVAL_COMMENT", $data['WorkflowComment']['comment']);
	}

/**
 * メール送信する定型文をセット(通常のプラグイン)
 *
 * @param string $typeKey メールの種類
 * @return void
 */
	private function __setMailSettingPlugin($typeKey) {
		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingData = $this->MailSetting->getMailSettingPlugin($typeKey);
		$this->__setMailSetting($mailSettingData);
	}

/**
 * メール送信する定型文をセット(システム管理系)
 *
 * @param string $typeKey メールの種類
 * @return void
 */
	private function __setMailSettingSystem($typeKey) {
		/** @see MailSetting::getMailSettingSystem() */
		$mailSettingData = $this->MailSetting->getMailSettingSystem($typeKey);
		$this->__setMailSetting($mailSettingData);
	}

/**
 * メール送信する定型文をセット
 *
 * @param array $mailSettingData メール設定データ
 * @return void
 */
	private function __setMailSetting($mailSettingData) {
		//public function setSendMailSetting($blockKey = null, $pluginKey = null, $typeKey = 'contents') {
		//public function setSendMailSetting($blockKey, $typeKey = 'contents') {
		if (empty($mailSettingData)) {
			return;
		}

		// メール通知フラグをセット
		$this->isMailSend = Hash::get($mailSettingData, 'MailSetting.is_mail_send');

		$subject = Hash::get($mailSettingData, 'MailSetting.mail_fixed_phrase_subject');
		$body = Hash::get($mailSettingData, 'MailSetting.mail_fixed_phrase_body');
		$replyTo = Hash::get($mailSettingData, 'MailSetting.replay_to');

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
		//$this->body = str_replace("\n", "<br />", $this->body). "<br />";

		//		$container =& DIContainerFactory::getContainer();
		//		$commonMain =& $container->getComponent('commonMain');
		//		$escapeText =& $commonMain->registerClass(WEBAPP_DIR . '/components/escape/Text.class.php', 'Escape_Text', 'escapeText');
		//
		//		$this->body = $escapeText->escapeWysiwyg($this->body);
	}

/**
 * メールを送信する
 *
 * @return bool true:正常,false:エラー
 */
	function sendMail(){
//		if($this->setting_config == false) {
//			$this->_log->error("システム管理の設定が正しくありません", "Mailer#send");
//			return false;
//		}
		if ($this->body == "") {
			LogError('メール本文がありません [' . __METHOD__ . '] ' . __FILE__ .' (line '. __LINE__ .')');
			return false;
		}

//		if (!empty($this->priority)) {
//			$this->headers[] = "X-Priority: ". $this->priority;
//		}
//		$this->headers[] = "X-Mailer: PHP/". phpversion();
//		$this->headers[] = "Return-Path: ". $this->fromEmail;
//
//		$container =& DIContainerFactory::getContainer();
//		$configView =& $container->getComponent("configView");
//		$this->assign("X-FROM_EMAIL", $this->fromEmail);
//		$this->assign("X-FROM_NAME", htmlspecialchars($this->fromName));
//		$confs = $configView->getConfigByConfname(_SYS_CONF_MODID, "sitename");
//		$this->assign("X-SITE_NAME", htmlspecialchars($confs["conf_value"]));
//		$this->assign("X-SITE_URL", BASE_URL.INDEX_FILE_NAME);
//
//		$session =& $container->getComponent("Session");
//		if (!isset($this->_assignedTags['X-ROOM'])) {
//			$request =& $container->getComponent("Request");
//			$pageView =& $container->getComponent("pagesView");
//			$roomId = $request->getParameter("room_id");
//			$pages = $pageView->getPageById($roomId);
//
//			$this->assign("X-ROOM", htmlspecialchars($pages["page_name"]));
//		}
//		if (!isset($this->_assignedTags["X-USER"])) {
//			$this->assign("X-USER", htmlspecialchars($session->getParameter("_handle")));
//		}
//
//		$commonMain =& $container->getComponent("commonMain");
//		$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
//		foreach ($this->_assignedTags as $k => $v) {
//			if (substr($k, 0, 4) == "X-TO" || $k == "X-URL") {
//				continue;
//			}
//
//			$this->body = str_replace("{".$k."}", $v, $this->body);
//			$this->subject = str_replace("{".$k."}", $convertHtml->convertHtmlToText($v), $this->subject);
//		}
//		$this->body = str_replace("\r\n", "\n", $this->body);
//		$this->body = str_replace("\r", "\n", $this->body);
//		$this->body = str_replace("\n", $this->_LE, $this->body);
//		$this->body = $this->_insertNewLine($this->body);
//	if(isset($this->_assignedTags["X-URL"])) {
//			$this->body = str_replace("{X-URL}", "<a href=\"". $this->_assignedTags["X-URL"]. "\">". $this->_assignedTags["X-URL"]. "</a>", $this->body);
//			$mobile_body = str_replace("{X-URL}", $this->_assignedTags["X-URL"], $this->body);
//			unset($this->_assignedTags["X-URL"]);
//		} else {
//			$mobile_body = $this->body;
//		}
//		$mobile_body = $convertHtml->convertHtmlToText($mobile_body);
//		$mobile_body = $this->_insertNewLine($mobile_body);
//		if(count($this->toUsers) > 0) {
//			foreach ($this->toUsers as $user) {
//				$email = $user["email"];
//				if (empty($email)) {
//					continue;
//				}
//				if(isset($this->_assignedTags["X-TO_DATE"])) {
//					$date = timezone_date_format($this->_assignedTags["X-TO_DATE"], _FULL_DATE_FORMAT);
//				} else {
//					$date = "";
//				}
//				if(!isset($user["handle"])) {
//					$user["handle"] = "";
//				}
//
//				// type (html(email) or text(mobile_email))
//				if(!isset($user["type"])) {
//					$user["type"] = "html";
//				}
//				if(empty($user["lang_dirname"])) {
//					$user["lang_dirname"] = $session->getParameter("_lang");
//					if(!isset($user["lang_dirname"]) || $user["lang_dirname"] == "") {
//						$user["lang_dirname"] = "japanese";
//					}
//				}
//				$subject = $this->subject;
//				if($this->isHTML == true && ($user["type"] == "html" || $user["type"] == "email")) {
//					// htmlメール
//					$this->_mailer->IsHTML(true);
//					$body = $this->body;
//					$body = str_replace("{X-TO_HANDLE}", htmlspecialchars($user["handle"]), $body);
//				} else {
//					// テキストメール
//					$this->_mailer->IsHTML(false);
//					$body = $mobile_body;
//					$body = str_replace("{X-TO_HANDLE}", $user["handle"], $body);
//				}
//
//				$subject = str_replace("{X-TO_HANDLE}", $user["handle"], $subject);
//				$subject = str_replace("{X-TO_EMAIL}", $email, $subject);
//				$subject = str_replace("{X-TO_DATE}", $date, $subject);
//				$body = str_replace("{X-TO_EMAIL}", $email, $body);
//				$body = str_replace("{X-TO_DATE}", $date, $body);
//
//				$localFilePath = WEBAPP_DIR. "/language/". strtolower($user["lang_dirname"]). "/Mailer_Local.php";
//				if (file_exists($localFilePath)) {
//					require_once($localFilePath);
//
//					$className = "Mailer_Local_" . ucfirst(strtolower($user["lang_dirname"]));
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
//
//				if (!$this->_mailer->Send()) {
//					$this->_log->warn($email. "宛にメールを送信できませんでした/". $this->_mailer->ErrorInfo, "Mailer#send");
//				} else {
//					$this->_log->trace($email. "宛にメールを送信しました", "Mailer#send");
//				}
//
//				//flush();	// ob_contentが送られてしまうためコメント
//			}
//		}

		// タグ変換
		//$this->assignTagReplace($body, $subject);
		$this->assignTagReplace();

		// 改行対応
		if (parent::emailFormat() == 'text') {
			// text形式は配列にすると改行される
			$this->body = explode('\n', $this->body);
		} else {
			$this->body = str_replace('\n', '<br />', $this->body);
		}

		parent::subject($this->subject);
		$messages = parent::send($this->body);
//		var_dump($messages);
		return $messages;
	}

/**
 * 変換タグの追加
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
		if (substr($tag, 0, 2) == "X-") {
			$this->assignTags[$tag] = $value;
		}
	}

	///**
	// * 変換タグを配列で追加
	// *
	// * @param array $tags タグ配列
	// * @return void
	// */
	//	public function assignTags($tags) {
	//		foreach ($tags as $key => $value) {
	//			$this->assignTag($key, $value);
	//		}
	//	}

/**
 * タグ変換
 * メール定型文をタグ変換して、生文に変換する
 *
 * @return array タグ
 */
	public function assignTagReplace() {
		//public function assignTagReplace($body, $subject) {
		$convertHtml = new ConvertHtml();

		foreach ($this->assignTags as $key => $value) {
			if (substr($value, 0, 4) == "X-TO" || $key == "X-URL") {
				continue;
			}
			$this->body = str_replace("{".$key."}", $value, $this->body);
			$this->subject = str_replace("{".$key."}", $convertHtml->convertHtmlToText($value), $this->subject);
		}

		$this->body = str_replace("\r\n", "\n", $this->body);
		$this->body = str_replace("\r", "\n", $this->body);
		//$this->body = str_replace("\n", $this->_LE, $this->body);
		$this->body = $this->insertNewLine($this->body);

		//		if(isset($this->assignTags["X-URL"])) {
		//			$this->body = str_replace("{X-URL}", "<a href=\"". $this->assignTags["X-URL"]. "\">". $this->assignTags["X-URL"]. "</a>", $this->body);
		//			$mobile_body = str_replace("{X-URL}", $this->assignTags["X-URL"], $this->body);
		//			unset($this->assignTags["X-URL"]);
		//		} else {
		//			$mobile_body = $this->body;
		//		}
		//		$mobile_body = $convertHtml->convertHtmlToText($mobile_body);
		//		$mobile_body = $this->insertNewLine($mobile_body);

		if (parent::emailFormat() == 'text') {
			$this->body = str_replace("{X-URL}", $this->assignTags["X-URL"], $this->body);
		} else {
			$this->body = str_replace("{X-URL}", "<a href=\"". $this->assignTags["X-URL"]. "\">". $this->assignTags["X-URL"]. "</a>", $this->body);
		}

		// URLの置換は一度きり
		//unset($this->assignTags["X-URL"]);
	}

	//	/**
	//	 * 送信先ユーザの設定
	//	 *
	//	 * @param	array	$users	ユーザ情報配列
	//	 *
	//	 * @access	public
	//	 */
	//	function setToUsers(&$users)
	//	{
	//		$this->toUsers = $users;
	//	}

	//	/**
	//	 * 送信先ユーザの追加
	//	 *
	//	 * @param	array	$user	ユーザ情報配列
	//	 *
	//	 * @access	public
	//	 */
	//	function addToUser(&$user)
	//	{
	//		$this->toUsers[] = $user;
	//	}

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
		$lines_out = array();

		while(list(,$line) = @each($lines)) {
			// 1行が300文字以下になったら抜ける
			while(mb_strlen($line) > $this::MAX_LINE_LENGTH) {
				// 1行300文字で改行。なので配列にセット。
				// 1行300文字まで取得、< があるか
				$pos = strrpos(mb_substr($line, 0, $this::MAX_LINE_LENGTH), '<');
				// 1行300文字の中に '<' ありなら、途中で改行
				if ($pos > 0) {
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					$lines_out[] = mb_substr($line, 0, $this::MAX_LINE_LENGTH);
					$line = mb_substr($line,  $this::MAX_LINE_LENGTH);
				}
			}
			$lines_out[] = $line;
		}
		//return implode($this->_LE, $lines_out);
		return implode('\n', $lines_out);
	}

/**
 * メールを送信する2 debug用
 */
	public function send2() {
		// 仮
		//		$this->_mailer->to('mutaguchi@opensource-workshop.jp');			// 送信先
		//		$this->_mailer->subject('メールタイトル');						// メールタイトル
		//
		//		$this->_mailer->send('メール本文');								// メール送信
		parent::to('mutaguchi@opensource-workshop.jp');			// 送信先
		parent::subject('メールタイトル');						// メールタイトル

		parent::send('メール本文');								// メール送信
	}

/**
 * メールを送信する3 debug用
 */
	public function send3($blockKey, $typeKey = 'contents') {
		$this->setSendMailSetting($blockKey);

		// 通知しない
		if (! $this->isMailSend) {
			return;
		}

		//		$config = $this->config();
		//$config['from'] = array('mutaguchi@opensource-workshop.jp' => 'NetCommons管理者');
		//		$fromEmail = key($config['from']);
		//		$fromName = current($config['from']);
		//var_dump($config, $fromEmail, $fromName);

		//		$this->assignTag("X-FROM_EMAIL", $fromEmail);
		//		$this->assignTag("X-FROM_NAME", htmlspecialchars($fromName));
		//		$this->assignTag("X-SITE_NAME", htmlspecialchars('サイト名称')); //仮
		//		$this->assignTag("X-SITE_URL", Router::fullbaseUrl());

		//		if (!isset($this->_assignedTags['X-ROOM'])) {
		//			$request =& $container->getComponent("Request");
		//			$pageView =& $container->getComponent("pagesView");
		//			$roomId = $request->getParameter("room_id");
		//			$pages = $pageView->getPageById($roomId);
		//
		//			$this->assign("X-ROOM", htmlspecialchars($pages["page_name"]));
		//		}

		if ($this->assignTag("X-USER") == null) {
			$this->assignTag("X-USER", htmlspecialchars(AuthComponent::user('handlename')));
		}

		$this->assignTag("X-PLUGIN_NAME", '動画');
		$this->assignTag("X-ROOM", 'グループルーム');
		$this->assignTag("X-BLOCK_NAME", '運動会');
		$this->assignTag("X-SUBJECT", 'タイトル');
		$this->assignTag("X-TO_DATE", '2099/01/01');
		$this->assignTag("X-BODY", '本文１\n本文２\n本文３');
		$this->assignTag("X-APPROVAL_COMMENT", '承認コメント１\n承認コメント２\n承認コメント３');
		$this->assignTag("X-URL", 'http://localhost');


		// タグ変換
		$this->assignTagReplace();

		//		$this->to('mutaguchi@opensource-workshop.jp');			// 送信先
		//		$this->subject('メールタイトル');						// メールタイトル
		//		$this->send('メール本文');								// メール送信
		parent::to('mutaguchi@opensource-workshop.jp');			// 送信先(仮)
		parent::subject($this->subject);						// メールタイトル

		$messages = parent::send($this->body);
		if (self::IS_DEBUG) {
			var_dump($this->subject, $messages);
		}
	}
}
