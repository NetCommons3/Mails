<?php
/**
 * MailSettingFixedPhrase::createMailSettingFixedPhrase()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('MailSettingFixedPhrase', 'Mails.Model');

/**
 * MailSettingFixedPhrase::createMailSettingFixedPhrase()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSettingFixedPhrase
 */
class MailSettingFixedPhraseCreateMailSettingFixedPhraseTest extends NetCommonsModelTestCase {

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
	protected $_methodName = 'createMailSettingFixedPhrase';

/**
 * Create用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderCreate() {
		return array(
			'通常' => [
				'languageId' => 2,
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'pluginKey' => 'dummy'
			],
			'通常:デフォルトデータなし' => [
				'languageId' => 2,
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'pluginKey' => 'xxx'
			],
			'回答タイプ:デフォルトデータなし' => [
				'languageId' => null,
				'typeKey' => MailSettingFixedPhrase::ANSWER_TYPE,
				'pluginKey' => null
			],
		);
	}

/**
 * createMailSettingFixedPhrase()のテスト
 *
 * @param int $languageId 言語ID
 * @param string $typeKey メール定型文の種類
 * @param string $pluginKey プラグインキー
 * @dataProvider dataProviderCreate
 * @return void
 */
	public function testCreateMailSettingFixedPhrase($languageId, $typeKey, $pluginKey) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//テスト実施
		$result = $this->$model->$methodName($languageId, $typeKey, $pluginKey);

		//チェック
		//debug($result);
		$this->assertArrayHasKey('MailSettingFixedPhrase', $result);
	}
}
