<?php
/**
 * GetReceivableEmailsFromUserTest.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsMail', 'Mails.Utility');

/**
 * Class GetReceivableEmailsFromUserTest
 */
final class GetReceivableEmailsFromUserTest extends \NetCommonsCakeTestCase {

/**
 * @var string[] fixtures
 */
	public $fixtures = [
		'plugin.mails.user_attribute_setting_for_get_receivable_emails_from_user_test'
	];

/**
 * UserAttributeSettingでemailとされてるフィールドで送信が許可されているメールアドレスを返す
 *
 * @return void
 * @throws ReflectionException
 */
	public function testGetEmailsWhereUserAttributeSettingIsEmailAndIsReceptionIsTrue() {
		$user = [
			'User' => [
				'email' => 'email@example.com',
				'is_email_reception' => true,
				'moblie_mail' => 'mobile@example.com',
				'is_moblie_mail_reception' => true,
				'other_mail' => 'other@example.com',
				'is_other_mail_reception' => true,
			]
		];

		$receivableEmails = $this->__invokeGetReceivableEmailsFromUser($user);
		$expected = [
			'email@example.com',
			'mobile@example.com',
			'other@example.com'
		];
		$this->assertSame($expected, $receivableEmails);
	}

/**
 * testメールアドレスが設定されていてもメールを受け取る設定になっていなければメールアドレスを取得できない
 *
 * @return void
 * @throws ReflectionException
 */
	public function testNotGetEmailsWhereIsReceptionIsFalse() {
		$user = [
			'User' => [
				'email' => 'email@example.com',
				'is_email_reception' => false,
				'other_mail' => 'other@example.com',
				'is_other_mail_reception' => false,
			]
		];

		$receivableEmails = $this->__invokeGetReceivableEmailsFromUser($user);

		$this->assertSame([], $receivableEmails);
	}

/**
 * NetCommonsMail::__getReceivableEmailsFromUserの実行
 *
 * @param array $user
 * @return mixed
 * @throws ReflectionException
 */
	private function __invokeGetReceivableEmailsFromUser(array $user) : array {
		$netCommonsMail = new NetCommonsMail();
		$sendMethod = new ReflectionMethod($netCommonsMail, '__getReceivableEmailsFromUser');
		$sendMethod->setAccessible(true);
		return $sendMethod->invoke($netCommonsMail, $user);
	}
}