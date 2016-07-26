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
class AddIndex extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_index';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'mail_queue_users' => array(
					'mail_queue_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => '個別送信パターン用（user_id,to_address）'),
				),
				'mail_queues' => array(
					'send_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'key' => 'index', 'comment' => 'sent date and time | 送信日時 | | '),
				),
			),
			'drop_field' => array(
				'mail_queue_users' => array('indexes' => array('fk_mail_queue_users_plugins1_idx', 'fk_mail_queue_users_blocks1_idx', 'fk_mail_queue_users_users1_idx', 'fk_mail_queue_users_rooms1_idx')),
				'mail_queues' => array('indexes' => array('fk_mail_queues_plugins1_idx', 'fk_mail_queues_blocks1_idx', 'fk_mail_queues_videos1_idx')),
				'mail_setting_fixed_phrases' => array('indexes' => array('fk_mail_setting_fixed_phrases_plugins1_idx', 'fk_mail_setting_fixed_phrases_blocks1_idx', 'fk_mail_setting_fixed_phrases_languages1_idx')),
				'mail_settings' => array('indexes' => array('fk_mail_settings_blocks1_idx', 'fk_mail_settings_plugins1_idx')),
			),
			'create_field' => array(
				'mail_queue_users' => array(
					'indexes' => array(
						'mail_queue_id' => array('column' => 'mail_queue_id', 'unique' => 0),
					),
				),
				'mail_queues' => array(
					'indexes' => array(
						'send_time' => array('column' => 'send_time', 'unique' => 0),
					),
				),
				'mail_setting_fixed_phrases' => array(
					'indexes' => array(
						'block_key' => array('column' => array('block_key', 'plugin_key', 'language_id', 'type_key'), 'unique' => 0, 'length' => array('191', '191', '191')),
					),
				),
				'mail_settings' => array(
					'indexes' => array(
						'plugin_key' => array('column' => array('plugin_key', 'block_key'), 'unique' => 0, 'length' => array('191', '191')),
					),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'mail_queue_users' => array(
					'mail_queue_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '個別送信パターン用（user_id,to_address）'),
				),
				'mail_queues' => array(
					'send_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => 'sent date and time | 送信日時 | | '),
				),
			),
			'create_field' => array(
				'mail_queue_users' => array(
					'indexes' => array(
						'fk_mail_queue_users_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_queue_users_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_queue_users_users1_idx' => array('column' => 'user_id', 'unique' => 0),
						'fk_mail_queue_users_rooms1_idx' => array('column' => 'room_id', 'unique' => 0),
					),
				),
				'mail_queues' => array(
					'indexes' => array(
						'fk_mail_queues_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_queues_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_queues_videos1_idx' => array('column' => 'content_key', 'unique' => 0, 'length' => array('191')),
					),
				),
				'mail_setting_fixed_phrases' => array(
					'indexes' => array(
						'fk_mail_setting_fixed_phrases_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_setting_fixed_phrases_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_setting_fixed_phrases_languages1_idx' => array('column' => 'language_id', 'unique' => 0),
					),
				),
				'mail_settings' => array(
					'indexes' => array(
						'fk_mail_settings_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('191')),
						'fk_mail_settings_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('191')),
					),
				),
			),
			'drop_field' => array(
				'mail_queue_users' => array('indexes' => array('mail_queue_id')),
				'mail_queues' => array('indexes' => array('send_time')),
				'mail_setting_fixed_phrases' => array('indexes' => array('block_key')),
				'mail_settings' => array('indexes' => array('plugin_key')),
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
