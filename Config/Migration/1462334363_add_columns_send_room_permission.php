<?php
class AddColumnsSendRoomPermission extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_columns_send_room_permission';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'mail_queue_users' => array(
					'send_room_permission' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'ルーム送信時のパーミッション', 'charset' => 'utf8mb4', 'after' => 'to_address'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'mail_queue_users' => array('send_room_permission'),
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
