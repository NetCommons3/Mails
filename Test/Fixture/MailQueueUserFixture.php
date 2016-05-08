<?php
/**
 * MailQueueUserFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailQueueUserFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailQueueUserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID |  |  | '),
		'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
		'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
		'content_key' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '各プラグインのコンテンツKey', 'charset' => 'utf8mb4'),
		'mail_queue_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '個別送信パターン用（user_id,to_address）'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ユーザに送信, 個別送信パターン1'),
		'room_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ルームに所属しているユーザに送信, 複数人パターン'),
		'to_address' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'メールアドレスで送信, 個別送信パターン2', 'charset' => 'utf8mb4'),
		'send_room_permission' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'ルーム送信時のパーミッション', 'charset' => 'utf8mb4'),
		'not_send_room_user_ids' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'ルーム送信時に送らない複数のユーザ', 'charset' => 'utf8mb4'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_queue_users_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191')),
			'fk_mail_queue_users_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_queue_users_users1_idx' => array('column' => 'user_id', 'unique' => 0),
			'fk_mail_queue_users_rooms1_idx' => array('column' => 'room_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// user_id送信パターン
		array(
			'id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'block_1',
			'content_key' => 'Lorem ipsum dolor sit amet',
			'mail_queue_id' => 1,
			'user_id' => 1,
			'room_id' => null,
			'to_address' => null,
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
			'created_user' => 1,
			'created' => '2016-03-22 12:23:24',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:23:24'
		),
		// メールアドレス送信パターン
		array(
			'id' => 2,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'block_1',
			'content_key' => 'Lorem ipsum dolor sit amet',
			'mail_queue_id' => 1,
			'user_id' => null,
			'room_id' => null,
			'to_address' => 'to@dummp.com',
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
			'created_user' => 1,
			'created' => '2016-03-22 12:23:24',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:23:24'
		),
		// room_id送信パターン
		array(
			'id' => 3,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'block_1',
			'content_key' => 'Lorem ipsum dolor sit amet',
			'mail_queue_id' => 1,
			'user_id' => null,
			'room_id' => 1,
			'to_address' => null,
			'send_room_permission' => 'mail_content_receivable',
			'not_send_room_user_ids' => '1|2',
			'created_user' => 1,
			'created' => '2016-03-22 12:23:24',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:23:24'
		),
		array(
			'id' => 4,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'block_1',
			'content_key' => 'Lorem ipsum dolor sit amet',
			'mail_queue_id' => 1,
			'user_id' => null,
			'room_id' => 1,
			'to_address' => null,
			'send_room_permission' => 'mail_answer_receivable',
			'not_send_room_user_ids' => '1|2',
			'created_user' => 1,
			'created' => '2016-03-22 12:23:24',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:23:24'
		),
	);

}
