<?php
/**
 * MailQueueDeleteBehavior::delete()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('TestMailQueueDeleteBehaviorDeleteModelFixture', 'Mails.Test/Fixture');

/**
 * MailQueueDeleteBehavior::delete()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueDeleteBehavior
 */
class MailQueueDeleteBehaviorDeleteTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.test_mail_queue_delete_behavior_delete_model',
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
		$this->TestModel = ClassRegistry::init('TestMails.TestMailQueueDeleteBehaviorDeleteModel');
	}

/**
 * delete()のテスト
 *
 * @return void
 */
	public function testDelete() {
		//テスト実施
		$result = $this->TestModel->delete(1);
		$this->assertTrue($result);

		//テスト実施
		$result = $this->TestModel->delete(2);
		$this->assertTrue($result);
	}

}
