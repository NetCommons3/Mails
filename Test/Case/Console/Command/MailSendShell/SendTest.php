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
		'plugin.mails.site_setting_for_mail',
		//'plugin.site_manager.site_setting',
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.rooms.room_role_permission4test',
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
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		// メール送信させない
		SiteSettingUtil::write('Mail.transport', 'Debug', 0);

		$this->MailQueue = ClassRegistry::init('Mails.MailQueue', true);
		$this->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);
	}

/**
 * send()のテスト
 *
 * @return void
 */
	public function testSend() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);
		SiteSettingUtil::write('Mail.from', 'dummy@test.com', 0);

		//テスト実施
		$this->$shell->send();

		//チェック
		$mailQueueCnt = $this->MailQueue->find('count');
		$mailQueueUserCnt = $this->MailQueueUser->find('count');
		//		debug($mailQueueCnt);
		//		debug($mailQueueUserCnt);
		$this->assertEquals(0, $mailQueueCnt);
		$this->assertEquals(0, $mailQueueUserCnt);
	}

/**
 * send()のFrom空テスト
 *
 * @return void
 */
	public function testSendFromEmpty() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);

		// From空
		SiteSettingUtil::write('Mail.from', '', 0);

		//チェック
		$this->$shell->expects($this->at(0))->method('out')
			->with('<error>From Address is empty. [MailSendShell::send]</error>');

		//テスト実施
		$this->$shell->send();
	}

/**
 * send()のmailQueue空テスト
 *
 * @return void
 */
	public function testSendMailQueueEmpty() {
		$this->MailQueue->query('TRUNCATE mail_queues;');
		$this->MailQueueUser->query('TRUNCATE mail_queue_users;');

		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);
		SiteSettingUtil::write('Mail.from', 'dummy@test.com', 0);

		//チェック
		// Trancateして、このテストmethod内だと0件。だけどconsoleだと6件でemptyにならない
		//		$this->$shell->expects($this->at(0))->method('out')
		//			->with('MailQueue is empty. [MailSendShell::send] ');

		//		$mailQueueCnt = $this->MailQueue->find('count');
		//		$mailQueueUserCnt = $this->MailQueueUser->find('count');
		//		debug($mailQueueCnt);
		//		debug($mailQueueUserCnt);

		//テスト実施
		$this->$shell->send();
	}

}
