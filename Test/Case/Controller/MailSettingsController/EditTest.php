<?php
/**
 * MailSettingsController::edit()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('MailSettingFixedPhrase', 'Mails.Model');

/**
 * MailSettingsController::edit()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Controller\MailSettingsController
 */
class MailSettingsControllerEditTest extends NetCommonsControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * Controller name
 *
 * @var string
 */
	protected $_controller = 'mail_settings';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//ログイン
		TestAuthGeneral::login($this);

		$this->MailSetting = ClassRegistry::init('Mails.MailSetting', true);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		//ログアウト
		TestAuthGeneral::logout($this);

		parent::tearDown();
	}

/**
 * edit()アクションのGetリクエストテスト
 *
 * @return void
 */
	public function testEditGet() {
		//テストデータ
		$frameId = '6';
		$blockId = '2';

		//テスト実行
		// http://book.cakephp.org/2.0/ja/development/testing.html#return
		$this->_testGetAction(array('action' => 'edit', 'block_id' => $blockId, 'frame_id' => $frameId),
			array('method' => 'assertEmpty'), null, 'result');

		//var_dump($this->vars);
		//var_dump($this->controller->request->data);
		//チェック
		$this->assertArrayHasKey('permissions', $this->vars);
		$this->assertArrayHasKey('roles', $this->vars);
		$this->assertArrayHasKey('mailSettingPlugin', $this->vars);

		$this->assertArrayHasKey('MailSetting', $this->controller->request->data);
		$this->assertArrayHasKey('MailSettingFixedPhrase', $this->controller->request->data);
		$this->assertArrayHasKey('BlockRolePermission', $this->controller->request->data);
		$this->assertArrayHasKey('Frame', $this->controller->request->data);
	}

/**
 * edit()アクションのPostリクエストテスト
 *
 * @return void
 */
	public function testEditPost() {
		//テストデータ
		$frameId = '6';
		$blockId = '2';
		$blockKey = 'block_1';
		$pluginKey = 'dummy2';

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingPlugin = $this->MailSetting->getMailSettingPlugin(
			null,
			MailSettingFixedPhrase::DEFAULT_TYPE,
			$pluginKey
		);
		$data['MailSetting'] = $mailSettingPlugin['MailSetting'];
		$data['MailSettingFixedPhrase'][0] = $mailSettingPlugin['MailSettingFixedPhrase'];
		// 値セット
		$data['MailSetting']['is_mail_send'] = false;
		$data['MailSettingFixedPhrase'][0]['mail_fixed_phrase_body'] = '更新';
		$data['MailSetting']['block_key'] = $blockKey;
		$data['MailSettingFixedPhrase'][0]['block_key'] = $blockKey;
		// 登録なのでid消し
		unset($data['MailSetting']['id']);
		unset($data['MailSettingFixedPhrase'][0]['id']);
		//var_dump($data);

		//テスト実行
		// http://book.cakephp.org/2.0/ja/development/testing.html#return
		$this->_testPostAction(
			'post',
			$data,
			array('action' => 'edit', 'block_id' => $blockId, 'frame_id' => $frameId),
			null,
			'result'
		);

		$mailSettingPlugin = $this->MailSetting->getMailSettingPlugin(
			null,
			MailSettingFixedPhrase::DEFAULT_TYPE,
			$pluginKey
		);

		//var_dump($mailSettingPlugin);
		//チェック
		$this->assertEquals($data['MailSetting']['is_mail_send'],
			$mailSettingPlugin['MailSetting']['is_mail_send']);
		$this->assertEquals($data['MailSettingFixedPhrase'][0]['mail_fixed_phrase_body'],
			$mailSettingPlugin['MailSettingFixedPhrase']['mail_fixed_phrase_body']);
	}
}
