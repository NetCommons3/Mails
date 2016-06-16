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
App::uses('MailSettingFixedPhrase', 'Mails.Model');

/**
 * メール設定
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
 * use behaviors
 *
 * @var array
 * @see NetCommonsAppModel::$actAs
 * @see OriginalKeyBehavior
 */
	public $actsAs = array(
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
			'plugin_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'is_mail_send' => array(
				'boolean' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'is_mail_send_approval' => array(
				'boolean' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'reply_to' => array(
				'email' => array(
					'rule' => array('email', false, null),
					'message' => sprintf(__d('mails', '%s, please enter by e-mail format'),
						__d('mails', 'E-mail address to receive a reply')),
					'allowEmpty' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * メール設定 データ新規作成
 *
 * @param string $pluginKey プラグインキー
 * @return array メール設定データ配列
 */
	public function createMailSetting($pluginKey = null) {
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}

		//デフォルトデータ取得
		$conditions = array(
			'plugin_key' => $pluginKey,
			'block_key' => null,
		);
		$mailSetting = $this->getMailSetting($conditions);
		if ($mailSetting) {
			$mailSetting = Hash::remove($mailSetting, '{s}.id');
		} else {
			$mailSetting = $this->create();
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
 * @param string|array $typeKey メールの種類(default)|メールの種類(複数)
 * @param string $pluginKey プラグインキー
 * @return array メール設定データ配列
 */
	public function getMailSettingPlugin($languageId = null,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE, $pluginKey = null) {
		$this->loadModels(array(
			'MailSettingFixedPhrase' => 'Mails.MailSettingFixedPhrase',
		));

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

		// $blockKeyで SELECT する
		$conditions = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
		);
		$mailSetting = $this->getMailSetting($conditions);
		if (! $mailSetting) {
			$mailSetting = $this->createMailSetting($pluginKey);
		}

		// $blockKey, $typeKeyでSELECT する
		$conditions['language_id'] = $languageId;
		$conditions['type_key'] = $typeKey;

		if (gettype($typeKey) == 'string') {
			// 通常
			$mailFixedPhrase = $this->MailSettingFixedPhrase->getMailSettingFixedPhrase($conditions);
			if (! $mailFixedPhrase) {
				$mailFixedPhrase = $this->MailSettingFixedPhrase->createMailSettingFixedPhrase($languageId,
					$typeKey);
			}
		} elseif (gettype($typeKey) == 'array') {
			// メール設定画面 複数メール定型文対応
			$fixedPhrases = $this->MailSettingFixedPhrase->getMailSettingFixedPhrase($conditions, 'all');
			if (! $fixedPhrases) {
				foreach ($typeKey as $type) {
					$fixedPhrases[] = $this->MailSettingFixedPhrase->createMailSettingFixedPhrase($languageId,
						$type);
				}
			}
			$mailFixedPhrase['MailSettingFixedPhrase'] = Hash::combine($fixedPhrases,
				'{n}.MailSettingFixedPhrase.type_key', '{n}.MailSettingFixedPhrase');
		}

		$result = Hash::merge($mailSetting, $mailFixedPhrase);
		return $result;
	}

/**
 * システム管理(カレンダー含む)のメール設定(定型文等) 取得
 *
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	public function getMailSettingSystem($typeKey) {
		$this->loadModels(array(
			'MailSettingFixedPhrase' => 'Mails.MailSettingFixedPhrase',
		));
		$pluginKey = Current::read('Plugin.key');

		// $pluginKeyで SELECT する
		$conditions = array(
			'plugin_key' => $pluginKey,
			//'type_key' => $typeKey,
		);
		$mailSetting = $this->getMailSetting($conditions);

		// $blockKey, $typeKeyでSELECT する
		$conditions['type_key'] = $typeKey;
		$mailFixedPhrase = $this->MailSettingFixedPhrase->getMailSettingFixedPhrase($conditions);

		$result = Hash::merge($mailSetting, $mailFixedPhrase);
		return $result;
	}

/**
 * メール設定(定型文等) 取得
 *
 * @param array $conditions 検索条件
 * @return array メール設定データ配列
 */
	public function getMailSetting($conditions) {
		$mailSetting = $this->find('first', array(
			'recursive' => 0,
			'conditions' => $conditions,
		));
		return $mailSetting;
	}

/**
 * メール設定 and メール定型文 保存
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailSettingAndFixedPhrase($data) {
		$this->loadModels(array(
			'MailSettingFixedPhrase' => 'Mails.MailSettingFixedPhrase',
		));

		//トランザクションBegin
		$this->begin();

		//バリデーション - 両方チェックしてからif判定
		$this->set($data);
		$check = $this->validates();
		$checkMany = $this->MailSettingFixedPhrase->validateMany($data['MailSettingFixedPhrase']);
		if (! $check || ! $checkMany) {
			return false;
		}

		try {
			// 保存
			if (! $mailSetting = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// メール設定変更時「通知しない」なら、メールキュー削除
			// idあり(=編集) & 通知しない
			if (isset($data[$this->alias]['id']) && !$data[$this->alias]['is_mail_send']) {
				$blockKey = Current::read('Block.key');
				/** @see MailQueueDeleteBehavior::deleteQueue() */
				$this->deleteQueue($blockKey, 'block_key');
			}

			// 複数レコード保存
			if (! $mailFixedPhrase = $this->MailSettingFixedPhrase->saveMany($data['MailSettingFixedPhrase'],
					array('validate' => false))) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		$result = Hash::merge($mailSetting, $mailFixedPhrase);
		return $result;
	}
}
