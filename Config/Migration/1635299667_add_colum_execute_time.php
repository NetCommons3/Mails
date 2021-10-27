<?php
/**
 * タイミングによっては同じ記事が複数メール送信されてしまうバグ修正のためカラム追加
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * タイミングによっては同じ記事が複数メール送信されてしまうバグ修正のためカラム追加
 *
 * @package NetCommons\Mails\Config\Migration
 * @see https://github.com/NetCommons3/NetCommons3/issues/1651
 */
class AddColumExecuteTime extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_colum_execute_time';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'mail_queues' => array(
					'execute_time' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '実行日時', 'after' => 'send_time'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'mail_queues' => array('execute_time'),
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
