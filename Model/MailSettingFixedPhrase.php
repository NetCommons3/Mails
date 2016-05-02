<?php
/**
 * MailSettingFixedPhrase Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailsAppModel', 'Mails.Model');

/**
 * メール設定-定型文
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailSettingFixedPhrase extends MailsAppModel {

/**
 * @var string typeのデフォルト値
 * @var string 回答タイプ
 */
	const DEFAULT_TYPE = 'contents',
		ANSWER_TYPE = 'answer';

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
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'language_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
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
 * メール設定-定型文 データ新規作成
 *
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @param string $pluginKey プラグインキー
 * @return array メール設定データ配列
 */
	public function createMailSettingFixedPhrase($languageId = null, $typeKey = self::DEFAULT_TYPE,
													$pluginKey = null) {
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
		$mailFixedPhrase = $this->getMailSettingFixedPhrase($conditions);
		if ($mailFixedPhrase) {
			$mailFixedPhrase = Hash::remove($mailFixedPhrase, '{s}.id');
		} else {
			$mailFixedPhrase = $this->create();
		}

		//初期データセット
		if (! $mailFixedPhrase[$this->alias]['mail_fixed_phrase_subject']) {
			$mailFixedPhrase[$this->alias]['mail_fixed_phrase_subject'] =
				__d('mails', 'MailSetting.mail_fixed_phrase_subject');
		}
		if (! $mailFixedPhrase[$this->alias]['mail_fixed_phrase_body']) {
			$mailFixedPhrase[$this->alias]['mail_fixed_phrase_body'] =
				__d('mails', 'MailSetting.mail_fixed_phrase_body');
		}
		$mailFixedPhrase = Hash::remove($mailFixedPhrase, '{s}.created');
		$mailFixedPhrase = Hash::remove($mailFixedPhrase, '{s}.created_user');
		$mailFixedPhrase = Hash::remove($mailFixedPhrase, '{s}.modified');
		$mailFixedPhrase = Hash::remove($mailFixedPhrase, '{s}.modified_user');

		return $mailFixedPhrase;
	}

/**
 * メール設定-定型文 取得
 *
 * @param array $conditions 検索条件
 * @return array メール設定データ配列
 */
	public function getMailSettingFixedPhrase($conditions) {
		$mailSetting = $this->find('first', array(
			//'recursive' => -1,
			'recursive' => 0,
			'conditions' => $conditions,
		));
		return $mailSetting;
	}

/**
 * メール設定-定型文 保存
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailSettingFixedPhrase($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			return false;
		}

		try {
			// 保存
			if (! $mailFixedPhrase = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailFixedPhrase;
	}
}
