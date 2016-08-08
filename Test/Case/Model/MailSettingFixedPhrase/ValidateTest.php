<?php
/**
 * MailSettingFixedPhrase::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('MailSettingFixedPhraseFixture', 'Mails.Test/Fixture');

/**
 * MailSettingFixedPhrase::validate()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSettingFixedPhrase
 */
class MailSettingFixedPhraseValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting_fixed_phrase',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'MailSettingFixedPhrase';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validates';

/**
 * ValidationErrorのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - field フィールド名
 *  - value セットする値
 *  - message エラーメッセージ
 *  - overwrite 上書きするデータ(省略可)
 *
 * @return array テストデータ
 */
	public function dataProviderValidationError() {
		$data['MailSettingFixedPhrase'] = (new MailSettingFixedPhraseFixture())->records[0];

		//debug($data);
		return array(
			array('data' => $data, 'field' => 'plugin_key', 'value' => null,
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'language_id', 'value' => 'xxx',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'mail_fixed_phrase_subject', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('mails', 'Subject'))),
			array('data' => $data, 'field' => 'mail_fixed_phrase_body', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('mails', 'Body'))),
		);
	}

}
