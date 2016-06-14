<?php
/**
 * IsMailSendBehavior::isMailSend()のテスト
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
App::uses('TestIsMailSendBehaviorModelFixture', 'Mails.Test/Fixture');

/**
 * IsMailSendBehavior::isMailSend()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorisMailSendTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.mails.room_role_permission_for_mail',
		'plugin.mails.site_setting_for_mail',
		'plugin.mails.test_is_mail_send_behavior_model',
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
		$this->TestModel = ClassRegistry::init('TestMails.TestIsMailSendBehaviorModel');
	}

/**
 * isMailSend()テストのDataProvider
 *
 * ### 戻り値
 *  - isMailSendApproval 承認メール通知機能を使うフラグ
 *  - createdUserId 登録ユーザID
 *  - expected テスト結果の想定
 *
 * @return array データ
 */
	public function dataProvider() {
		$data['TestIsMailSendBehaviorModel'] =
			(new TestIsMailSendBehaviorModelFixture())->records[1];

		return array(
			'true:正常ケース' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => null,
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'permission' => null,
				'data' => array(),
				'expected' => true,
			),
			'false:isMailSendCommon' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => null,
				'settingPluginKey' => null,
				'settings' => null,
				'permission' => null,
				'data' => array(),
				'expected' => false,
			),
			'false:投稿メールOFFなら、メール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => null,
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'isMailSendPost' => '0',
				),
				'permission' => null,
				'data' => array(),
				'expected' => false,
			),
			// 公開許可
			'false:公開許可ありで編集の場合、メール送らない' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'publish_key',
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'permission' => 'content_publishable',
				'data' => array(),
				'expected' => false,
			),
			'true:公開許可あり、新規登録' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'xxx',
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'permission' => 'content_publishable',
				'data' => array(),
				'expected' => true,
			),
			'true:公開許可あり、公開以外の編集' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'content_4',
				'settingPluginKey' => 'dummy',
				'settings' => null,
				'permission' => 'content_publishable',
				'data' => array(),
				'expected' => true,
			),
			// コメント公開許可
			'true:コメント - 公開許可なし' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'publish_key',
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'workflowType' => MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
				),
				'permission' => null,
				'data' => array(),
				'expected' => true,
			),
			'true:コメント - 公開許可ありで承認時' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'publish_key',
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'workflowType' => MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
					'isCommentApproveAction' => '1',
				),
				'permission' => 'content_comment_publishable',
				'data' => array(),
				'expected' => true,
			),
			'false:コメント - 公開許可ありで編集' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'publish_key',
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'workflowType' => MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
				),
				'permission' => 'content_comment_publishable',
				'data' => array(),
				'expected' => false,
			),
			'true:コメント - 公開許可ありで新規登録' => array(
				'typeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'contentKey' => 'publish_key',
				'settingPluginKey' => 'dummy',
				'settings' => array(
					'workflowType' => MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
				),
				'permission' => 'content_comment_publishable',
				'data' => $data,
				'expected' => true,
			),
		);
	}

/**
 * isMailSend()のテスト
 *
 * @param string $typeKey メールの種類
 * @param string $contentKey コンテンツキー
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @param array $settings ビヘイビアsetting
 * @param string $permission パーミッション
 * @param array $data modelデータ
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testIsSendMailQueueNotice($typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
												$contentKey = null,
												$settingPluginKey = null,
												$settings = array(),
												$permission = null,
												$data = array(),
												$expected = null) {
		if (isset($permission)) {
			Current::write('Permission.' . $permission . '.value', 1);
		}

		if (isset($settings)) {
			// ビヘイビアのsettingに isMailSendPost を設定
			$this->TestModel->Behaviors->unload('Mails.IsMailSend');
			$this->TestModel->Behaviors->load('Mails.IsMailSend', $settings);
		}
		$this->TestModel->data = $data;

		//テスト実施
		/** @see IsMailSendBehavior::isMailSend() */
		$result = $this->TestModel->isMailSend($typeKey, $contentKey, $settingPluginKey);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}

}
