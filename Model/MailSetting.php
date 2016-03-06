<?php
/**
 * MailSetting Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailsAppModel', 'Mails.Model');

/**
 * メールセッティング
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailSetting extends MailsAppModel {

/**
 * Use database config
 *
 * @var string
 */
	public $useDbConfig = 'master';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'plugin_key' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		//		'block_key' => array(
		//			'notBlank' => array(
		//				'rule' => array('notBlank'),
		//				//'message' => 'Your custom message here',
		//				//'allowEmpty' => false,
		//				//'required' => false,
		//				//'last' => false, // Stop validation after this rule
		//				//'on' => 'create', // Limit validation to 'create' or 'update' operations
		//			),
		//		),
		'type_key' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'mail_fixed_phrase_subject' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'mail_fixed_phrase_body' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'replay_to' => array(
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				'allowEmpty' => true,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

/**
 * プラグインの定型文を取得する
 *
 * @param string $blockKey ブロックキー
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	public function getMailSettingPlugin($blockKey = null, $typeKey = 'contents') {
		if ($blockKey === null) {
			$blockKey = Current::read('Block.key');
		}

		// $blockKey, $typeKeyで、mail_settings を SELECT する
		$conditions = array(
			'block_key' => $blockKey,
			'type_key' => $typeKey,
		);

		return $this->getMailSetting($conditions);
	}

/**
 * システム管理(カレンダー含む)の定型文を取得する
 *
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	public function getMailSettingSystem($typeKey) {
		//public function getMailSettingSystem($pluginKey = null, $typeKey = 'contents') {
		$pluginKey = Current::read('Plugin.key');

		// $pluginKey, $typeKeyで、mail_settings を SELECT する
		$conditions = array(
			'plug_key' => $pluginKey,
			'type_key' => $typeKey,
		);
		return $this->getMailSetting($conditions);
	}

/**
 * プラグインの定型文を取得する
 *
 * @param array $conditions 検索条件
 * @return array メール設定データ配列
 */
	public function getMailSetting($conditions) {
		$mailSettingData = $this->find('first', array(
			//'recursive' => -1,
			'recursive' => 0,
			'conditions' => $conditions,
		));
		return $mailSettingData;
	}
}
