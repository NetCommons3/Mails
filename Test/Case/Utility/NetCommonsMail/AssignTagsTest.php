<?php
/**
 * NetCommonsMail::assignTags()のテスト
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
 * NetCommonsMail::assignTags()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailAssignTagsTest extends NetCommonsCakeTestCase {

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
 * assignTags()のテスト
 *
 * @return void
 */
	public function testAssignTags() {
		//データ生成
		$tags = array('X-HOGE' => 'hogehoge');

		//テスト実施
		$this->mail->assignTags($tags);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('X-HOGE', $this->mail->mailAssignTag->assignTags);
	}

}
