<?php
/**
 * NetCommonsMail::initPlugin()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsMail', 'Mails.Utility');

/**
 * NetCommonsMail::initPlugin()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMail
 */
class MailsUtilityNetCommonsMailInitPluginTest extends NetCommonsCakeTestCase {

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
 * メール
 *
 * @var object
 */
	public $mail = null;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->mail = new NetCommonsMail();
		SiteSettingUtil::write('App.default_timezone', 'Asia/Tokyo', 0);
	}

/**
 * initPlugin()のテスト
 *
 * @return void
 */
	public function testInitPlugin() {
		//データ生成
		$languageId = null;
		$pluginName = null;

		//テスト実施
		$this->mail->initPlugin($languageId, $pluginName);

		//チェック
		//debug($this->mail->mailAssignTag->assignTags);
		$this->assertNotEmpty($this->mail->mailAssignTag->assignTags);
	}

}
