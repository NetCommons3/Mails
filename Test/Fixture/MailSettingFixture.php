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
 * Summary for MailSettingFixture
 */
class MailSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID |  |  | '),
		'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
		'block_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
		'type_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'type key | 定型文の種類', 'charset' => 'utf8mb4'),
		'is_mail_send' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'notified mail, 0: not send, 1: send | メール通知  0:通知しない、1:通知する |  | '),
		'mail_fixed_phrase_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'fixed_phrase subject | 定型文 件名 | | ', 'charset' => 'utf8mb4'),
		'mail_fixed_phrase_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'fixed_phrase body | 定型文 本文 | | ', 'charset' => 'utf8mb4'),
		'etc_to_address' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail address to be notified outside the editor-in-chief | その他に通知するメールアドレス | | ', 'charset' => 'utf8mb4'),
		'replay_to' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_settings_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_settings_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191'))
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'Lorem ipsum dolor sit amet',
			'type_key' => 'Lorem ipsum dolor sit amet',
			'is_mail_send' => 1,
			'mail_fixed_phrase_subject' => 'Lorem ipsum dolor sit amet',
			'mail_fixed_phrase_body' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'etc_to_address' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'replay_to' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => 1,
			'created' => '2015-12-09 07:09:40',
			'modified_user' => 1,
			'modified' => '2015-12-09 07:09:40'
		),
	);

}
