<?php
/**
 * IsMailSendBehavior::isSendMailQueueNotice()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * IsMailSendBehavior::isSendMailQueueNotice()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\IsMailSendBehavior
 */
class IsMailSendBehaviorIsSendMailQueueNoticeTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.blocks.block_setting',
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.mails.room_role_permission_for_mail',
		'plugin.mails.block_setting_for_mail',
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
		/** @see TestIsMailSendBehaviorModel */
		$this->TestModel = ClassRegistry::init('TestMails.TestIsMailSendBehaviorModel');
	}

/**
 * isSendMailQueueNotice()テストのDataProvider
 *
 * ### 戻り値
 *  - isMailSendApproval 承認メール通知機能を使うフラグ
 *  - createdUserId 更新ユーザID
 *  - expected テスト結果の想定
 *
 * @return array データ
 */
	public function dataProvider() {
		$result = array(
			'true:正常ケース' => array(
				'isMailSendApproval' => 1,
				'isContentComment' => 0,
				'modifiedUserId' => 5,
				'data' => array(
					'Block' => array(
						'key' => 'block_1'
					),
				),
				'expected' => true,
			),
			'true:承認コメントあり' => array(
				'isMailSendApproval' => 1,
				'isContentComment' => 0,
				'modifiedUserId' => 5,
				'data' => array(
					'WorkflowComment' => array(
						'comment' => '承認コメントあり'
					),
					'Block' => array(
						'key' => 'block_1'
					),
				),
				'expected' => true,
			),
			'true:コンテンツコメントのケース' => array(
				'isMailSendApproval' => 1,
				'isContentComment' => 1,
				'modifiedUserId' => 5,
				'data' => array(
					'Block' => array(
						'key' => 'block_1'
					),
				),
				'expected' => true,
			),
			'false:承認しない(BlockSettingデータなしでも)場合、通知メール送らない' => array(
				'isMailSendApproval' => 0,
				'isContentComment' => 0,
				'modifiedUserId' => null,
				'data' => array(
					'Block' => array(
						'key' => null	// BlockSettingデータなし条件
					),
				),
				'expected' => false,
			),
			'false:承認メール使わないなら、通知メール送らない' => array(
				'isMailSendApproval' => 0,
				'isContentComment' => 0,
				'modifiedUserId' => null,
				'data' => array(
					'Block' => array(
						'key' => 'block_1'
					),
				),
				'expected' => false,
			),
			'false:投稿者がルーム内の承認者だったら、通知メール送らない' => array(
				'isMailSendApproval' => 1,
				'isContentComment' => 0,
				'modifiedUserId' => 1,
				'data' => array(
					'Block' => array(
						'key' => 'block_1'
					),
				),
				'expected' => false,
			),
		);

		return $result;
	}

/**
 * isSendMailQueueNotice()のテスト
 *
 * @param int $isMailSendApproval 承認メール通知機能を使うフラグ
 * @param int $isContentComment 承認タイプはコンテンツコメントだフラグ
 * @param int $modifiedUserId 更新ユーザID
 * @param array $data modelデータ
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testIsSendMailQueueNotice($isMailSendApproval,
												$isContentComment,
												$modifiedUserId,
												$data,
												$expected) {
		$roomId = '2';
		Current::write('Room.id', $roomId);
		$pluginKey = 'dummy';
		$blockKey = Hash::get($data, 'Block.key');
		Current::write('Block.key', $blockKey);
		$this->TestModel->data = $data;
		if ($isContentComment) {
			// コンテンツコメントの時
			$this->TestModel->Behaviors->load('Mails.IsMailSend', array(
				'workflowType' => 'contentComment',
				'publishablePermissionKey' => 'content_publishable',
				'keyField' => 'key',
				'reminder' => array(
					'sendTimes' => null,
					'useReminder' => 0,
				),
			));
		}

		//テスト実施
		/** @see IsMailSendBehavior::isSendMailQueueNotice() */
		$result = $this->TestModel->isSendMailQueueNotice($isMailSendApproval, $modifiedUserId, $pluginKey);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}

}
