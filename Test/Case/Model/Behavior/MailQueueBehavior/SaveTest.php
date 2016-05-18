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
App::uses('NetCommonsTime', 'NetCommons.Utility');

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
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.rooms.room_role_permission4test',
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
		Current::write('Plugin.key', 'dummy');
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
 * @param string $pluginKey プラグインキー
 * @return void
 */
	public function testSaveSendRoom($pluginKey = null) {
		if (is_null($pluginKey)) {
			$pluginKey = Current::read('Plugin.key');
		}

		//テストデータ
		$data = array(
			'TestMailQueueBehaviorSaveModel' => (new TestMailQueueBehaviorSaveModelFixture())
				->records[1],
		);

		//テスト実施
		$this->TestModel->save($data, false);

		//チェック
		$mailQueue = $this->MailQueue->find('all', array(
			'recursive' => -1,
			'conditions' => array('plugin_key' => $pluginKey)
		));
		$mailQueueUsers = $this->MailQueueUser->find('all', array(
			'recursive' => -1,
			'conditions' => array('plugin_key' => $pluginKey)
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
				//$this->assertNotEmpty($mailQueueUser['MailQueueUser']['not_send_room_user_ids']);
			}
		}
	}

/**
 * save()のテスト - 承認機能なしで配信
 *
 * @return void
 */
	public function testSaveSendNoneWorkflow() {
		//準備
		/** @see MailQueueBehavior::setSetting() */
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE,
			MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_NONE);

		//テスト実施
		$this->testSaveSendRoom();
	}

/**
 * save()のテスト - リマインダーを配信
 *
 * @return void
 */
	public function testSaveReminder() {
		// セット
		$netCommonsTime = new NetCommonsTime();
		$sendTimeReminders = array(
			$netCommonsTime->toServerDatetime('2027-03-31 14:30:00'),
			$netCommonsTime->toServerDatetime('2027-04-20 13:30:00'),
		);
		/** @see MailQueueBehavior::setSendTimeReminder() */
		$this->TestModel->setSendTimeReminder($sendTimeReminders);

		//テスト実施
		$this->testSaveSendRoom();

		// チェック
		$results = $this->MailQueue->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'plugin_key' => Current::read('Plugin.key'),
				'send_time' => $sendTimeReminders,
			)
		));
		//debug($results);
		$this->assertCount(2, $results);
	}

/**
 * save()のテスト - 回答配信
 *
 * @return void
 */
	public function testSaveSendAnswer() {
		//準備
		/** @see MailQueueBehavior::setSetting() */
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE,
			MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER);

		// メールアドレス セット
		$toAddresses = array(
			'test1@example.com',
			'test2@example.com',
		);
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_TO_ADDRESSES, $toAddresses);

		//テスト実施
		$this->testSaveSendRoom();

		// チェック
		$results = $this->MailQueueUser->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'plugin_key' => Current::read('Plugin.key'),
				'to_address' => $toAddresses,
			)
		));
		//debug($results);
		$this->assertCount(2, $results);
	}

/**
 * save()のテスト - グループ配信
 *
 * @return void
 */
	public function testSaveSendGroup() {
		//準備
		/** @see MailQueueBehavior::setSetting() */
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE,
			MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY);

		// ユーザID セット
		$userIds = array(
			4,
			5,
		);
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS, $userIds);

		//テスト実施
		$this->testSaveSendRoom();

		// チェック
		$results = $this->MailQueueUser->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'plugin_key' => Current::read('Plugin.key'),
				'user_id' => $userIds,
			)
		));
		//debug($results);
		$this->assertCount(2, $results);
	}

/**
 * save()のテスト - コンテンツコメント配信
 *
 * @return void
 */
	public function testSaveSendComment() {
		//準備
		$pluginKey = 'content_comments';

		/** @see MailQueueBehavior::setSetting() */
		//$this->TestModel->setSetting('useCommentApproval', '_mail.use_comment_approval');
		//$this->TestModel->setSetting('isCommentApproveAction', '_mail.is_comment_approve_action');
		$this->TestModel->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_WORKFLOW_TYPE,
			MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT);
		$this->TestModel->setSetting('keyField', 'key');
		$this->TestModel->setSetting('pluginKey', $pluginKey);
		$this->TestModel->setSetting('publishablePermissionKey', 'content_comment_publishable');

		//テスト実施
		$this->testSaveSendRoom($pluginKey);
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
		$this->TestModel->save($data, false);

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
		$this->TestModel->save($data, false);
	}
}
