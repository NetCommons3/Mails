<?php
/**
 * NetCommonsMail::initShell()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsMail', 'Mails.Utility');
App::uses('SiteSetting', 'SiteManager.Model');

/**
 * NetCommonsMail::initShell()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailInitShellTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.site_setting_for_mail',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * メール
 *
 * @var object
 */
	public $mail = null;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->mail = new NetCommonsMail();
	}

/**
 * initShell()のテスト - phpmail
 *
 * @return void
 */
	public function testInitShell() {
		//データ生成
		//MailQueueデータあり
		$MailQueueFixture = new MailQueueFixture();
		$mailQueue['MailQueue'] = $MailQueueFixture->records[0];
		SiteSettingUtil::write('Mail.transport', SiteSetting::MAIL_TRANSPORT_PHPMAIL, 0);

		//テスト実施
		$this->mail->initShell($mailQueue);

		//チェック
		$config = $this->mail->config();
		// メール設定チェック
		$this->assertEquals('Mail', $config['transport']);

		// $mailQueueがデータありなら、件名、本文、replyToは値あり
		$this->assertNotEmpty($this->mail->subject);
		$this->assertNotEmpty($this->mail->body);
		$this->assertNotEmpty($this->mail->replyTo());
	}

/**
 * initShell()のテスト - smtp
 *
 * @return void
 */
	public function testInitShellSmtp() {
		//データ生成
		//MailQueueデータなし
		$mailQueue = null;
		SiteSettingUtil::write('Mail.transport', SiteSetting::MAIL_TRANSPORT_SMTP, 0);
		SiteSettingUtil::write('Mail.smtp.host', 'localhost', 0);
		SiteSettingUtil::write('Mail.smtp.port', 25, 0);

		//テスト実施
		$this->mail->initShell($mailQueue);

		//チェック
		$config = $this->mail->config();
		// メール設定チェック
		$this->assertEquals('Smtp', $config['transport']);
		$this->assertEquals('localhost', $config['host']);
		$this->assertEquals(25, $config['port']);

		// smtpのユーザ名、パスワードは設定されてない
		$this->assertArrayNotHasKey('username', $config);
		$this->assertArrayNotHasKey('password', $config);

		// $mailQueueが空なら、件名、本文、replyToは空
		$this->assertEmpty($this->mail->subject);
		$this->assertEmpty($this->mail->body);
		$this->assertEmpty($this->mail->replyTo());
	}

/**
 * initShell()のテスト - smtpAuth
 *
 * @return void
 */
	public function testInitShellSmtpAuth() {
		//データ生成
		//MailQueueデータなし
		$mailQueue = null;
		SiteSettingUtil::write('Mail.transport', SiteSetting::MAIL_TRANSPORT_SMTP, 0);
		SiteSettingUtil::write('Mail.smtp.host', 'localhost', 0);
		SiteSettingUtil::write('Mail.smtp.port', 25, 0);
		SiteSettingUtil::write('Mail.smtp.user', 'dummy', 0);
		SiteSettingUtil::write('Mail.smtp.pass', 'password', 0);

		//テスト実施
		$this->mail->initShell($mailQueue);

		//チェック
		$config = $this->mail->config();
		// メール設定チェック
		$this->assertEquals('Smtp', $config['transport']);
		$this->assertEquals('localhost', $config['host']);
		$this->assertEquals(25, $config['port']);

		// smtpのユーザ名、パスワードは設定されてる
		$this->assertEquals('dummy', $config['username']);
		$this->assertEquals('password', $config['password']);

		// $mailQueueが空なら、件名、本文、replyToは空
		$this->assertEmpty($this->mail->subject);
		$this->assertEmpty($this->mail->body);
		$this->assertEmpty($this->mail->replyTo());
	}
}
