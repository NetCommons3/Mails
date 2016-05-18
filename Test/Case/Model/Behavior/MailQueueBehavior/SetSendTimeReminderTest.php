<?php
/**
 * MailQueueBehavior::setSendTimeReminder()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsTime', 'NetCommons.Utility');

/**
 * MailQueueBehavior::setSendTimeReminder()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueBehavior
 */
class MailQueueBehaviorSetSendTimeReminderTest extends NetCommonsModelTestCase {

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
 * setSendTimeReminder()のテスト - 未来日
 *
 * @return void
 */
	public function testSetSendTimeReminderFuture() {
		$netCommonsTime = new NetCommonsTime();

		$sendTimeReminders = array(
			$netCommonsTime->toServerDatetime('2027-03-31 14:30:00'),
			$netCommonsTime->toServerDatetime('2027-04-20 13:30:00'),
		);

		//テスト実施
		/** @see MailQueueBehavior::setSendTimeReminder() */
		$result = $this->TestModel->setSendTimeReminder($sendTimeReminders);

		//チェック
		//debug($result);
		$this->assertCount(2, $result[$this->TestModel->alias]['reminder']['sendTimes']);
		$this->assertEquals(1, $result[$this->TestModel->alias]['reminder']['useReminder']);
	}

/**
 * setSendTimeReminder()のテスト - 過去日
 *
 * @return void
 */
	public function testSetSendTimeReminderPast() {
		$netCommonsTime = new NetCommonsTime();

		$sendTimeReminders = array(
			$netCommonsTime->toServerDatetime('2016-03-31 14:30:00'),
			$netCommonsTime->toServerDatetime('2016-04-20 13:30:00'),
		);

		//テスト実施
		/** @see MailQueueBehavior::setSendTimeReminder() */
		$result = $this->TestModel->setSendTimeReminder($sendTimeReminders);

		//チェック
		//debug($result);
		$this->assertEmpty($result);
	}

}
