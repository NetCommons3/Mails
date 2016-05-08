<?php
/**
 * SiteSettingFixture
 *
 * @author   Jun Nishikawa <topaz2@m0n0m0n0.com>
 * @link     http://www.netcommons.org NetCommons Project
 * @license  http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Summary for SiteSettingFixture
 */
class SiteSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Key of the record.\\ne.g.) theme_name, site_name', 'charset' => 'utf8'),
		'value' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Value of the record.\\ne.g.) default, My Homepage', 'charset' => 'utf8'),
		'label' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Human friendly label for the record.\\ne.g.) Theme, Site Name', 'charset' => 'utf8'),
		'weight' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'Display order.'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		//		array(
		//			'id' => '2',
		//			'language_id' => '2',
		//			'key' => 'theme',
		//			'value' => 'UnitTestTheme',
		//			'label' => 'Theme',
		//			'weight' => '1',
		//		),
		//		array(
		//			'id' => '3',
		//			'language_id' => '2',
		//			'key' => 'App.default_timezone',
		//			'value' => 'Asia/Tokyo',
		//			'label' => 'SiteTimezone',
		//			'weight' => '1',
		//		),
		//		array(
		//			'id' => '4',
		//			'language_id' => '1',
		//			'key' => 'App.default_timezone',
		//			'value' => 'Asia/Tokyo',
		//			'label' => 'SiteTimezone',
		//			'weight' => '1',
		//		),
		array(
			'id' => '5',
			'language_id' => '0',
			'key' => 'Mail.from',
			'value' => 'from@dummy.com',
			'label' => 'Mail.from',
			'weight' => '1',
		),
	);

}
