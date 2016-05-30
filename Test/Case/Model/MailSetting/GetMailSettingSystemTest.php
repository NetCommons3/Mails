<?php
/**
 * MailSetting::getMailSettingSystem()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');

/**
 * MailSetting::getMailSettingSystem()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSetting
 */
class MailSettingGetMailSettingSystemTest extends NetCommonsGetTest {

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
	protected $_methodName = 'getMailSettingSystem';

/**
 * getMailSettingSystem()のテスト
 *
 * @return void
 */
	public function testGetMailSettingSystem() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$typeKey = null;

		//テスト実施
		$result = $this->$model->$methodName($typeKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSetting', $result);
	}

}
