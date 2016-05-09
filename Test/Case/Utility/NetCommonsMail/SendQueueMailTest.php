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

}
