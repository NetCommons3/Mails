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
class ReviewNotNull extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'review_not_null';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'mail_queue_users' => array(
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
					'room_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'room id | ルームID | rooms.id | '),
					'to_address' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail address | メールアドレス | | ', 'charset' => 'utf8mb4'),
				),
				'mail_queues' => array(
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
					'replay_to' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
					'content_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'content key | 各プラグインのコンテンツKey | | ', 'charset' => 'utf8mb4'),
				),
				'mail_settings' => array(
					'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
					'is_mail_send' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'notified mail, 0: not send, 1: send | メール通知  0:通知しない、1:通知する |  | '),
					'replay_to' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'mail_queue_users' => array(
					'block_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index', 'comment' => 'room id | ルームID | rooms.id | '),
					'to_address' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail address | メールアドレス | | ', 'charset' => 'utf8mb4'),
				),
				'mail_queues' => array(
					'block_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
					'replay_to' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
					'content_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'content key | 各プラグインのコンテンツKey | | ', 'charset' => 'utf8mb4'),
				),
				'mail_settings' => array(
					'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
					'is_mail_send' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'notified mail, 0: not send, 1: send | メール通知  0:通知しない、1:通知する |  | '),
					'replay_to' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
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
