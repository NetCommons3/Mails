<?php
/**
 * MailQueueUser::addMailQueueUserInRoomByPermission()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * MailQueueUser::addMailQueueUserInRoomByPermission()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueueUser
 */
class MailQueueUserAddMailQueueUserInRoomByPermissionTest extends NetCommonsModelTestCase {

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
	protected $_methodName = 'addMailQueueUserInRoomByPermission';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		Current::$current['Plugin']['key'] = 'dummy';
		Current::$current['Block']['key'] = 'block_key999';
		Current::$current['Room']['id'] = 1;
	}

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$results = array();
		$results['登録'] = array(1, 'content_key', null, 'content_publishable', [1], 1);

		return $results;
	}

/**
 * Saveのテスト
 *
 * @param int $mailQueueId メールキューID
 * @param string $contentKey 各プラグイン側のコンテンツキー
 * @param string $pluginKey プラグインキー
 * @param string $permissionKey パーミッションキー
 * @param array $notSendUserIds 送らないユーザID
 * @param int $expectedCount 想定の登録件数
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($mailQueueId,
								$contentKey,
								$pluginKey = null,
								$permissionKey = 'content_publishable',
								$notSendUserIds = array(),
								$expectedCount = 0) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model->$method($mailQueueId, $contentKey, $pluginKey, $permissionKey,
			$notSendUserIds);

		$result = $this->$model->find('all', array(
			'recursive' => -1,
			'conditions' => array('block_key' => Current::read('Block.key')),
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

		$mailQueueId = $result[0];
		$contentKey = $result[1];
		$pluginKey = $result[2];
		$permissionKey = $result[3];
		$notSendUserIds = $result[4];

		return array(
			array('Mails.MailQueueUser', 'validates', $mailQueueId, $contentKey, $pluginKey,
				$permissionKey, $notSendUserIds),
		);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param int $mailQueueId メールキューID
 * @param string $contentKey 各プラグイン側のコンテンツキー
 * @param string $pluginKey プラグインキー
 * @param string $permissionKey パーミッションキー
 * @param array $notSendUserIds 送らないユーザID
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($mockModel,
											$mockMethod,
											$mailQueueId,
											$contentKey,
											$pluginKey = null,
											$permissionKey = 'content_publishable',
											$notSendUserIds = array()) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($mailQueueId, $contentKey, $pluginKey, $permissionKey,
			$notSendUserIds);
	}

}
