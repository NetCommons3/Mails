<?php
/**
 * MailQueueUser::getRolesRoomsUsersByPermission()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');

/**
 * MailQueueUser::getRolesRoomsUsersByPermission()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueueUser
 */
class MailQueueUserGetRolesRoomsUsersByPermissionTest extends NetCommonsGetTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		//'plugin.rooms.room_role_permission4test',
		'plugin.mails.room_role_permission_for_mail',
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
	protected $_methodName = 'getRolesRoomsUsersByPermission';

/**
 * getRolesRoomsUsersByPermission()のテスト
 *
 * @return void
 */
	public function testGetRolesRoomsUsersByPermission() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$permissionKey = 'content_publishable';
		$roomId = '2';

		//テスト実施
		$result = $this->$model->$methodName($permissionKey, $roomId);

		//チェック
		//debug($result);
		$this->assertNotEmpty($result);
	}

/**
 * getRolesRoomsUsersByPermission()のテスト - methodの引数にルームIDをセットしない
 *
 * @return void
 */
	public function testGetRolesRoomsUsersByPermissionNotSetRoomId() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$permissionKey = 'content_publishable';
		Current::write('Room.id', '2');

		//テスト実施
		$result = $this->$model->$methodName($permissionKey);

		//チェック
		//debug($result);
		$this->assertNotEmpty($result);
	}
}
