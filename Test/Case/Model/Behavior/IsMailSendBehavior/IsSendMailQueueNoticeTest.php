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
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		'plugin.mails.room_role_permission_for_mail',
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
 * isSendMailQueueNotice()テストのDataProvider
 *
 * ### 戻り値
 *  - isMailSendApproval 承認メール通知機能を使うフラグ
 *  - createdUserId 登録ユーザID
 *  - expected テスト結果の想定
 *
 * @return array データ
 */
	public function dataProvider() {
		$result = array(
			'true:正常ケース' => array(
				'isMailSendApproval' => 1,
				'createdUserId' => 5,
				'expected' => true,
			),
			'false:承認メール使わないなら、通知メール送らない' => array(
				'isMailSendApproval' => 0,
				'createdUserId' => null,
				'expected' => false,
			),
			'false:投稿者がルーム内の承認者だったら、通知メール送らない' => array(
				'isMailSendApproval' => 1,
				'createdUserId' => 1,
				'expected' => false,
			),
		);

		return $result;
	}

/**
 * isSendMailQueueNotice()のテスト
 *
 * @param strig $isMailSendApproval 承認メール通知機能を使うフラグ
 * @param int $createdUserId 登録ユーザID
 * @param bool $expected テスト結果の想定
 * @dataProvider dataProvider
 * @return void
 */
	public function testIsSendMailQueueNotice($isMailSendApproval, $createdUserId, $expected) {
		$roomId = 1;
		Current::write('Room.id', $roomId);

		//テスト実施
		$result = $this->TestModel->isSendMailQueueNotice($isMailSendApproval, $createdUserId);

		//チェック
		//debug($result);
		$this->assertEquals($expected, $result);
	}

}
