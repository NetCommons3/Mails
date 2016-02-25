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

/**
 * NetCommonsメール Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 */
class NetCommonsMail extends CakeEmail {

/**
 * 変換タグ
 *
 * @var array
 */
	public $assignTags = array();

/**
 * Constructor
 *
 * @param array|string $config Array of configs, or string to load configs from email.php
 * @see CakeEmail::__construct()
 */
	public function __construct($config = null) {
		parent::__construct($config);

		if ($config != null) {
			$this->init();
		}
	}

/**
 * 初期設定
 *
 * @return void
 * @see CakeEmail::$charset
 */
	public function init() {
		// 仮対応
		// ここでDBから取得したSMTP設定をセットする
		$SiteSetting = ClassRegistry::init('SiteManager.SiteSetting', true);

		/** @see SiteSetting::getSiteSettingForEdit() */
		$siteSettingData = $SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array(
				'Mail.from',
				'Mail.from_name',
				'Mail.messageType',
				'Mail.transport',
				'Mail.smtp.host',
				'Mail.smtp.port',
				'Mail.smtp.user',
				'Mail.smtp.pass',
			)
		));

		//		$siteSettingData = $SiteSetting->find('all', array(
		//			'recursive' => -1,
		//			'conditions' => array(
		//				'SiteSetting.key' => array(
		//					'Mail.from',
		//					'Mail.from_name',
		//					'Mail.messageType',
		//					'Mail.transport',
		//					'Mail.smtp.host',
		//					'Mail.smtp.port',
		//					'Mail.smtp.user',
		//					'Mail.smtp.pass',
		//				)
		//			)
		//		));
		//CakeLog::debug(print_r($siteSettingData, true));

		$config = array();

		//$config['from'] = array('mutaguchi@opensource-workshop.jp' => 'NetCommons管理者');
		$from = Hash::get($siteSettingData['Mail.from'], '0.value');
		$fromName = Hash::get($siteSettingData['Mail.from_name'], '2.value');	//仮：日本語
		$config['from'] = array($from => $fromName);

		//		$config['host'] = '____.sakura.ne.jp';		// 初期ドメイン
		//		$config['port'] = 587;
		//		$config['username'] = 'username@____.sakura.ne.jp';
		//		$config['password'] = 'secret';
		//		$config['transport'] = 'Smtp';
		$transport = Hash::get($siteSettingData['Mail.transport'], '0.value');

		// SMTP, SMTPAuth
		if ($transport == $SiteSetting::MAIL_TRANSPORT_SMTP) {
			$smtpHost = Hash::get($siteSettingData['Mail.smtp.host'], '0.value');
			$smtpPort = Hash::get($siteSettingData['Mail.smtp.port'], '0.value');
			$smtpUser = Hash::get($siteSettingData['Mail.smtp.user'], '0.value');
			$smtpPass = Hash::get($siteSettingData['Mail.smtp.pass'], '0.value');

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
		} elseif ($transport == $SiteSetting::MAIL_TRANSPORT_PHPMAIL) {
			$config['transport'] = 'Mail';
		}

		//CakeLog::debug(print_r($config, true));
		$this->config($config);

		// html or text
		$messageType = Hash::get($siteSettingData['Mail.messageType'], '0.value');
		$this->emailFormat($messageType);

		//		$this->fromEmail = "";
		//		$this->fromName = "";
		//		$this->priority = "";
		//		$this->toUsers = array();
		//		$this->headers = array();
		//		$this->subject = "";
		//		$this->body = "";
		//		$this->_log =& LogFactory::getLog();
		//		$this->_assignedTags = array();
		//		$this->_LE = "\n";
		//		$this->charSet = _CHARSET;
		//		$this->encoding = "8bit";
		//		$this->isHTML = true;

		//		$container =& DIContainerFactory::getContainer();
		//		$configView =& $container->getComponent("configView");
		//		$mailConfigs = $configView->getConfigByCatid(_SYS_CONF_MODID, _MAIL_CONF_CATID);


		//		$this->setFromEmail($mailConfigs["from"]["conf_value"]);
		//		$this->setFromName($mailConfigs["fromname"]["conf_value"]);

		//		$this->_mailer->Host = $mailConfigs["smtphost"]["conf_value"];
		//		$this->setting_config = (($mailConfigs["mailmethod"]["conf_value"] == "smtpauth" || $mailConfigs["mailmethod"]["conf_value"] == "smtp") && $this->_mailer->Host == "") ? false : true;

		//		if ($mailConfigs["mailmethod"]["conf_value"] == "smtpauth") {
		//		    $this->_mailer->Mailer = "smtp";
		//			$this->_mailer->SMTPAuth = TRUE;
		//			$this->_mailer->Username = $mailConfigs["smtpuser"]["conf_value"];
		//			$this->_mailer->Password = $mailConfigs["smtppass"]["conf_value"];
		//		} else {
		//			$this->_mailer->Mailer = $mailConfigs["mailmethod"]["conf_value"];
		//			$this->_mailer->SMTPAuth = FALSE;
		//			$this->_mailer->Sendmail = $mailConfigs["sendmailpath"]["conf_value"];
		//		}
		//		if($mailConfigs["mailmethod"]["conf_value"] == "sendmail") {
		//			$this->setting_config = ($this->_mailer->Sendmail == "") ? false : true;
		//		}


		//		if (isset($mailConfigs["htmlmail"]) && $mailConfigs["htmlmail"]["conf_value"] == _OFF) {
		//			// htmlメールかいなか
		//			$this->isHTML = false;
		//		}
	}

