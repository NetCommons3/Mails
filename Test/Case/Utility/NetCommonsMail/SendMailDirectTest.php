<?php
/**
 * NetCommonsMail::sendMailDirect()のテスト
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
 * NetCommonsMail::sendMailDirect()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailSendMailDirectTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.site_manager.site_setting',
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

		$this->mail->mailAssignTag->assignTag('X-BODY_HEADER', '本文ヘッダー文');
		$this->mail->mailAssignTag->assignTag('X-SIGNATURE', '署名');
	}

/**
 * sendMailDirect()のテスト
 *
 * @return void
 */
	public function testSendMailDirect() {
		//データ生成
		$this->mail->from('from@dummpy.com');
		$this->mail->to('to@dummpy.com');

		$this->mail->mailAssignTag->setFixedPhraseBody("本文１\r\n本文２\r\n本文３\r\n");

		//テスト実施
		$result = $this->mail->sendMailDirect();

		//チェック
		//debug($result);
		$this->assertNotEmpty($result);
	}

/**
 * sendMailDirect()の本文空テスト
 *
 * @return void
 */
	public function testSendMailDirectBodyEmpty() {
		//データ生成
		$this->mail->from('from@dummpy.com');
		$this->mail->to('to@dummpy.com');

		//$mail->mailAssignTag->setFixedPhraseBody("本文１\r\n本文２\r\n本文３\r\n");

		//テスト実施
		$result = $this->mail->sendMailDirect();

		//チェック
		//debug($result);
		$this->assertFalse($result);
	}

	///**
	// * cakeMail 実際にメール送るテスト
	// *
	// * @return void
	// */
	//	public function testCakeMail() {
	//		$email = new CakeEmail();
	//		//debug($email->config());
	//		// sakuraインターネット例
	//		$sakura = array(
	//			'transport' => 'Smtp',
	//			'from' => array('site@localhost' => 'NetCommons管理者'),
	//			'host' => '______.sakura.ne.jp',  // 初期ドメイン
	//			'port' => 587,
	//			'username' => 'username@______.sakura.ne.jp', // ユーザ名：
	//			// 初期ドメインのメールアドレスもしくは、独自ドメインのメールアドレス
	//			'password' => 'password',           // メールパスワード
	//			'tls' => true,	// STARTTLS
	//		);
	//
	//		$email->config($sakura);
	//		$email->to('reciever@domain.com');	// 送信先
	//		$email->subject('メールタイトル');
	//
	//		$email->send('メール本文');
	//	}
}
