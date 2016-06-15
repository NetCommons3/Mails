<?php
/**
 * IsMailSendBehavior::isSendMailQueuePublish()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * IsMailSendBehavior::isSendMailQueuePublish()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorIsSendMailQueuePublishTest extends NetCommonsModelTestCase {

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
		$this->TestModel = ClassRegistry::init('TestMails.TestIsMailSendBehaviorModel');
	}

/**
 * isSendMailQueuePublish()テストのDataProvider
 *
 * @return array データ
 */
	public function dataProvider() {
		return array(
			'true:正常ケース' => array(
				'isMailSend' => '1',
				'data' => array(
					'TestIsMailSendBehaviorModel' => array(
						'status' => 1,
					),
				),
				'expected' => true,
			),
			'false:公開以外' => array(
				'isMailSend' => '1',
				'data' => array(
					'TestIsMailSendBehaviorModel' => array(
						'status' => 2,
					),
				),
				'expected' => false,
			),
			'false:メール送らない' => array(
				'isMailSend' => '0',
				'data' => array(
					'TestIsMailSendBehaviorModel' => array(
						'status' => 1,
					),
				),
				'expected' => false,
			),
		);
	}

/**
 * isSendMailQueuePublish()のテスト
 *
 * @param strig $isMailSend メール通知機能を使うフラグ
 * @param array $data modelデータ
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testisSendMailQueuePublish($isMailSend,
										$data = array(),
										$expected = null) {
		$this->TestModel->data = $data;

		//テスト実施
		/** @see IsMailSendBehavior::isSendMailQueuePublish() */
		$result = $this->TestModel->isSendMailQueuePublish($isMailSend);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}
}
