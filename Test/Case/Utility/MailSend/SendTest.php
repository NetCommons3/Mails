<?php
/**
 * MailSend::send()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('MailSend', 'Mails.Utility');

/**
 * MailSend::send()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\MailSend
 */
class MailsUtilityMailSendSendTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_queue',
		'plugin.mails.mail_queue_user',
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
 * send()のテスト
 *
 * @return void
 */
	public function testSend() {
		//テスト実施
		MailSend::send();

		//チェック
		$this->assertTrue(true);
	}

}
