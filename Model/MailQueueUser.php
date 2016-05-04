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
			// 新規登録
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
 * ルーム内で該当パーミッションありのユーザ ゲット
 * - MailQueueUser に 承認者達をsaveするために必要
 *
 * @param string $permissionKey パーミッションキー
 * @param string $roomId ルームID
 * @return array
 */
	public function getRolesRoomsUsersByPermission($permissionKey, $roomId = null) {
		$this->loadModels(array(
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
		));

		if ($roomId === null) {
			$roomId = Current::read('Room.id');
		}

		$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
		//$permissions = $WorkflowComponent->getBlockRolePermissions(array($permission));
		$permissions = $WorkflowComponent->getRoomRolePermissions(array($permissionKey),
																DefaultRolePermission::TYPE_ROOM_ROLE);
		foreach ($permissions['RoomRolePermission'][$permissionKey] as $key => $roomRolePermission) {
			if (!$roomRolePermission['value']) {
				unset($permissions['RoomRolePermission'][$permissionKey][$key]);
			}
		}

		//$roleKeys = array_keys($permissions['BlockRolePermissions'][$permission]);
		$roleKeys = array_keys($permissions['RoomRolePermission'][$permissionKey]);
		$conditions = array(
			'Room.id' => $roomId,
			'RolesRoom.role_key' => $roleKeys,
		);
		$rolesRoomsUsers = $this->RolesRoomsUser->getRolesRoomsUsers($conditions);
		return $rolesRoomsUsers;
	}

/**
 * 登録者に配信 登録
 *
 * @param int $mailQueueId メールキューID
 * @param Model $createdUserId 登録ユーザID
 * @param string $contentKey 各プラグイン側のコンテンツのキー
 * @param string $pluginKey プラグインキー
 * @return void
 * @throws InternalErrorException
 */
	public function addMailQueueUserInCreatedUser($mailQueueId, $createdUserId, $contentKey,
													$pluginKey = null) {
		if (empty($createdUserId)) {
			return;
		}
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}
		$blockKey = Current::read('Block.key');

		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'mail_queue_id' => $mailQueueId,
			'user_id' => $createdUserId,
			'room_id' => null,
			'to_address' => null,
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
		);

		// 新規登録
		$mailQueueUser = $this->create($mailQueueUser);
		if (! self::saveMailQueueUser($mailQueueUser)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * ルーム内の承認者達に配信 登録
 *
 * @param int $mailQueueId メールキューID
 * @param string $contentKey 各プラグイン側のコンテンツキー
 * @param string $pluginKey プラグインキー
 * @param string $permissionKey パーミッションキー
 * @return array ユーザID
 * @throws InternalErrorException
 */
	public function addMailQueueUserInRoomAuthorizers($mailQueueId, $contentKey, $pluginKey = null,
														$permissionKey = 'content_publishable') {
		if ($pluginKey === null) {
			$pluginKey = Current::read('Plugin.key');
		}
		$blockKey = Current::read('Block.key');

		$mailQueueUser['MailQueueUser'] = array(
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'mail_queue_id' => $mailQueueId,
			'user_id' => null,
			'room_id' => null,
			'to_address' => null,
			'send_room_permission' => null,
			'not_send_room_user_ids' => null,
		);

		$notSendRoomUserIds = array();

		// 送信者データ取得
		$rolesRoomsUsers = self::getRolesRoomsUsersByPermission($permissionKey);
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$mailQueueUser['MailQueueUser']['user_id'] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
			// 新規登録
			$mailQueueUser = $this->create($mailQueueUser);
			if (! self::saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
			// ルーム配信で送らないユーザID を返す
			$notSendRoomUserIds[] = $rolesRoomsUser['RolesRoomsUser']['user_id'];
		}

		return $notSendRoomUserIds;
	}

/**
 * ルーム配信で、キューの配信先データ登録
 *
 * @param int $roomId ルームID
 * @param array $mailQueueUser received post data
 * @param string $sendTime メール送信日時
 * @param array $notSendRoomUserIds ルーム配信で送らないユーザID
 * @param array $addUserIds 追加のユーザ達
 * @param string $sendRoomPermission ルーム配信で送るパーミッション
 * @return void
 * @throws InternalErrorException
 */
	public function addMailQueueUserInRoom($roomId, $mailQueueUser, $sendTime, $notSendRoomUserIds,
											$addUserIds, $sendRoomPermission = 'mail_content_receivable') {
		// --- ルーム配信
		//$roomId = Current::read('Room.id');
		$mailQueueUser['MailQueueUser']['room_id'] = $roomId;
		$mailQueueUser['MailQueueUser']['send_room_permission'] = $sendRoomPermission;

		// ルーム配信で送らないユーザID
		$notSendRoomUserIds = $this->__getNotSendRoomUserIds($sendTime, $notSendRoomUserIds);
		$mailQueueUser['MailQueueUser']['not_send_room_user_ids'] = $notSendRoomUserIds;

		$mailQueueUser = $this->create($mailQueueUser);
		/** @see MailQueueUser::saveMailQueueUser() */
		if (! $this->saveMailQueueUser($mailQueueUser)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// --- 追加のユーザ達に配信
		// ルームIDをクリア
		$mailQueueUser['MailQueueUser']['room_id'] = null;

		// 追加のユーザ達
		$addUserIds = $this->__getAddUserIds($addUserIds, $notSendRoomUserIds);

		$this->addMailQueueUsers($mailQueueUser, 'user_id', $addUserIds);
	}

/**
 * ルーム配信で送らないユーザID ゲット
 *
 * @param string $sendTime メール送信日時
 * @param array $notSendRoomUserIds ルーム配信で送らないユーザID
 * @return string ルーム配信で送らないユーザID
 */
	private function __getNotSendRoomUserIds($sendTime, $notSendRoomUserIds) {
		// 未来日送信は2通（承認完了とルーム配信）送るため、送らないユーザIDをセットしない
		$now = NetCommonsTime::getNowDatetime();
		if ($sendTime > $now) {
			return null;
		}

		// 重複登録を排除
		$notSendRoomUserIds = array_unique($notSendRoomUserIds);
		// 空要素を排除
		$notSendRoomUserIds = Hash::filter($notSendRoomUserIds);
		$notSendRoomUserIds = implode('|', $notSendRoomUserIds);

		return $notSendRoomUserIds;
	}

/**
 * 追加で配信するのユーザID ゲット
 *
 * @param array $addUserIds 追加で配信するのユーザID
 * @param array $notSendUserIds 送らないユーザID
 * @return string 追加で配信するのユーザID
 */
	private function __getAddUserIds($addUserIds, $notSendUserIds) {
		// 登録者と追加ユーザ達の重複登録を排除
		$addUserIds = array_unique($addUserIds);
		// 空要素を排除
		$addUserIds = Hash::filter($addUserIds);

		if ($notSendUserIds === null) {
			return $addUserIds;
		}

		// 送らないユーザIDを排除
		$notSendUserIds = explode('|', $notSendUserIds);
		foreach ($notSendUserIds as $notSendRoomUserId) {
			if (($key = array_search($notSendRoomUserId, $addUserIds)) !== false) {
				unset($addUserIds[$key]);
			}
		}

		return $addUserIds;
	}
}
