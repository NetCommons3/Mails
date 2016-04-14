<?php
/**
 * MailQueueUser Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailsAppModel', 'Mails.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');
App::uses('DefaultRolePermission', 'Roles.Model');

/**
 * メールキュー送信先
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailQueueUser extends MailsAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * beforeValidate
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'plugin_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			//'block_key' => array(
			//	'notBlank' => array(
			//		'rule' => array('notBlank'),
			//		'message' => __d('net_commons', 'Invalid request.'),
			//		'required' => true,
			//	),
			//),
			'mail_queue_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'user_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => true,
				),
			),
			'room_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => true,
				),
			),
			'to_address' => array(
				'email' => array(
					'rule' => array('email'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'MailQueue' => array(
			'className' => 'MailQueue',
			'foreignKey' => 'mail_queue_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Room' => array(
			'className' => 'Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * キューの配信先データ登録(複数)
 *
 * @param array $mailQueueUser received post data
 * @param string $filed 一部だけ変更するフィールド
 * @param array $values 一部だけ変更するフィールドの値(配列)
 * @return void
 * @throws InternalErrorException
 */
	public function addMailQueueUsers($mailQueueUser, $filed, $values) {
		foreach ($values as $value) {
			$mailQueueUser['MailQueueUser'][$filed] = $value;
			$mailQueueUser = $this->create($mailQueueUser);
			if (! self::saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
	}

/**
 * キューの配信先データ保存 - （誰に）
 *
 * セットするパターンが３つ。いずれかをセットする
 * ・room_id + ロール（block_role_permission）　：　複数人パターン
 * ・user_id 　　：　個別パターン1。パスワード再発行等 (NCにいる人イメージ)
 * ・to_address　：　個別パターン2。メールアドレスのみで通知する (NCにいない人イメージ)
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailQueueUser($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			$this->rollback();
			return false;
		}

		try {
			// 保存
			if (! $mailQueueUser = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailQueueUser;
	}

/**
 * キューの配信先データ削除
 *
 * @param int $id ID
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function deleteMailQueueUser($id) {
		if (empty($id)) {
			return false;
		}

		//トランザクションBegin
		$this->begin();

		try {
			if (! $this->delete($id, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return true;
	}

/**
 * ルーム内で該当パーミッションありのユーザ ゲット
 * - MailQueueUser に 複数saveするために必要
 *
 * @param string $permission パーミッション
 * @param string $roomId ルームID
 * @return array
 */
	public function getRolesRoomsUsersByPermission($permission, $roomId = null) {
		$this->loadModels(array(
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
		));

		if ($roomId === null) {
			$roomId = Current::read('Room.id');
		}

		$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
		//$permissions = $WorkflowComponent->getBlockRolePermissions(array($permission));
		$permissions = $WorkflowComponent->getRoomRolePermissions(array($permission), DefaultRolePermission::TYPE_ROOM_ROLE);
		foreach ($permissions['RoomRolePermission'][$permission] as $key => $roomRolePermission) {
			if (!$roomRolePermission['value']) {
				unset($permissions['RoomRolePermission'][$permission][$key]);
			}
		}

		//$roleKeys = array_keys($permissions['BlockRolePermissions'][$permission]);
		$roleKeys = array_keys($permissions['RoomRolePermission'][$permission]);
		$conditions = array(
			'Room.id' => $roomId,
			'RolesRoom.role_key' => $roleKeys,
		);
		$rolesRoomsUsers = $this->RolesRoomsUser->getRolesRoomsUsers($conditions);
		return $rolesRoomsUsers;
	}
}
