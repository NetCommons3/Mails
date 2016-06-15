<?php
/**
 * IsMailSendBehaviorテスト用Fixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * IsMailSendBehaviorテスト用Fixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class TestIsMailSendBehaviorModelFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => ''),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => ''),
		'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => ''),
		'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => ''),
		'content' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => ''),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => ''),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB'),
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
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		// publish_key=publish_key 編集あり
		array(
			'id' => '2',
			'language_id' => '2',
			'key' => 'publish_key',
			'status' => '1',
			'is_active' => false,
			'is_latest' => false,
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		array(
			'id' => '3',
			'language_id' => '2',
			'key' => 'publish_key',
			'status' => '1',
			'is_active' => true,
			'is_latest' => true,
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		// publish_key=content_4 一時保存あり
		array(
			'id' => '4',
			'language_id' => '2',
			'key' => 'content_4',
			'status' => '1',
			'is_active' => true,
			'is_latest' => false,
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-03-29 10:39:13'
		),
		array(
			'id' => '5',
			'language_id' => '2',
			'key' => 'content_4',
			'status' => '3',	// 一時保存
			'is_active' => false,
			'is_latest' => true,
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => '1',
			'created' => '2016-03-29 10:39:13',
			'modified_user' => '1',
			'modified' => '2016-04-01 00:39:13'
		),
	);

}
