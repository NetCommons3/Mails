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
 * sendMailDirect()のテスト
 *
 * @return void
 */
	public function testSendMailDirect() {
		//データ生成
		$mail = new NetCommonsMail();
		$mail->from('from@dummpy.com');
		$mail->to('to@dummpy.com');

		//送信しない（デバッグ用）
		$config = $mail->config();
		$config['transport'] = 'Debug';
		$mail->config($config);

		$mail->mailAssignTag->setFixedPhraseBody("本文１\r\n本文２\r\n本文３\r\n");
		$mail->mailAssignTag->assignTag('X-BODY_HEADER', '本文ヘッダー文');
		$mail->mailAssignTag->assignTag('X-SIGNATURE', '署名');

		//テスト実施
		$result = $mail->sendMailDirect();

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
		$mail = new NetCommonsMail();
		$mail->from('from@dummpy.com');
		$mail->to('to@dummpy.com');

		//送信しない（デバッグ用）
		$config = $mail->config();
		$config['transport'] = 'Debug';
		$mail->config($config);

		//$mail->mailAssignTag->setFixedPhraseBody("本文１\r\n本文２\r\n本文３\r\n");
		$mail->mailAssignTag->assignTag('X-BODY_HEADER', '本文ヘッダー文');
		$mail->mailAssignTag->assignTag('X-SIGNATURE', '署名');

		//テスト実施
		$result = $mail->sendMailDirect();

		//チェック
		//debug($result);
		$this->assertEmpty($result);
	}

}
