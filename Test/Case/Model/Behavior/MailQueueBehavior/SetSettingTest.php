<?php
/**
 * MailQueueBehavior::setSetting()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');

/**
 * MailQueueBehavior::setSetting()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueBehavior
 */
class MailQueueBehaviorSetSettingTest extends NetCommonsModelTestCase {

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
 * setSetting()テストのDataProvider
 *
 * ### 戻り値
 *  - settingKey セッティングのキー
 *  - settingValue セッティングの値
 *
 * @return array データ
 */
	public function dataProvider() {
		$result[0] = array();
		$result[0]['settingKey'] = MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS;
		$result[0]['settingValue'] = array(
			'test1@example.com',
			'test2@example.com',
		);

		return $result;
	}

/**
 * setSetting()のテスト
 *
 * @param string $settingKey セッティングのキー
 * @param string|array $settingValue セッティングの値
 * @dataProvider dataProvider
 * @return void
 */
	public function testSetSetting($settingKey, $settingValue) {
		//テスト実施
		/** @see MailQueueBehavior::setSetting() */
		$result = $this->TestModel->setSetting($settingKey, $settingValue);

		//チェック
		//debug($result);
		$this->assertEquals($settingValue, $result[$this->TestModel->alias][$settingKey]);
	}

}
