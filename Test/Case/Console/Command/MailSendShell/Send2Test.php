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
	protected $_defaultFixtures = array(
		'plugin.blocks.block',
		'plugin.blocks.block_role_permission',
		'plugin.boxes.box',
		'plugin.boxes.boxes_page',
		'plugin.containers.container',
		'plugin.containers.containers_page',
		'plugin.data_types.data_type',
		'plugin.data_types.data_type_choice',
		'plugin.files.upload_file',
		'plugin.files.upload_files_content',
		'plugin.frames.frame',
		'plugin.m17n.language',
		//'plugin.mails.mail_queue',
		//'plugin.mails.mail_queue_user',
		'plugin.mails.mail_setting',
		'plugin.pages.page',
		'plugin.plugin_manager.plugin',
		//'plugin.plugin_manager.plugins_role',
		//'plugin.roles.default_role_permission',
		'plugin.roles.role',
		'plugin.rooms.roles_room',
		'plugin.rooms.roles_rooms_user',
		'plugin.rooms.room',
		'plugin.rooms.rooms_language',
		//'plugin.rooms.room_role',
		//'plugin.rooms.room_role_permission',
		'plugin.rooms.space',
		'plugin.topics.topic',
		'plugin.topics.topic_readable',
		'plugin.topics.topic_user_status',
		'plugin.user_attributes.user_attribute',
		'plugin.user_attributes.user_attribute_choice',
		'plugin.user_attributes.user_attribute_setting',
		'plugin.user_roles.user_attributes_role',
		'plugin.users.user',
		'plugin.users.users_language',
	);

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_queue_empty',
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