/**
 * メール送信する定型文をセットする
 *
 * @return void
 */
	public function setSendMailSetting($blockKey, $typeKey = 'contents') {
	//public function setSendMailSetting($blockKey = null, $pluginKey = null, $typeKey = 'contents') {
		// $pluginKey = $Model->plugin();
		// 'plugin_key' => Inflector::underscore($model->plugin),
		//$pluginKey = Inflector::underscore($model->plugin);

		// 定型文を取得する
//		if (isset($blockKey)) {
			// 通常のプラグインはこちら
			$mailSetting = $this->getMailSettingPlugin($blockKey, $typeKey);
//		} else {
//			// システム管理系はこちら
//			$mailSetting = $this->getMailSettingSystem($pluginKey, $typeKey);
//		}

		// メール通知フラグをセットする
		//$this->setIsMailSend($mailSetting['mail_setting']['is_mail_send']);
		$isMailSend = Hash::get($mailSetting, 'mail_setting.is_mail_send');

		// 通知する場合
		//if ($this->getIsMailSend()) {
		if ($isMailSend) {
			// ブロックキー、プラグインキーをセットする
//			$this->setMailSettingBlockKey($mailSetting['mail_setting']['block_key']);
//			$this->setMailSettingPlaginKey($mailSetting['mail_setting']['plugin_key']);

			// 定型文をセットする
			$this->setMailSubject($mailSetting['mail_setting']['mail_fixed_phrase_subject']);
			$this->setMailBody($mailSetting['mail_setting']['mail_fixed_phrase_body']);

			// 返信先アドレスをセットする
//			$this->setMailReplayTo($mailSetting['mail_setting']['replay_to']);
			$this->replyTo($mailSetting['mail_setting']['replay_to']);
		}
	}

/**
 * プラグインの定型文を取得する
 *
 * @return array メール設定データ配列
 */
	public function getMailSettingPlugin($blockKey, $typeKey = 'contents') {
		// $blockKey, $typeKeyで、mail_settings を SELECT する
		$MailSetting = ClassRegistry::init('Mails.MailSetting', true);
		$mailSettingData = $MailSetting->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'block_key' => $blockKey,
				'type_key' => $typeKey,
			)
		));
		return $mailSettingData;
	}

	//	/**
	//	 * Fromアドレスをセットする
	//	 *
	//	 * @param	string	$value	Fromアドレス
	//	 *
	//	 * @access	public
	//	 */
	//	function setFromEmail($value)
	//	{
	//		$this->fromEmail = trim($value);
	//	}

	//	/**
	//	 * From名称をセットする
	//	 *
	//	 * @param	string	$value	From名称
	//	 *
	//	 * @access	public
	//	 */
	//	function setFromName($value)
	//	{
	//		$this->fromName = trim($value);
	//	}

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

	//	/**
	//	 * 件名をセットする
	//	 *
	//	 * @param	string	$value	件名
	//	 *
	//	 * @access	public
	//	 */
	//	function setSubject($value)
	//	{
	//		$this->subject = trim($value);
	//	}

	//	/**
	//	 * 本文をセットする
	//	 *
	//	 * @param	string	$value	本文
	//	 *
	//	 * @access	public
	//	 */
	//	function setBody($value)
	//	{
	//		$this->body = trim($value);
	//		$this->body = str_replace("\n", "<br />", $this->body). "<br />";
	//
	//		$container =& DIContainerFactory::getContainer();
	//		$commonMain =& $container->getComponent('commonMain');
	//		$escapeText =& $commonMain->registerClass(WEBAPP_DIR . '/components/escape/Text.class.php', 'Escape_Text', 'escapeText');
	//
	//		$this->body = $escapeText->escapeWysiwyg($this->body);
	//	}

/**
 * メールを送信する2 debug用
 */
	public function send2() {
		// 仮
		//		$this->_mailer->to('mutaguchi@opensource-workshop.jp');			// 送信先
		//		$this->_mailer->subject('メールタイトル');						// メールタイトル
		//
		//		$this->_mailer->send('メール本文');								// メール送信
		$this->to('mutaguchi@opensource-workshop.jp');			// 送信先
		$this->subject('メールタイトル');						// メールタイトル

		$this->send('メール本文');								// メール送信
	}

