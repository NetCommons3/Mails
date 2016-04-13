<?php
/**
 * MailSettingFixedPhraseFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailSettingFixedPhraseFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailSettingFixedPhraseFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mail_setting_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'type_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文の種類', 'charset' => 'utf8mb4'),
		'mail_fixed_phrase_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 件名', 'charset' => 'utf8mb4'),
		'mail_fixed_phrase_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 本文', 'charset' => 'utf8mb4'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_setting_fixed_phrases_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191')),
			'fk_mail_setting_fixed_phrases_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_setting_fixed_phrases_languages1_idx' => array('column' => 'language_id', 'unique' => 0)
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
			'mail_setting_id' => 1,
			'language_id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'block_key' => 'Lorem ipsum dolor sit amet',
			'type_key' => 'Lorem ipsum dolor sit amet',
			'mail_fixed_phrase_subject' => 'Lorem ipsum dolor sit amet',
			'mail_fixed_phrase_body' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created_user' => 1,
			'created' => '2016-03-22 12:21:35',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:21:35'
		),
	);

}
