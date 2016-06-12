<?php
/**
 * MailSetting::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('MailSettingFixture', 'Mails.Test/Fixture');

/**
 * MailSetting::validate()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailSetting
 */
class MailSettingValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

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
		$data['MailSetting'] = (new MailSettingFixture())->records[0];

		//debug($data);
		return array(
			array('data' => $data, 'field' => 'plugin_key', 'value' => null,
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_mail_send', 'value' => 'xxx',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_mail_send_approval', 'value' => 'xxx',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'replay_to', 'value' => 'hoge',
				'message' => sprintf(__d('mails', '%s, please enter by e-mail format'),
					__d('mails', 'E-mail address to receive a reply'))),
		);
	}

}