/**
 * メールを送信する3 debug用
 */
	public function send3($blockKey, $typeKey = 'contents') {
		// 仮
		//public function setSendMailSetting($blockKey = null, $pluginKey = null, $typeKey = 'contents') {

		// 通常のプラグインはこちら
		$mailSetting = $this->getMailSettingPlugin($blockKey, $typeKey);

		// メール通知フラグ
		$isMailSend = Hash::get($mailSetting, 'mail_setting.is_mail_send');
		// 通知する場合
		//if ($this->getIsMailSend()) {
		if (! $isMailSend) {
			return;
		}
//		// ブロックキー、プラグインキーをセットする
//		$this->setMailSettingBlockKey($mailSetting['mail_setting']['block_key']);
//		$this->setMailSettingPlaginKey($mailSetting['mail_setting']['plugin_key']);
//
//		// 定型文をセットする
//		$this->setMailSubject($mailSetting['mail_setting']['mail_fixed_phrase_subject']);
//		$this->setMailBody($mailSetting['mail_setting']['mail_fixed_phrase_body']);
//
//		// 返信先アドレスをセットする
//		$this->setMailReplayTo($mailSetting['mail_setting']['replay_to']);

		$subject = $mailSetting['mail_setting']['mail_fixed_phrase_subject'];
		$body = $mailSetting['mail_setting']['mail_fixed_phrase_body'];

		// 返信先アドレスをセットする
		$this->replyTo($mailSetting['mail_setting']['replay_to']);

		$config = $this->config();
		//$config['from'] = array('mutaguchi@opensource-workshop.jp' => 'NetCommons管理者');
		$fromEmail = key($config['from']);
		$fromName = current($config['from']);

		$this->assignTag("X-FROM_EMAIL", $fromEmail);
		$this->assignTag("X-FROM_NAME", htmlspecialchars($fromName));
		//$this->assignTag("X-SITE_NAME", htmlspecialchars($confs["conf_value"]));
		//$this->assign("X-SITE_URL", BASE_URL.INDEX_FILE_NAME);
		$this->assignTag("X-SITE_NAME", htmlspecialchars('サイト名称')); //仮
		$this->assignTag("X-SITE_URL", Router::fullbaseUrl());

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

//		$this->to('mutaguchi@opensource-workshop.jp');			// 送信先
//		$this->subject('メールタイトル');						// メールタイトル
//		$this->send('メール本文');								// メール送信
		$this->to('mutaguchi@opensource-workshop.jp');			// 送信先(仮)
		$this->subject($subject);						// メールタイトル
		$this->send($body);								// メール送信
	}

	//	/**
	//	 * メールを送信する
	//	 *
	//	 * @access	public
	//	 */
	//	function send()
	//	{
	//		if($this->setting_config == false) {
	//			$this->_log->error("システム管理の設定が正しくありません", "Mailer#send");
	//			return false;
	//		}
	//		if ( $this->body == "") {
	//			$this->_log->error("メッセージ本文がありません", "Mailer#send");
	//			return false;
	//		}
	//
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
	//		return true;
	//	}

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
		if ($value === null) {
			return Hash::get($this->assignTag, $tag);
		}
		// タグの両端空白なくして、大文字に変換。おおよそいらない処理
		//$tag = strtoupper(trim($tag));

		if (substr($tag, 0, 2) == "X-") {
			$this->assignTags[$tag] = $value;
		}
	}

/**
 * 変換タグを配列で追加
 *
 * @param array $tags タグ配列
 * @return void
 */
	public function assignTags($tags) {
		foreach ($tags as $key => $value) {
			$this->assignTag($key, $value);
		}
	}

	//	/**
	//	 * ヘッダの追加
	//	 *
	//	 * @param	string	$value	ヘッダの値
	//	 *
	//	 * @access	public
	//	 */
	//	function addHeaders($value)
	//	{
	//		$this->headers[] = trim($value). $this->_LE;
	//	}

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

	//	/**
	//	 * 改行コード挿入
	//	 */
	//	function _insertNewLine($body)
	//	{
	//		$lines = explode($this->_LE, $body);
	//		$pos = 0;
	//		$max_line_length = 300;
	//
	//		while(list(,$line) = @each($lines)) {
	//			while(mb_strlen($line) > $max_line_length) {
	//				$pos = strrpos(mb_substr($line, 0, $max_line_length), '<');
	//				if ($pos > 0) {
	//					$lines_out[] = substr($line, 0, $pos);
	//					$line = substr($line, $pos);
	//				} else {
	//					$lines_out[] = mb_substr($line, 0, $max_line_length);
	//					$line = mb_substr($line,  $max_line_length);
	//				}
	//			}
	//			$lines_out[] = $line;
	//		}
	//		return implode($this->_LE, $lines_out);
	//	}
}
