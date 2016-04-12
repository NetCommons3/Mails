<?php
/**
 * メール送信 Shell
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMail', 'Mails.Utility');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');

/**
 * メール送信 Shell
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Console\Command
 * @property MailQueue $MailQueue
 * @property MailQueueUser $MailQueueUser
 * @property Language $Language
 * @property SiteSetting $SiteSetting
 * @property RolesRoomsUser $RolesRoomsUser
 */
class MailSendShell extends AppShell {

/**
 * use model
 *
 * @var array
 * @link http://book.cakephp.org/2.0/ja/console-and-shells.html#Shell::$uses
 */
	public $uses = array(
		'Mails.MailQueue',
		'Mails.MailQueueUser',
		'M17n.Language',
		'SiteManager.SiteSetting',
		'Rooms.RolesRoomsUser',
	);

/**
 * Cronからメール送信 - Cronからコールするのはこちらを使ってください
 * ### コマンド
 * cake Mails.mailSend
 *
 * @return void
 * @link http://book.cakephp.org/2.0/ja/console-and-shells.html#id2
 */
	public function main() {
		// 初回のみ、システム管理の「クーロンを使いますフラグ」をONにする対応
		$siteSetting = $this->SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array(
				'Mail.use_cron',
			)
		));
		$useCron = Hash::get($siteSetting['Mail.use_cron'], '0.value');
		if (! $useCron) {
			$this->SiteSetting->id = $siteSetting['Mail.use_cron'][0]['id'];
			$this->SiteSetting->saveField('value', 1);
		}

		// メール送信
		$this->send();
	}

/**
 * メール送信
 *
 * @return void
 */
	public function send() {
		$now = NetCommonsTime::getNowDatetime();

		// キュー取得
		/** @link http://www.cpa-lab.com/tech/081 */
		$mailQueues = $this->MailQueue->find('all', array(
			'recursive' => 1,
			'conditions' => array(
				'MailQueue.send_time <=' => $now,
			)
		));
		if (empty($mailQueues)) {
			//CakeLog::debug("MailQueue is empty. [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
		}

		// SiteSettingからメール設定を取得する
		$siteSetting = $this->SiteSetting->getSiteSettingForEdit(array(
			'SiteSetting.key' => array(
				'Mail.from',
				'Mail.from_name',
				'Mail.messageType',
				'Mail.transport',
				'Mail.smtp.host',
				'Mail.smtp.port',
				'Mail.smtp.user',
				'Mail.smtp.pass',
				'App.site_name',
			)
		));
		$from = Hash::get($siteSetting['Mail.from'], '0.value');

		// Fromが空ならメール未設定のため、メール送らない
		if (empty($from)) {
			LogError('From Address is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
		}

		// ルーム配信時、同じメールは１通だけ送るようにする
		$mailQueues = $this->__deleteDuplicateMailForRoom($mailQueues);

		foreach ($mailQueues as $mailQueue) {
			foreach ($mailQueue['MailQueueUser'] as $mailQueueUser) {
				$mail = new NetCommonsMail();
				$mail->initShell($siteSetting, $mailQueue);

				//送信しない（デバッグ用）
				//				$config = $mail->config();
				//				$config['transport'] = 'Debug';
				//				$mail->config($config);
				//				$messages = $mail->sendQueueMail($mailQueueUser, $mailQueue['MailQueue']['language_id']);
				//				CakeLog::debug(print_r($messages, true));

				$mail->sendQueueMail($mailQueueUser, $mailQueue['MailQueue']['language_id']);

				// 送信後にキュー削除
				$this->MailQueueUser->deleteMailQueueUser($mailQueueUser['id']);
			}
			$this->MailQueue->deleteMailQueue($mailQueue['MailQueue']['id']);
		}
	}

/**
 * ルーム配信時、同じメールは１通だけ送るようにする
 *
 * @param array $mailQueues メールキュー
 * @return array メールキュー
 */
	private function __deleteDuplicateMailForRoom($mailQueues) {
		foreach ($mailQueues as &$mailQueue) {
			$roomIds = Hash::extract($mailQueue['MailQueueUser'], '{n}.room_id');
			$roomIds = array_filter($roomIds);
			$userIds = Hash::extract($mailQueue['MailQueueUser'], '{n}.user_id');
			$userIds = array_filter($userIds);

			// ルーム配信時、同じメールは１通だけ送るようにする
			// 複数件でルームIDあり
			// ルームIDあり & ユーザIDあり
			if (!empty($roomIds) && !empty($userIds)) {
				$key = key($roomIds);
				$roomId = $roomIds[$key];
				$blockKey = $mailQueue['MailQueueUser'][$key]['block_key'];

				// --- ルーム単位でメールするユーザID達
				$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
				$permissions = $WorkflowComponent->getBlockRolePermissions(array('mail_content_receivable'), $roomId, $blockKey);

				$roleKeys = array_keys($permissions['BlockRolePermissions']['mail_content_receivable']);
				$conditions = array(
					'Room.id' => $roomId,
					'RolesRoom.role_key' => $roleKeys,
				);
				$rolesRoomsUsers = $this->RolesRoomsUser->getRolesRoomsUsers($conditions);
				$rolesRoomsUserIds = Hash::extract($rolesRoomsUsers, '{n}.RolesRoomsUser.roles_room_id');

				foreach ($userIds as $key => $userId) {
					// ルーム配信に含まれる
					if (in_array($userId, $rolesRoomsUserIds)) {
						// ルーム配信とユーザID重複のため、キューユーザ削除
						$this->MailQueueUser->deleteMailQueueUser($mailQueue['MailQueueUser'][$key]['id']);

						unset($mailQueue['MailQueueUser'][$key]);
					}
				}
			}
		}
		return $mailQueues;
	}
}
