<?php
/**
 * MailSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailSettingFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_mail_send' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'メール通知  0:通知しない、1:通知する'),
		'is_mail_send_approval' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '承認メール通知  0:通知しない、1:通知する'),
		'reply_to' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '問合せ先メール', 'charset' => 'utf8'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_settings_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_settings_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191'))
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'plugin_key' => 'dummy',
			'block_key' => 'block_1',
			'is_mail_send' => true,
			'is_mail_send_approval' => true,
			'reply_to' => 'replay-to@test.com',
			'created_user' => 1,
			'modified_user' => 1,
		),
		// block_key=nullは、プラグインの初期データ
		array(
			'id' => 2,
			'plugin_key' => 'dummy',
			'block_key' => null,
			'is_mail_send' => true,
			'is_mail_send_approval' => true,
			'reply_to' => 'replay-to@test.com',
		),
		array(
			'id' => 3,
			'plugin_key' => 'dummy2',
			'block_key' => null,
			'is_mail_send' => true,
			'is_mail_send_approval' => true,
			'reply_to' => 'replay-to@test.com',
		),
	);

}
