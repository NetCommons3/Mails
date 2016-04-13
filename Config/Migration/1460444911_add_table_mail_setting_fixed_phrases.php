<?php
/**
 * Migration file
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Mails CakeMigration
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Config\Migration
 */
class AddTableMailSettingFixedPhrases extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_table_mail_setting_fixed_phrases';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'mail_setting_fixed_phrases' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
					'mail_setting_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false, 'key' => 'index'),
					'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
					'type_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文の種類', 'charset' => 'utf8mb4'),
					'mail_fixed_phrase_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 件名', 'charset' => 'utf8mb4'),
					'mail_fixed_phrase_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 本文', 'charset' => 'utf8mb4'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'fk_mail_setting_fixed_phrases_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_setting_fixed_phrases_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_setting_fixed_phrases_languages1_idx' => array('column' => 'language_id', 'unique' => 0),
					),
					'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB'),
				),
			),
			'drop_field' => array(
				'mail_settings' => array('language_id', 'type_key', 'mail_fixed_phrase_subject', 'mail_fixed_phrase_body'),
			),
			'alter_field' => array(
				'mail_settings' => array(
					'is_mail_send' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'メール通知  0:通知しない、1:通知する'),
					'replay_to' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '返信先アドレス', 'charset' => 'utf8mb4'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'mail_setting_fixed_phrases'
			),
			'create_field' => array(
				'mail_settings' => array(
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false),
					'type_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'type key | 定型文の種類', 'charset' => 'utf8mb4'),
					'mail_fixed_phrase_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'fixed_phrase subject | 定型文 件名 | | ', 'charset' => 'utf8mb4'),
					'mail_fixed_phrase_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'fixed_phrase body | 定型文 本文 | | ', 'charset' => 'utf8mb4'),
				),
			),
			'alter_field' => array(
				'mail_settings' => array(
					'is_mail_send' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'notified mail, 0: not send, 1: send | メール通知  0:通知しない、1:通知する |  | '),
					'replay_to' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
