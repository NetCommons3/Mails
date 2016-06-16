<?php
/**
 * MailSendShell::send()のmailQueue空テスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsConsoleTestCase', 'NetCommons.TestSuite');

/**
 * MailSendShell::send()のmailQueue空テスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Console\Command\MailSendShell
 */
class MailsConsoleCommandMailSendShellSend2Test extends NetCommonsConsoleTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_queue',
		'plugin.mails.mail_queue_user',
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
	}

/**
 * send()のmailQueue空テスト
 *
 * @return void
 */
	public function testSendMailQueueEmpty() {
		//mailQueue削除
		$conditions = array(
			'content_key' => ['content_1', 'content_2']
		);
		$this->MailQueue->deleteAll($conditions);

		$shell = $this->_shellName;
		$this->$shell = $this->loadShell($shell);
		SiteSettingUtil::write('Mail.from', 'dummy@test.com', 0);

		//チェック
		$this->$shell->expects($this->at(0))->method('out')
			->with('MailQueue is empty. [MailSendShell::send] ');

		//テスト実施
		$this->$shell->send();
	}
}
