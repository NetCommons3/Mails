<?php
/**
 * MailQueueBehavior::saveQueuePostMail()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('MailSettingFixedPhrase', 'Mails.Model');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');
App::uses('TestMailQueueBehaviorSaveModelFixture', 'Mails.Test/Fixture');


/**
 * MailQueueBehavior::saveQueuePostMail()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueBehavior
 */
class MailQueueBehaviorSaveQueuePostMailTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		//'plugin.mails.site_setting_for_mail',
		'plugin.site_manager.site_setting',
		'plugin.mails.test_mail_queue_behavior_save_model',
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		//'plugin.rooms.room_role_permission4test',
		'plugin.mails.room_role_permission_for_mail',
		'plugin.user_attributes.user_role_setting4test',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Mails', 'TestMails');
		$this->TestModel = ClassRegistry::init('TestMails.TestMailQueueBehaviorSaveModel');

//		$this->MailQueue = ClassRegistry::init('Mails.MailQueue', true);
		$this->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);
	}

/**
 * saveQueuePostMail()テストのDataProvider
 *
 * ### 戻り値
 *  - settingKey セッティングのキー
 *  - settingValue セッティングの値
 *
 * @return array データ
 */
	public function dataProvider() {
		$data = array(
			'TestMailQueueBehaviorSaveModel' => (new TestMailQueueBehaviorSaveModelFixture())
				->records[1],
		);
		$result[] = array(
			'data' => $data,
			'languageId' => 2,
			'sendTimes' => null,
			'userIds' => array(10),
			'toAddresses' => array('dummy@dummy.com'),
			'roomId' => 1,
			'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
		);

		return $result;
	}

/**
 * saveQueuePostMail()のテスト
 *
 * @param array $data 登録データ
 * @param int $languageId 言語ID
 * @param array $sendTimes メール送信日時 配列
 * @param array $userIds 送信ユーザID 配列
 * @param array $toAddresses 送信先メールアドレス 配列
 * @param int $roomId ルームID
 * @param string $typeKey メールの種類
 * @dataProvider dataProvider
 * @return void
 */
	public function testSaveQueuePostMail($data, $languageId, $sendTimes = null, $userIds = null,
										$toAddresses = null, $roomId = null,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$this->TestModel->data = $data;
		$this->TestModel->setSetting('pluginKey', 'dummy_check');

		// 末尾定型文
		$mailBodyAfter = "\n\n登録内容確認画面URL\n{X-URL}";
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_MAIL_BODY_AFTER,
			$mailBodyAfter);
		$mailBodyAfterCheck = "\n\n登録内容確認画面URL\n<a href='http://";

		//テスト実施
		/** @see MailQueueBehavior::saveQueuePostMail() */
		$this->TestModel->saveQueuePostMail($languageId, $sendTimes, $userIds,
			$toAddresses, $roomId, $typeKey);

		//チェック
		// ユーザ配信1件
		$results = $this->MailQueueUser->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'MailQueueUser.plugin_key' => 'dummy_check',
				'MailQueueUser.user_id' => $userIds,
			)
		));
		//debug($results);
		$this->assertCount(1, $results);
		// 末尾定型文なし
		$this->assertTextNotContains($mailBodyAfterCheck, $results[0]['MailQueue']['mail_body']);

		// メール配信1件
		$results = $this->MailQueueUser->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'MailQueueUser.plugin_key' => 'dummy_check',
				'MailQueueUser.to_address' => $toAddresses,
			)
		));
		//debug($results);
		$this->assertCount(1, $results);
		// 末尾定型文なし
		$this->assertTextNotContains($mailBodyAfterCheck, $results[0]['MailQueue']['mail_body']);

		// ルーム配信1件
		$results = $this->MailQueueUser->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'MailQueueUser.plugin_key' => 'dummy_check',
				'MailQueueUser.room_id' => $roomId,
			)
		));
		//debug($results);
		$this->assertCount(1, $results);
		// 末尾定型文を含む
		$this->assertTextContains($mailBodyAfterCheck, $results[0]['MailQueue']['mail_body']);
	}

/**
 * saveQueuePostMail()の例外テスト - Plugin.key=nullでvalidateエラーのため発生
 *
 * @param array $data 登録データ
 * @param int $languageId 言語ID
 * @param array $sendTimes メール送信日時 配列
 * @param array $userIds 送信ユーザID 配列
 * @param array $toAddresses 送信先メールアドレス 配列
 * @param int $roomId ルームID
 * @param string $typeKey メールの種類
 * @dataProvider dataProvider
 * @return void
 */
	public function testSaveQueuePostMailException($data, $languageId, $sendTimes = null, $userIds = null,
										$toAddresses = null, $roomId = null,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$this->TestModel->data = $data;
		$this->setExpectedException('InternalErrorException');

		$this->TestModel->setSetting('pluginKey', null);

		//テスト実施
		/** @see MailQueueBehavior::saveQueuePostMail() */
		$this->TestModel->saveQueuePostMail($languageId, $sendTimes, $userIds,
			$toAddresses, $roomId, $typeKey);
	}
}
