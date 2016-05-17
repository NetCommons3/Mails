<?php
/**
 * NetCommonsMail::assignTag()のテスト
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
 * NetCommonsMail::assignTag()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailAssignTagTest extends NetCommonsCakeTestCase {

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
 * assignTag()のテスト
 *
 * @return void
 */
	public function testAssignTag() {
		//データ生成
		$tag = 'X-HOGE';
		$value = 'hoge';

		//テスト実施
		$this->mail->assignTag($tag, $value);

		//チェック
		//debug($this->mail->mailAssignTag->assignTags);
		$this->assertArrayHasKey('X-HOGE', $this->mail->mailAssignTag->assignTags);
	}

/**
 * assignTag()のテスト - nullでもセットできる
 *
 * @return void
 */
	public function testAssignTagNull() {
		//データ生成
		$tag = 'X-HOGE';
		$value = null;

		//テスト実施
		$this->mail->assignTag($tag, $value);

		//チェック
		//debug($this->mail->mailAssignTag->assignTags);
		$this->assertArrayHasKey('X-HOGE', $this->mail->mailAssignTag->assignTags);
	}

}
