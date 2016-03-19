<?php
/**
 * Workflow ComponentのPermissionゲットのコピー
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Workflow ComponentのPermissionゲットのコピー
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 */
class NetCommonsPermission {

/**
 * Function to get the data of BlockRolePermmissions.
 *    e.g.) BlockRolePermmissions controller
 *
 * @param array $permissions permissions
 * @return array Role and Permissions data
 *   - The `Role` merged of Role and RoomRole
 *   - The `Permission` sets in priority of BlockRolePermission and RoomRolePermission and DefaultRolePermission.
 */
	public function getBlockRolePermissions($permissions) {
		//modelのロード
		$models = array(
			'BlockRolePermission' => 'Blocks.BlockRolePermission',
			'DefaultRolePermission' => 'Roles.DefaultRolePermission',
			'Role' => 'Roles.Role',
			'RolesRoom' => 'Rooms.RolesRoom',
			'RoomRole' => 'Rooms.RoomRole',
			'RoomRolePermission' => 'Rooms.RoomRolePermission',
		);
		foreach ($models as $model => $class) {
			$this->$model = ClassRegistry::init($class, true);
		}

		$blockKey = Current::read('Block.key');

		//RoomRolePermissions取得
		$results = $this->getRoomRolePermissions($permissions, DefaultRolePermission::TYPE_ROOM_ROLE);
		$defaultPermissions = Hash::remove($results['DefaultRolePermission'], '{s}.{s}.id');
		$roles = $results['Role'];
		$rolesRooms = $results['RolesRoom'];
		$roomRolePermissions = Hash::remove($results['RoomRolePermission'], '{s}.{s}.id');
		$roomRoles = $results['RoomRole'];

		//BlockRolePermission取得
		$blockPermissions = $this->BlockRolePermission->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'BlockRolePermission.roles_room_id' => $rolesRooms,
				'BlockRolePermission.block_key' => $blockKey,
				'BlockRolePermission.permission' => $permissions,
			),
		));
		$blockPermissions = Hash::combine(
			$blockPermissions,
			'{n}.RolesRoom.role_key',
			'{n}.BlockRolePermission',
			'{n}.BlockRolePermission.permission'
		);

		//戻り値の設定
		$results = array(
			'BlockRolePermissions' => Hash::merge($defaultPermissions, $roomRolePermissions, $blockPermissions),
			'Roles' => Hash::merge($roomRoles, $roles)
		);

		//block_keyのセット
		$results['BlockRolePermissions'] = Hash::insert($results['BlockRolePermissions'], '{s}.{s}.block_key', $blockKey);

		return $results;
	}

