<?php
/**
 * BlockSettingForMailFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('BlockSettingFixture', 'Blocks.Test/Fixture');

/**
 * Summary for BlockSettingForMailFixture
 */
class BlockSettingForMailFixture extends BlockSettingFixture {

/**
 * Plugin key
 *
 * @var string
 */
	public $pluginKey = 'dummy';

/**
 * Model name
 *
 * @var string
 */
	public $name = 'BlockSetting';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'block_settings';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// ブロック設定後 - room_idあり、block_keyあり
		array(
			'plugin_key' => 'dummy',
			'room_id' => 1,
			'block_key' => 'block_1',
			'field_name' => BlockSettingBehavior::FIELD_USE_WORKFLOW,
			'value' => '1',
			'type' => BlockSettingBehavior::TYPE_NUMERIC,
		),
		array(
			'plugin_key' => 'dummy',
			'room_id' => 1,
			'block_key' => 'block_1',
			'field_name' => BlockSettingBehavior::FIELD_USE_COMMENT_APPROVAL,
			'value' => '1',
			'type' => BlockSettingBehavior::TYPE_NUMERIC,
		),
	);

}
