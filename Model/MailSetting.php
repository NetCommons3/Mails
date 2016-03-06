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
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * beforeValidate
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
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
		));

		return parent::beforeValidate($options);
	}

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

/**
 * メール設定保存
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailSetting($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			$this->rollback();
			return false;
		}

		try {
			// 保存
			if (! $mailSetting = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailSetting;
	}
}
