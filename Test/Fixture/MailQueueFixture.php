<?php
/**
 * MailQueueFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailQueueFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailQueueFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false),
		'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'プラグインKey', 'charset' => 'utf8'),
		'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'ブロック削除用', 'charset' => 'utf8'),
		'reply_to' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '問合せ先メール', 'charset' => 'utf8'),
		'content_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'ブロック削除用, 各プラグインのコンテンツキー', 'charset' => 'utf8'),
		'mail_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'メール件名', 'charset' => 'utf8'),
		'mail_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'メール本文', 'charset' => 'utf8'),
		'send_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => '送信日時'),
		'execute_time' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '実行日時'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_queues_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191')),
			'fk_mail_queues_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_queues_videos1_idx' => array('column' => 'content_key', 'unique' => 0, 'length' => array('content_key' => '191'))
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
			'language_id' => '2',
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'Lorem ipsum dolor sit amet',
			'reply_to' => 'reply_to@dummy.com',
			'content_key' => 'content_1',
			'mail_subject' => '件名',
			'mail_body' => "本文１\r\n本文２\r\n本文３\r\n",
			'send_time' => '2016-03-22 12:22:15',
			'execute_time' => null,
		),
		array(
			'id' => 2,
			'language_id' => '2',
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'block_999',
			'reply_to' => null,
			'content_key' => 'content_2',
			'mail_subject' => '件名2',
			'mail_body' => "本文１\r\n本文２\r\n本文３\r\n",
			'send_time' => '2016-03-22 12:22:15',
			'execute_time' => null,
		),
	);

}
