<?php
/**
 * MailQueueSendRequestFixture
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for MailQueueSendRequestFixture
 */
class MailQueueSendRequestFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID |  |  | '),
		'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
		'block_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
		'replay_to' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'return address | 返信先アドレス | | ', 'charset' => 'utf8mb4'),
		'content_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'content key | 各プラグインのコンテンツKey | | ', 'charset' => 'utf8mb4'),
		'mail_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail subject | メール件名 | | ', 'charset' => 'utf8mb4'),
		'mail_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => 'mail body | メール本文 | | ', 'charset' => 'utf8mb4'),
		'send_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => 'sent date and time | 送信日時 | | '),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_queue_send_requests_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191')),
			'fk_mail_queue_send_requests_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_queue_send_requests_videos1_idx' => array('column' => 'content_key', 'unique' => 0, 'length' => array('content_key' => '191'))
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
			'replay_to' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'content_key' => 'Lorem ipsum dolor sit amet',
			'mail_subject' => 'Lorem ipsum dolor sit amet',
			'mail_body' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'send_time' => '2015-12-09 07:15:09',
			'created_user' => 1,
			'created' => '2015-12-09 07:15:09',
			'modified_user' => 1,
			'modified' => '2015-12-09 07:15:09'
		),
	);

}
