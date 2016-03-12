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
class AddColumnsKey extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_columns_key';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'mail_queue_users' => array(
					'mail_queue_key' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '複数人パターン用（room_id）', 'charset' => 'utf8mb4', 'after' => 'mail_queue_id'),
				),
				'mail_queues' => array(
					'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4', 'after' => 'id'),
				),
			),
			'alter_field' => array(
				'mail_queue_users' => array(
					'mail_queue_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '個別送信パターン用（user_id,to_address）'),
					'room_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ルームに所属しているユーザに送信, 複数人パターン'),
					'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ユーザに送信, 個別送信パターン1'),
					'to_address' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'メールアドレスで送信, 個別送信パターン2', 'charset' => 'utf8mb4'),
				),
				'mail_queues' => array(
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'ブロック削除用', 'charset' => 'utf8mb4'),
					'content_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'ブロック削除用, 各プラグインのコンテンツキー', 'charset' => 'utf8mb4'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'mail_queue_users' => array('mail_queue_key'),
				'mail_queues' => array('key'),
			),
			'alter_field' => array(
				'mail_queue_users' => array(
					'mail_queue_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'mail queue id | メールキューの送信依頼ID | mail_queues.id | '),
					'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'created user | ユーザID | users.id | '),
					'room_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'room id | ルームID | rooms.id | '),
					'to_address' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail address | メールアドレス | | ', 'charset' => 'utf8mb4'),
				),
				'mail_queues' => array(
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
					'content_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'content key | 各プラグインのコンテンツKey | | ', 'charset' => 'utf8mb4'),
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
