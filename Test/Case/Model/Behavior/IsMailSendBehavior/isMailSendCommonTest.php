<?php
/**
 * IsMailSendBehavior::isMailSendCommon()のテスト
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
App::uses('SiteSettingUtil', 'SiteManager.Utility');
App::uses('Block', 'Blocks.Model');

/**
 * IsMailSendBehavior::isMailSendCommon()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorIsMailSendCommonTest extends NetCommonsModelTestCase {

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
 * isMailSendCommon()テストのDataProvider
 *
 * @return array データ
 */
	public function dataProvider() {
		return array(
			'true:正常ケース' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'isFromEmpty' => null,
				'data' => array(),
				'expected' => true,
			),
			'false:isMailSendCommon' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => null,
				'settings' => null,
				'isFromEmpty' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:isMailSendCommon - emptyFrom' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => null,
				'settings' => null,
				'isFromEmpty' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:From空' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'isMailSendPost' => '0',
				),
				'isFromEmpty' => '1',
				'data' => array(),
				'expected' => false,
			),
			'false:一時保存はメール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'isFromEmpty' => null,
				'data' => array(
					'TestIsMailSendBehaviorModel' => array(
						'status' => '3'
					)
				),
				'expected' => false,
			),
			'false:ブロック非公開はメール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'isFromEmpty' => null,
				'data' => array(
					'Block' => array(
						'public_type' => Block::TYPE_PRIVATE,
					)
				),
				'expected' => false,
			),
		);
	}

/**
 * isMailSendCommon()のテスト
 *
 * @param string $typeKey メールの種類
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @param array $settings ビヘイビアsetting
 * @param string $isFromEmpty From空フラグ
 * @param array $data modelデータ
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testisMailSendCommon($typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
										$settingPluginKey = null,
										$settings = array(),
										$isFromEmpty = null,
										$data = array(),
										$expected = null) {
		if (isset($isFromEmpty)) {
			SiteSettingUtil::write('Mail.from', '', 0);
		}

		if (isset($settings)) {
			// ビヘイビアのsettingに isMailSendPost を設定
			$this->TestModel->Behaviors->unload('Mails.IsMailSend');
			$this->TestModel->Behaviors->load('Mails.IsMailSend', $settings);
		}
		if (Hash::get($data, 'Block')) {
			Current::write('Block', $data['Block']);
		}
		$this->TestModel->data = $data;

		//テスト実施
		/** @see IsMailSendBehavior::isMailSendCommon() */
		$result = $this->TestModel->isMailSendCommon($typeKey, $settingPluginKey);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}
}
