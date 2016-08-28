<?php
/**
 * MailsMigration
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * MailsMigration
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Config\Migration
 */
class MailsMigration extends NetCommonsMigration {

/**
 * plugin data
 *
 * @var array $migration
 */
	public $records = array();

/**
 * マイグレーションupの更新と,downの削除
 *
 * @param string $direction Direction of migration process (up or down)
 * @param string $pluginKey プラグインキー
 * @return bool Should process continue
 */
	public function updateAndDelete($direction, $pluginKey) {
		$this->loadModels(array(
			'MailSetting' => 'Mails.MailSetting',
		));
		$conditions = array(
			'plugin_key' => $pluginKey,
			'block_key' => null,
		);
		// コールバックoff
		$validate = array(
			'validate' => false,
			'callbacks' => false,
		);

		foreach ($this->records as $model => $records) {
			$Model = $this->generateModel($model);
			if ($direction == 'up') {
				if ($model == 'MailSettingFixedPhrase') {
					// mail_setting_id セット
					$data = $this->MailSetting->find('first', array(
						'recursive' => -1,
						'conditions' => $conditions,
						'callbacks' => false,
					));
					foreach ($records as &$record) {
						$record['mail_setting_id'] = $data['MailSetting']['id'];
					}
				}

				// 登録
				foreach ($records as $record2) {
					$Model->create();
					if (!$Model->save($record2, $validate)) {
						return false;
					}
				}

			} elseif ($direction == 'down') {
				if (!$Model->deleteAll($conditions, false, false)) {
					return false;
				}
			}
		}
		return true;
	}
}
