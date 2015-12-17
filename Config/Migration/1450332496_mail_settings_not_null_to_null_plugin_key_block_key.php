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
class MailSettingsNotNullToNullPluginKeyBlockKey extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'mail_settings_not_null_to_null_plugin_key_block_key';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'mail_settings' => array(
					'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
					'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'mail_settings' => array(
					'plugin_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'plugin key | プラグインKey | plugins.key | ', 'charset' => 'utf8mb4'),
					'block_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'comment' => 'blocks.key | ブロックKey', 'charset' => 'utf8mb4'),
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
