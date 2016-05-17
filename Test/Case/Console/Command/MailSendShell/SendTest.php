<?php
/**
 * MailSendShell::send()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsConsoleTestCase', 'NetCommons.TestSuite');

/**
 * MailSendShell::send()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Console\Command\MailSendShell
 */
class MailsConsoleCommandMailSendShellSendTest extends NetCommonsConsoleTestCase {

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
 * send()のテスト
 *
 * @return void
 */
	public function testSend() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);

		//チェック
		//		$this->$shell->expects($this->at(0))->method('out')
		//			->with('<error>From Address is empty. [MailSendShell::send] /var/www/app/app/Plugin/Mails/Console/Command/MailSendShell.php (line 119)</error>');

		//テスト実施
		$this->$shell->send();
	}

/**
 * Mockのロード処理
 *
 * @param string $shell ロードするShell名(PluginName.ShellName)
 * @param string $stdinValue 標準入力値
 * @param array $methods メソッド
 * @param bool $construct コンストラクタの有無
 * @return Mock Mockオブジェクト
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	protected function _loadMock($shell, $stdinValue = '', $methods = array(), $construct = true) {
		//		$stdout = $this->getMock('ConsoleOutput', array(), array(), '', false);
		//		$stderr = $this->getMock('ConsoleOutput', array(), array(), '', false);
		if ($stdinValue) {
			$file = fopen(TMP . 'tests' . DS . 'test_stdin', 'w');
			fwrite($file, $stdinValue);
			fclose($file);
			$stdin = new ConsoleInput(TMP . 'tests' . DS . 'test_stdin');
		} else {
			$stdin = $this->getMock('ConsoleInput', array(), array(), '', false);
			$methods += array('in');
		}

		return $this->getMock($shell,
			Hash::merge(array('out', 'hr', 'err', 'createFile', '_stop'), $methods),
			//array($stdout, $stderr, $stdin), '', $construct
			array(), '', $construct
		);
	}

}
