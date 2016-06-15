<?php
/**
 * IsMailSendBehavior::isMailSendTime()のテスト
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
 * IsMailSendBehavior::isMailSendTime()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorIsMailSendTimeTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.site_setting_for_mail',
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
		$this->TestModel = ClassRegistry::init('TestMails.TestIsMailSendBehaviorModel');
	}

/**
 * isMailSendTime()テストのDataProvider
 *
 * @return array データ
 */
	public function dataProvider() {
		$netCommonsTime = new NetCommonsTime();
		$sendTime = $netCommonsTime->toServerDatetime('2027-03-31 14:30:00', 'Asia/Tokyo');
		$sendTimePast = $netCommonsTime->toServerDatetime('2007-03-31 14:30:00', 'Asia/Tokyo');

		return array(
			'true:正常ケース' => array(
				'sendTime' => $sendTime,
				'settings' => array(
					'reminder' => array(
						'useReminder' => 1,
					),
				),
				'expected' => true,
			),
			'true:送信日時なし' => array(
				'sendTime' => null,
				'settings' => null,
				'expected' => true,
			),
			'true:クーロン使える & 未来日 & リマインダーではない' => array(
				'sendTime' => $sendTime,
				'settings' => array(
					'reminder' => array(
						'useReminder' => 0,
					),
				),
				'expected' => true,
			),
			'false:クーロン使える & 過去日 & リマインダー' => array(
				'sendTime' => $sendTimePast,
				'settings' => array(
					'reminder' => array(
						'useReminder' => 1,
					),
				),
				'expected' => false,
			),
		);
	}

/**
 * isMailSendTime()のテスト
 *
 * @param int $sendTime 送信日時
 * @param array $settings ビヘイビアsetting
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testisMailSendTime($sendTime,
										$settings = array(),
										$expected = null) {
		if (isset($settings)) {
			// ビヘイビアのsettingに isMailSendPost を設定
			$this->TestModel->Behaviors->unload('Mails.IsMailSend');
			$this->TestModel->Behaviors->load('Mails.IsMailSend', $settings);
		}

		//テスト実施
		/** @see IsMailSendBehavior::isMailSendTime() */
		$result = $this->TestModel->isMailSendTime($sendTime);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}
}
