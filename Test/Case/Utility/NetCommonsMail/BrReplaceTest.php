<?php
/**
 * NetCommonsMail::brReplace()のテスト
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
 * NetCommonsMail::brReplace()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailBrReplaceTest extends NetCommonsCakeTestCase {

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
 * brReplace()のテスト - textモード
 *
 * @return void
 */
	public function testBrReplaceText() {
		//データ生成
		$str = "11111\n22222\n3333\n";

		$this->mail->setBody($str);
		$this->mail->emailFormat('text');

		//テスト実施
		$this->mail->brReplace();

		//チェック
		$this->assertTrue(is_array($this->mail->body));
	}

/**
 * brReplace()のテスト - htmlモードで改行が<br>に置換されている
 *
 * @return void
 */
	public function testBrReplaceHtml() {
		//データ生成
		$str = "11111\n22222\n3333\n";

		$this->mail->setBody($str);
		$this->mail->emailFormat('html');

		//テスト実施
		$this->mail->brReplace();

		//チェック
		$this->assertTextContains('<br />', $this->mail->body);
	}

}
