<?php
/**
 * MailSetting::createMailSetting()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * MailSetting::createMailSetting()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSetting
 */
class MailSettingCreateMailSettingTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
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
	protected $_methodName = 'createMailSetting';

/**
 * createMailSetting()のテスト
 *
 * @return void
 */
	public function testCreateMailSetting() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$pluginKey = 'dummy';

		//テスト実施
		$result = $this->$model->$methodName($pluginKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSetting', $result);
	}

/**
 * createMailSetting()のテスト - pluginKey空
 *
 * @return void
 */
	public function testCreateMailSettingEmptypluginKey() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$pluginKey = null;

		//テスト実施
		$result = $this->$model->$methodName($pluginKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSetting', $result);
	}

}
