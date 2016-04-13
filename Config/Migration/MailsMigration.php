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
 * マイグレーションupの更新と,downの削除
 *
 * @param string $direction Direction of migration process (up or down)
 * @param string $pluginKey プラグインキー
 * @return bool Should process continue
 */
	public function updateAndDelete($direction, $pluginKey) {
		$this->loadModels(array(
			'MailSetting' => 'Mails.MailSetting',
			'MailSettingFixedPhrase' => 'Mails.MailSettingFixedPhrase',
		));

		foreach ($this->records as $model => $records) {
			$conditions = array(
				'plugin_key' => $pluginKey,
				'block_key' => null,
			);

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
				if (!$this->updateRecords($model, $records)) {
					return false;
				}

			} elseif ($direction == 'down') {
				if (!$this->MailSettingFixedPhrase->deleteAll($conditions, false, false)) {
					return false;
				}
				if (!$this->MailSetting->deleteAll($conditions, false, false)) {
					return false;
				}
			}
		}
		return true;
	}
}
