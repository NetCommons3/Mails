<?php
/**
 * NetCommonsMail::sendQueueMail()のテスト
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
 * NetCommonsMail::sendQueueMail()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailSendQueueMailTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.site_setting_for_mail',
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.rooms.room_role_permission4test',
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

		//送信しない（デバッグ用）
		$config = $this->mail->config();
		$config['transport'] = 'Debug';
		$this->mail->config($config);
	}

/**
 * sendQueueMail()のテスト
 *
 * @param int $index MailQueueUserFixtureのインデックス
 * @param string $assert チェック
 * @dataProvider dataProviderSendQueueMail
 * @return void
 */
	public function testSendQueueMail($index, $assert = 'assertNotEmpty') {
		//データ生成
		$MailQueueFixture = new MailQueueFixture();
		$mailQueue['MailQueue'] = $MailQueueFixture->records[0];
		$MailQueueUserFixture = new MailQueueUserFixture();
		$mailQueueUser = $MailQueueUserFixture->records[$index];
		$mailQueueLanguageId = 2;

		$this->mail->initShell($mailQueue);

		//テスト実施
		$result = $this->mail->sendQueueMail($mailQueueUser, $mailQueueLanguageId);

		//チェック
		//debug($result);
		$this->$assert($result);
	}

/**
 * SendQueueMailテスト用DataProvider
 *
 * #### 戻り値
 *  - mailQueue: リクエストのmethod(post put delete)
 *  - mailQueueUser: 登録データ
 *
 * @return array
 */
	public function dataProviderSendQueueMail() {
		return array(
			'user_id送信パターン' => array(
				'index' => 0,
			),
			'メールアドレス送信パターン' => array(
				'index' => 1,
			),
			'room_id送信パターン - mail_content_receivable' => array(
				'index' => 2,
				'assert' => 'assertEmpty',
			),
			'room_id送信パターン - mail_answer_receivable' => array(
				'index' => 3,
				'assert' => 'assertEmpty',
			),
			'送信パターン該当なし' => array(
				'index' => 4,
				'assert' => 'assertEmpty',
			),
		);
	}

/**
 * sendQueueMail()の本文空テスト
 *
 * @return void
 */
	public function testSendQueueMailBodyEmpty() {
		//データ生成
		$mailQueueUser = null;
		$mailQueueLanguageId = 2;

		//テスト実施
		$result = $this->mail->sendQueueMail($mailQueueUser, $mailQueueLanguageId);

		//チェック
		//debug($result);
		$this->assertFalse($result);
	}

/**
 * initShell()のテスト - phpmail
 *
 * @return void
 */
	public function testInitShell() {
		//データ生成
		$mailQueue = null;
		SiteSettingUtil::write('Mail.transport', SiteSetting::MAIL_TRANSPORT_PHPMAIL, 0);

		//テスト実施
		$this->mail->initShell($mailQueue);

		//チェック
		$config = $this->mail->config();
		// メール設定チェック
		$this->assertEquals('Mail', $config['transport']);

		// $mailQueueが空なら、件名、本文、replyToは空
		$this->assertEmpty($this->mail->subject);
		$this->assertEmpty($this->mail->body);
		$this->assertEmpty($this->mail->replyTo());
	}

/**
 * initShell()のテスト - smtp
 *
 * @return void
 */
	public function testInitShellSmtp() {
		//データ生成
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
	}

/**
 * initShell()のテスト - smtpAuth
 *
 * @return void
 */
	public function testInitShellSmtpAuth() {
		//データ生成
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
	}


}
