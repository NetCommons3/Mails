<?php
/**
 * MailQueueUser::addMailQueueUserInRoom()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsTime', 'NetCommons.Utility');

/**
 * MailQueueUser::addMailQueueUserInRoom()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueueUser
 */
class MailQueueUserAddMailQueueUserInRoomTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'MailQueueUser';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'addMailQueueUserInRoom';

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$contentKey = 'content_key';
		$pluginKey = 'dummy';
		$blockKey = 'block_key999';

		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
			'mail_queue_id' => 1,
		);
		$sendTime = NetCommonsTime::getNowDatetime();

		$results = array();
		$results['登録'] = array(1, $mailQueueUser, $sendTime, [1], 'mail_content_receivable', 1);

		return $results;
	}

/**
 * Saveのテスト
 *
 * @param int $roomId ルームID
 * @param array $mailQueueUser received post data
 * @param string $sendTime メール送信日時
 * @param array $notSendRoomUserIds ルーム配信で送らないユーザID
 * @param string $sendRoomPermission ルーム配信で送るパーミッション
 * @param int $expectedCount 想定の登録件数
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($roomId, $mailQueueUser, $sendTime, $notSendRoomUserIds,
								$sendRoomPermission = 'mail_content_receivable', $expectedCount = 0) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model->$method($roomId, $mailQueueUser, $sendTime, $notSendRoomUserIds,
			$sendRoomPermission);

		$result = $this->$model->find('all', array(
			'recursive' => -1,
			'conditions' => array('block_key' => $mailQueueUser['MailQueueUser']['block_key']),
		));

		//debug($result);
		$this->assertCount($expectedCount, $result);
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
		$result = $this->dataProviderSave()['登録'];

		$roomId = $result[0];
		$mailQueueUser = $result[1];
		$sendTime = $result[2];
		$notSendRoomUserIds = $result[3];
		$sendRoomPermission = $result[4];

		return array(
			array('Mails.MailQueueUser', 'validates', $roomId, $mailQueueUser, $sendTime,
				$notSendRoomUserIds, $sendRoomPermission),
		);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param int $roomId ルームID
 * @param array $mailQueueUser received post data
 * @param string $sendTime メール送信日時
 * @param array $notSendRoomUserIds ルーム配信で送らないユーザID
 * @param string $sendRoomPermission ルーム配信で送るパーミッション
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($mockModel,
											$mockMethod,
											$roomId, $mailQueueUser, $sendTime, $notSendRoomUserIds,
											$sendRoomPermission = 'mail_content_receivable') {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($roomId, $mailQueueUser, $sendTime, $notSendRoomUserIds,
			$sendRoomPermission);
	}

}
