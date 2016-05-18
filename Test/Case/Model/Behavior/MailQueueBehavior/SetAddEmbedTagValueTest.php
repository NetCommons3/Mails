<?php
/**
 * MailQueueBehavior::setAddEmbedTagValue()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * MailQueueBehavior::setAddEmbedTagValue()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueBehavior
 */
class MailQueueBehaviorSetAddEmbedTagValueTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Mails', 'TestMails');
		$this->TestModel = ClassRegistry::init('TestMails.TestMailQueueBehaviorModel');
	}

/**
 * setAddEmbedTagValue()テストのDataProvider
 *
 * ### 戻り値
 *  - embedTag 埋め込みタグ
 *  - value タグから置き換わる値
 *
 * @return array データ
 */
	public function dataProvider() {
		$result[0] = array();
		$result[0]['embedTag'] = 'X-URL';
		$result[0]['value'] = 'http://localhost/';

		return $result;
	}

/**
 * setAddEmbedTagValue()のテスト
 *
 * @param string $embedTag 埋め込みタグ
 * @param string $value タグから置き換わる値
 * @dataProvider dataProvider
 * @return void
 */
	public function testSetAddEmbedTagValue($embedTag, $value) {
		//テスト実施
		/** @see MailQueueBehavior::setAddEmbedTagValue() */
		$result = $this->TestModel->setAddEmbedTagValue($embedTag, $value);

		//チェック
		//debug($result);
		$this->assertEquals($value, $result[$this->TestModel->alias]['addEmbedTagsValues'][$embedTag]);
	}

}
