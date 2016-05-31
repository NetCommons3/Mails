<?php
/**
 * MailSetting::saveMailSettingAndFixedPhrase()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsSaveTest', 'NetCommons.TestSuite');
App::uses('MailSettingFixture', 'Mails.Test/Fixture');
App::uses('MailSettingFixedPhraseFixture', 'Mails.Test/Fixture');

/**
 * MailSetting::saveMailSettingAndFixedPhrase()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSetting
 */
class MailSettingSaveMailSettingAndFixedPhraseTest extends NetCommonsSaveTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
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
	protected $_modelName = 'MailSetting';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'saveMailSettingAndFixedPhrase';

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$data['MailSetting'] = (new MailSettingFixture())->records[0];
		$data['MailSettingFixedPhrase'][0] = (new MailSettingFixedPhraseFixture())->records[0];

		$results = array();
		// * 編集の登録処理 - 通知しない
		$results[0] = array($data);
		$results[0] = Hash::insert($results[0], '0.MailSetting.is_mail_send', false);
		// * 新規の登録処理
		$results[1] = array($data);
		$results[1] = Hash::insert($results[1], '0.MailSetting.id', null);
		$results[1] = Hash::remove($results[1], '0.MailSetting.created_user');
		$results[1] = Hash::insert($results[1], '0.MailSettingFixedPhrase.0.id', null);
		$results[1] = Hash::remove($results[1], '0.MailSettingFixedPhrase.0.created_user');

		return $results;
	}

/**
 * SaveのExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnExceptionError() {
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Mails.MailSetting', 'save'),
			array($data, 'Mails.MailSettingFixedPhrase', 'saveMany'),
		);
	}

/**
 * SaveのValidationError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド(省略可：デフォルト validates)
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnValidationError() {
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Mails.MailSetting'),
		);
	}

}
