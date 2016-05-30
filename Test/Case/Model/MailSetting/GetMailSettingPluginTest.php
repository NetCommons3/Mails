<?php
/**
 * MailSetting::getMailSettingPlugin()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');

/**
 * MailSetting::getMailSettingPlugin()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSetting
 */
class MailSettingGetMailSettingPluginTest extends NetCommonsGetTest {

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
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'MailSetting';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getMailSettingPlugin';

/**
 * getMailSetting()のテスト
 *
 * @return void
 */
	public function testGetMailSetting() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$languageId = null;
		$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE;
		$pluginKey = null;

		//テスト実施
		$result = $this->$model->$methodName($languageId, $typeKey, $pluginKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSetting', $result);
		$this->assertArrayHasKey('MailSettingFixedPhrase', $result);
	}

/**
 * getMailSetting()のテスト - 複数
 *
 * @return void
 */
	public function testGetMailSettings() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$languageId = null;
		$typeKey = array(
			MailSettingFixedPhrase::DEFAULT_TYPE,
			MailSettingFixedPhrase::ANSWER_TYPE
		);
		$pluginKey = null;

		//テスト実施
		$result = $this->$model->$methodName($languageId, $typeKey, $pluginKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSetting', $result);
		$this->assertArrayHasKey(MailSettingFixedPhrase::DEFAULT_TYPE,
			$result['MailSettingFixedPhrase']);
		$this->assertArrayHasKey(MailSettingFixedPhrase::ANSWER_TYPE,
			$result['MailSettingFixedPhrase']);
	}

}
