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
 * メール設定
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailSetting extends MailsAppModel {

/**
 * typeのデフォルト値
 *
 * @var int
 */
	const DEFAULT_TYPE = 'contents';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * use behaviors
 *
 * @var array
 * @see NetCommonsAppModel::$actAs
 * @see OriginalKeyBehavior
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'Blocks.BlockRolePermission',
	);

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
			'is_mail_send' => array(
				'boolean' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'replay_to' => array(
				'email' => array(
					'rule' => array('email'),
					'message' => sprintf(__d('mails', '%s, please enter by e-mail format'), __d('mails', 'E-mail address to receive a reply')),
					'allowEmpty' => true,
				),
			),
			'mail_fixed_phrase_subject' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('mails', 'Subject')),
					'required' => true,
				),
			),
			'mail_fixed_phrase_body' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('mails', 'Body')),
					'required' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * メール設定 データ新規作成
 *
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @param string $pluginKey プラグインキー
 * @return array メール設定データ配列
 */
	public function createMailSetting($languageId = null, $typeKey = self::DEFAULT_TYPE, $pluginKey = null) {
		if ($languageId === null) {
			$languageId = Current::read('Language.id');
		}
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}

		//デフォルトデータ取得
		$conditions = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => null,
			'type_key' => $typeKey,
		);
		$mailSetting = $this->getMailSetting($conditions);
		if ($mailSetting) {
			$mailSetting = Hash::remove($mailSetting, '{s}.id');
		} else {
			$mailSetting = $this->create();
		}

		//初期データセット
		if (! $mailSetting[$this->alias]['mail_fixed_phrase_subject']) {
			$mailSetting[$this->alias]['mail_fixed_phrase_subject'] = __d('mails', 'MailSetting.mail_fixed_phrase_subject');
		}
		if (! $mailSetting[$this->alias]['mail_fixed_phrase_body']) {
			$mailSetting[$this->alias]['mail_fixed_phrase_body'] = __d('mails', 'MailSetting.mail_fixed_phrase_body');
		}
		$mailSetting = Hash::remove($mailSetting, '{s}.created');
		$mailSetting = Hash::remove($mailSetting, '{s}.created_user');
		$mailSetting = Hash::remove($mailSetting, '{s}.modified');
		$mailSetting = Hash::remove($mailSetting, '{s}.modified_user');

		return $mailSetting;
	}

/**
 * プラグインのメール設定(定型文等) 取得
 *
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @param string $pluginKey プラグインキー
 * @return array メール設定データ配列
 */
	public function getMailSettingPlugin($languageId = null, $typeKey = 'contents', $pluginKey = null) {
		//if ($blockKey === null) {
		//	$blockKey = Current::read('Block.key');
		//}
		if ($languageId === null) {
			$languageId = Current::read('Language.id');
		}
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}
		$blockKey = Current::read('Block.key');

		// $blockKey, $typeKeyで、mail_settings を SELECT する
		$conditions = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'type_key' => $typeKey,
		);
		$mailSetting = $this->getMailSetting($conditions);

		if (! $mailSetting) {
			$mailSetting = $this->createMailSetting($languageId, $typeKey);
		}

		return $mailSetting;
	}

/**
 * システム管理(カレンダー含む)のメール設定(定型文等) 取得
 *
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	public function getMailSettingSystem($typeKey) {
		$pluginKey = Current::read('Plugin.key');

		// $pluginKey, $typeKeyで、mail_settings を SELECT する
		$conditions = array(
			'plug_key' => $pluginKey,
			'type_key' => $typeKey,
		);
		return $this->getMailSetting($conditions);
	}

/**
 * メール設定(定型文等) 取得
 *
 * @param array $conditions 検索条件
 * @return array メール設定データ配列
 */
	public function getMailSetting($conditions) {
		$mailSetting = $this->find('first', array(
			//'recursive' => -1,
			'recursive' => 0,
			'conditions' => $conditions,
		));
		return $mailSetting;
	}

/**
 * メール設定 保存
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
			return false;
		}

		try {
			// 保存
			if (! $mailSetting = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// ここに、メール設定変更時「通知しない」なら、メールキュー削除 処理を記述

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailSetting;
	}
}
