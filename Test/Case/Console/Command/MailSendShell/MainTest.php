<?php
/**
 * MailSendShell::main()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsConsoleTestCase', 'NetCommons.TestSuite');

/**
 * MailSendShell::main()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Console\Command\MailSendShell
 */
class MailsConsoleCommandMailSendShellMainTest extends NetCommonsConsoleTestCase {

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
 * Shell name
 *
 * @var string
 */
	protected $_shellName = 'MailSendShell';

/**
 * main()のテスト
 *
 * @return void
 */
	public function testMain() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);

		//テスト実施
		$this->$shell->main();

		//チェック
		$useCron = SiteSettingUtil::read('Mail.use_cron', false);
		//debug($useCron);
		$this->assertEquals(1, $useCron);
	}
}
