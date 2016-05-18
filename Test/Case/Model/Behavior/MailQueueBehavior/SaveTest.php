<?php
/**
 * MailQueueBehavior::save()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('TestMailQueueBehaviorSaveModelFixture', 'Mails.Test/Fixture');

/**
 * MailQueueBehavior::save()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueBehavior
 */
class MailQueueBehaviorSaveTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.mails.site_setting_for_mail',
		'plugin.mails.test_mail_queue_behavior_save_model',
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

		Current::write('Block.key', 'block_1');
		Current::write('Block.name', 'テストメールブロック');
		Current::write('Plugin.key', 'Dummy');
		Current::write('Plugin.name', 'ダミー');
		Current::write('Room.id', 1);
		SiteSettingUtil::write('App.default_timezone', 'Asia/Tokyo', 0);
		$this->MailQueue = ClassRegistry::init('Mails.MailQueue', true);
		$this->MailQueueUser = ClassRegistry::init('Mails.MailQueueUser', true);

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Mails', 'TestMails');
		$this->TestModel = ClassRegistry::init('TestMails.TestMailQueueBehaviorSaveModel');
	}

/**
 * save()のテスト - ルーム配信
 *
 * @return void
 */
	public function testSaveSendRoom() {
		//テストデータ
		$data = array(
			'TestMailQueueBehaviorSaveModel' => (new TestMailQueueBehaviorSaveModelFixture())
				->records[1],
		);

		//テスト実施
		$this->TestModel->save($data);

		//チェック
		$mailQueue = $this->MailQueue->find('all', array(
			'recursive' => -1,
			'conditions' => array('plugin_key' => Current::read('Plugin.key'))
		));
		$mailQueueUsers = $this->MailQueueUser->find('all', array(
			'recursive' => -1,
			'conditions' => array('plugin_key' => Current::read('Plugin.key'))
		));
		//debug($mailQueue);
		//debug($mailQueueUsers);

		// --- 件名には下記が含まれる
		$mailSubject = $mailQueue[0]['MailQueue']['mail_subject'];
		$siteName = (new SiteSettingForMailFixture())->records[4];
		// サイト名
		$this->assertTextContains($siteName['value'], $mailSubject);
		// プラグイン名
		$this->assertTextContains(Current::read('Plugin.name'), $mailSubject);
		// コンテンツのタイトル
		$this->assertTextContains($data['TestMailQueueBehaviorSaveModel']['title'], $mailSubject);
		// ルーム名
		//$this->assertTextContains('Room name', $mailSubject);
		// ブロック名
		$this->assertTextContains(Current::read('Block.name'), $mailSubject);
		// 埋め込みタグが、消えている事
		$this->assertTextNotContains('X-', $mailSubject);

		// --- 本文には下記が含まれる
		$mailBody = $mailQueue[0]['MailQueue']['mail_body'];
		$mailSignature = (new SiteSettingForMailFixture())->records[3];
		$mailBodyHeader = (new SiteSettingForMailFixture())->records[2];
		// 署名
		$this->assertTextContains($mailSignature['value'], $mailBody);
		// 本文ヘッダー文
		$this->assertTextContains($mailBodyHeader['value'], $mailBody);
		// コンテンツの本文
		$this->assertTextContains($data['TestMailQueueBehaviorSaveModel']['content'], $mailBody);

		// コンテンツのタイトル
		$this->assertTextContains($data['TestMailQueueBehaviorSaveModel']['title'], $mailBody);
		// ブロック名
		$this->assertTextContains(Current::read('Block.name'), $mailBody);
		// プラグイン名
		$this->assertTextContains(Current::read('Plugin.name'), $mailBody);
		// 埋め込みタグが、消えている事
		$this->assertTextNotContains('X-', $mailBody);

		foreach ($mailQueueUsers as $mailQueueUser) {
			if (isset($mailQueueUser['MailQueueUser']['user_id'])) {
				$this->assertEmpty($mailQueueUser['MailQueueUser']['room_id']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['to_address']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['send_room_permission']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['not_send_room_user_ids']);
			}
			if (isset($mailQueueUser['MailQueueUser']['to_address'])) {
				$this->assertEmpty($mailQueueUser['MailQueueUser']['room_id']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['user_id']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['send_room_permission']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['not_send_room_user_ids']);
			}
			if (isset($mailQueueUser['MailQueueUser']['room_id'])) {
				$this->assertEmpty($mailQueueUser['MailQueueUser']['to_address']);
				$this->assertEmpty($mailQueueUser['MailQueueUser']['user_id']);
				$this->assertNotEmpty($mailQueueUser['MailQueueUser']['send_room_permission']);
				$this->assertNotEmpty($mailQueueUser['MailQueueUser']['not_send_room_user_ids']);
			}
		}
	}

/**
 * save()のテスト - MailSettingFixedPhraseデータなしによる、メール送信しない設定で、メール送らない動作確認
 *
 * @return void
 */
	public function testSaveIsMailSendFail() {
		//テストデータ
		$data = array(
			// 一時保存データ
			'TestMailQueueBehaviorSaveModel' => (new TestMailQueueBehaviorSaveModelFixture())
				->records[0],
		);

		//テスト実施
		$this->TestModel->save($data);

		//チェック
		$this->MailQueue->find();
		$actual = $this->MailQueue->find('all', array(
			'recursive' => -1,
			'conditions' => array('plugin_key' => Current::read('Plugin.key'))
		));
		//debug($actual);
		$this->assertEmpty($actual);
	}

/**
 * save()の例外テスト - MailQueue::saveMailQueue() で Plugin.key=nullでvalidateエラーのため発生
 *
 * @return void
 */
	public function testSaveException() {
		$this->setExpectedException('InternalErrorException');

		$this->TestModel->setSetting('pluginKey', null);

		//テストデータ
		$data = array(
			'TestMailQueueBehaviorSaveModel' => (new TestMailQueueBehaviorSaveModelFixture())
				->records[1],
		);

		//テスト実施
		$this->TestModel->save($data);
	}
}