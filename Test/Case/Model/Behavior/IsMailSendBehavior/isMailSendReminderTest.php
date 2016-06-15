<?php
/**
 * IsMailSendBehavior::IsMailSendReminder()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('MailSettingFixedPhrase', 'Mails.Model');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');
App::uses('TestIsMailSendBehaviorModelFixture', 'Mails.Test/Fixture');
App::uses('NetCommonsTime', 'NetCommons.Utility');

/**
 * IsMailSendBehavior::IsMailSendReminder()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorIsMailSendReminderTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.mails.room_role_permission_for_mail',
		'plugin.mails.site_setting_for_mail',
		'plugin.mails.test_is_mail_send_behavior_model',
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
 * isMailSendReminder()テストのDataProvider
 *
 * @return array データ
 */
	public function dataProvider() {
		$data['TestIsMailSendBehaviorModel'] =
			(new TestIsMailSendBehaviorModelFixture())->records[1];

		$netCommonsTime = new NetCommonsTime();
		$sendTimes = array(
			$netCommonsTime->toServerDatetime('2027-03-31 14:30:00', 'Asia/Tokyo'),
			$netCommonsTime->toServerDatetime('2027-04-20 13:30:00', 'Asia/Tokyo'),
		);
		$sendTimesPast = array(
			$netCommonsTime->toServerDatetime('2007-03-31 14:30:00', 'Asia/Tokyo'),
			$netCommonsTime->toServerDatetime('2007-04-20 13:30:00', 'Asia/Tokyo'),
		);

		return array(
			'true:正常ケース' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'reminder' => array(
						'sendTimes' => $sendTimes,
						'useReminder' => 1,
					),
				),
				'permission' => null,
				'data' => $data,
				'expected' => true,
			),
			'false:リマインダーOFF' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'permission' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:リマインダーの公開以外はメール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'reminder' => array(
						'sendTimes' => $sendTimes,
						'useReminder' => 1,
					),
				),
				'permission' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:isMailSendCommon' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => null,
				'settings' => array(
					'reminder' => array(
						'sendTimes' => $sendTimes,
						'useReminder' => 1,
					),
				),
				'permission' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:リマインダーが複数日あって、全て日時が過ぎてたら、メール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'reminder' => array(
						'sendTimes' => $sendTimesPast,
						'useReminder' => 1,
					),
				),
				'permission' => null,
				'data' => $data,
				'expected' => false,
			),
		);
	}

/**
 * isMailSendReminder()のテスト
 *
 * @param string $typeKey メールの種類
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @param array $settings ビヘイビアsetting
 * @param string $permission パーミッション
 * @param array $data modelデータ
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testIsMailSendReminder($typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
												$settingPluginKey = null,
												$settings = array(),
												$permission = null,
												$data = array(),
												$expected = null) {
		if (isset($permission)) {
			Current::write('Permission.' . $permission . '.value', 1);
		}

		if (isset($settings)) {
			// ビヘイビアのsettingに isMailSendPost を設定
			$this->TestModel->Behaviors->unload('Mails.IsMailSend');
			$this->TestModel->Behaviors->load('Mails.IsMailSend', $settings);
		}
		$this->TestModel->data = $data;

		//テスト実施
		/** @see IsMailSendBehavior::isMailSendReminder() */
		$result = $this->TestModel->isMailSendReminder($typeKey, $settingPluginKey);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}
}
