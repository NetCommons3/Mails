<?php
/**
 * RoomRolePermissionForMailFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('RoomRolePermission4testFixture', 'Rooms.Test/Fixture');

/**
 * RoomRolePermissionForMailFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Rooms\Test\Fixture
 */
class RoomRolePermissionForMailFixture extends RoomRolePermission4testFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'RoomRolePermission';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'room_role_permissions';

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		parent::init();

		$this->records = array_merge($this->records, array(
			////パブリックスペース
			////--ルーム管理者
			array('roles_room_id' => '1', 'permission' => 'content_editable', 'value' => '1'),
			////--編集長
			array('roles_room_id' => '2', 'permission' => 'content_editable', 'value' => '1'),
			////--編集者
			array('roles_room_id' => '3', 'permission' => 'content_editable', 'value' => '1'),
			////--一般
			array('roles_room_id' => '4', 'permission' => 'content_editable', 'value' => '0'),
			////--ゲスト
			array('roles_room_id' => '5', 'permission' => 'content_editable', 'value' => '0'),
			//パブリックスペース、別ルーム(room_id=4)
			//--ルーム管理者
			array('roles_room_id' => '6', 'permission' => 'content_editable', 'value' => '1'),
		));
	}
}