/**
 * Function to get the data of RoomRolePermmissions.
 *    e.g.) RoomRolePermmissions controller
 *
 * @param array $permissions パーミッションリスト
 * @param string $type タイプ(DefaultRolePermissions.type)
 * @param string $roomId ルームID
 * @return array Role and Permissions and Rooms data
 *   - The `DefaultPermissions` data.
 *   - The `Roles` data.
 *   - The `RolesRooms` data.
 *   - The `RoomRolePermissions` data.
 *   - The `RoomRoles` data.
 */
	public function getRoomRolePermissions($permissions, $type, $roomId = null) {
		//戻り値の設定
		$results = array(
			'DefaultRolePermission' => null,
			'Role' => null,
			'RolesRoom' => null,
			'RoomRolePermission' => null,
			'RoomRole' => null,
		);

		//modelのロード
		$models = array(
			'DefaultRolePermission' => 'Roles.DefaultRolePermission',
			'Role' => 'Roles.Role',
			'RolesRoom' => 'Rooms.RolesRoom',
			'RoomRole' => 'Rooms.RoomRole',
			'RoomRolePermission' => 'Rooms.RoomRolePermission',
		);
		foreach ($models as $model => $class) {
			$this->$model = ClassRegistry::init($class, true);
		}

		if (! $roomId) {
			$roomId = Current::read('Room.id');
		}

		//RoomRole取得
		$roomRoles = $this->RoomRole->find('all', array(
			'recursive' => -1,
		));
		$results['RoomRole'] = Hash::combine($roomRoles, '{n}.RoomRole.role_key', '{n}.RoomRole');

		//Role取得
		$roles = $this->Role->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Role.type' => Role::ROLE_TYPE_ROOM,
				'Role.language_id' => Current::read('Language.id'),
			),
		));
		$results['Role'] = Hash::combine($roles, '{n}.Role.key', '{n}.Role');

		//DefaultRolePermission取得
		$defaultPermissions = $this->DefaultRolePermission->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'DefaultRolePermission.type' => $type,
				'DefaultRolePermission.permission' => $permissions,
			),
		));
		$results['DefaultRolePermission'] = Hash::combine(
			$defaultPermissions,
			'{n}.DefaultRolePermission.role_key',
			'{n}.DefaultRolePermission',
			'{n}.DefaultRolePermission.permission'
		);

		if (! isset($roomId)) {
			return $results;
		}

		//RolesRoomのIDリストを取得
		$results['RolesRoom'] = $this->RolesRoom->find('list', array(
			'recursive' => -1,
			'conditions' => array(
				'RolesRoom.room_id' => $roomId,
			),
		));

		//RoomRolePermission取得
		$roomRolePermissions = $this->RoomRolePermission->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'RoomRolePermission.roles_room_id' => $results['RolesRoom'],
				'RoomRolePermission.permission' => $permissions,
			),
		));
		$results['RoomRolePermission'] = Hash::combine(
			$roomRolePermissions,
			'{n}.RolesRoom.role_key',
			'{n}.RoomRolePermission',
			'{n}.RoomRolePermission.permission'
		);
		//$results['RoomRolePermission'] = Hash::remove($roomRolePermissions, '{s}.{s}.id');

		//戻り値の設定
		return $results;
	}

/**
 * Function to get the data of BlockRolePermmissions.
 *    e.g.) BlockRolePermmissions controller
 *
 * @param array $permissions permissions
 * @return array Role and Permissions data
 *   - The `Role` merged of Role and RoomRole
 *   - The `Permission` sets in priority of BlockRolePermission and RoomRolePermission and DefaultRolePermission.
 */
	public function getRoomRolePermissions2($permissions) {
		//modelのロード
//		$models = array(
//			'BlockRolePermission' => 'Blocks.BlockRolePermission',
//			'DefaultRolePermission' => 'Roles.DefaultRolePermission',
//			'Role' => 'Roles.Role',
//			'RolesRoom' => 'Rooms.RolesRoom',
//			'RoomRole' => 'Rooms.RoomRole',
//			'RoomRolePermission' => 'Rooms.RoomRolePermission',
//		);
//		foreach ($models as $model => $class) {
//			$this->$model = ClassRegistry::init($class, true);
//		}

//		$blockKey = Current::read('Block.key');

		//RoomRolePermissions取得
		$results = $this->getRoomRolePermissions($permissions, DefaultRolePermission::TYPE_ROOM_ROLE);
		$defaultPermissions = Hash::remove($results['DefaultRolePermission'], '{s}.{s}.id');
//		$roles = $results['Role'];
//		$rolesRooms = $results['RolesRoom'];
		$roomRolePermissions = Hash::remove($results['RoomRolePermission'], '{s}.{s}.id');
//		$roomRoles = $results['RoomRole'];

//		//BlockRolePermission取得
//		$blockPermissions = $this->BlockRolePermission->find('all', array(
//			'recursive' => 0,
//			'conditions' => array(
//				'BlockRolePermission.roles_room_id' => $rolesRooms,
//				'BlockRolePermission.block_key' => $blockKey,
//				'BlockRolePermission.permission' => $permissions,
//			),
//		));
//		$blockPermissions = Hash::combine(
//			$blockPermissions,
//			'{n}.RolesRoom.role_key',
//			'{n}.BlockRolePermission',
//			'{n}.BlockRolePermission.permission'
//		);

		//戻り値の設定
		$results = array(
			'RoomRolePermissions' => Hash::merge($defaultPermissions, $roomRolePermissions),
//			'Roles' => Hash::merge($roomRoles, $roles)
		);

		//block_keyのセット
//		$results['BlockRolePermissions'] = Hash::insert($results['BlockRolePermissions'], '{s}.{s}.block_key', $blockKey);

		return $results;
	}
}
