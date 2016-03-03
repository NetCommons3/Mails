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
class RenameColumsMailQueueId extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'rename_colums_mail_queue_id';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'mail_queue_users' => array(
					'mail_queue_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'mail queue id | メールキューの送信依頼ID | mail_queues.id | ', 'after' => 'block_key'),
				),
			),
			'drop_field' => array(
				'mail_queue_users' => array('mail_queue_send_request_id'),
			),
		),
		'down' => array(
			'drop_field' => array(
				'mail_queue_users' => array('mail_queue_id'),
			),
			'create_field' => array(
				'mail_queue_users' => array(
					'mail_queue_send_request_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'mail queue send request id | キューの送信依頼ID | mail_queue_send_requests.id | '),
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
