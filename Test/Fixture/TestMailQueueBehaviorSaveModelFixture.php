<?php
/**
 * MailQueueBehavior::save()テスト用Fixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailQueueBehavior::save()テスト用Fixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class TestMailQueueBehaviorSaveModelFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => ''),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'charset' => 'utf8mb4'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => ''),
		'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => ''),
		'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => ''),
		'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index',
			'collate' => 'utf8mb4_general_ci', 'comment' => 'コンテンツコメント送信のみ利用 ', 'charset' => 'utf8mb4'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null,
			'collate' => 'utf8mb4_general_ci', 'comment' => 'メール送信の件名で利用', 'charset' => 'utf8mb4'),
		'content' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'charset' => 'utf8mb4'),
		'public_type' => array('type' => 'integer', 'null' => false, 'default' => '2', 'length' => 4, 'unsigned' => false),
		'publish_start' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'publish_end' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => ''),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => ''),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB'),
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'language_id' => '2',
			'key' => 'not_publish_key',
			'status' => '3',
			'is_active' => false,
			'is_latest' => true,
			'plugin_key' => 'dummy',	// コンテンツコメント送信のみ利用. これを基にMailSettingを取得
			'title' => 'Lorem ipsum.',
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'public_type' => '1',
			'publish_start' => null,
			'publish_end' => null,
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		array(
			'id' => '2',
			'language_id' => '2',
			'key' => 'publish_key2',
			'status' => '1',
			'is_active' => true,
			'is_latest' => true,
			'plugin_key' => 'dummy',	// コンテンツコメント送信のみ利用. これを基にMailSettingを取得
			'title' => '件名2',
			'content' => "本文１\r\n本文２\r\n本文３\r\nシステム管理者投稿",
			'public_type' => '1',
			'publish_start' => null,
			'publish_end' => null,
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		array(
			'id' => '3',
			'language_id' => '2',
			'key' => 'publish_key3',
			'status' => '1',
			'is_active' => true,
			'is_latest' => true,
			'plugin_key' => 'dummy',	// コンテンツコメント送信のみ利用. これを基にMailSettingを取得
			'title' => '件名3',
			'content' => "本文１\r\n本文２\r\n本文３\r\n一般投稿",
			'public_type' => '1',
			'publish_start' => '2026-12-29 10:00:00', // 未来日として利用
			'publish_end' => null,
			'created_user' => '4',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '4',
			'modified' => '2016-03-29 10:39:13',
		),
		// public_type=2 一般の限定公開
		array(
			'id' => '4',
			'language_id' => '2',
			'key' => 'publish_key4',
			'status' => '1',
			'is_active' => true,
			'is_latest' => true,
			'plugin_key' => 'dummy',	// コンテンツコメント送信のみ利用. これを基にMailSettingを取得
			'title' => '件名4',
			'content' => "本文１\r\n本文２\r\n本文３\r\n一般の限定公開",
			'public_type' => '2',
			'publish_start' => '2016-03-17 07:10:12',
			'publish_end' => '2026-03-17 07:10:12',
			'created_user' => '4',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '4',
			'modified' => '2016-03-29 10:39:13',
		),
		// status=2 承認待ち
		array(
			'id' => '5',
			'language_id' => '2',
			'key' => 'publish_key5',
			'status' => '2',
			'is_active' => true,
			'is_latest' => true,
			'plugin_key' => 'dummy',	// コンテンツコメント送信のみ利用. これを基にMailSettingを取得
			'title' => '件名5',
			'content' => "本文１\r\n本文２\r\n本文３\r\n一般の承認待ち",
			'public_type' => '1',
			'publish_start' => null,
			'publish_end' => null,
			'created_user' => '4',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '4',
			'modified' => '2016-03-29 10:39:13',
		),
	);

}
