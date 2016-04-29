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
 * ```
 * cake Mails.mailSend
 * ```
 *
 * @return void
 * @link http://book.cakephp.org/2.0/ja/console-and-shells/cron-jobs.html
 * @link http://book.cakephp.org/2.0/ja/console-and-shells.html#id2
 */
	public function main() {
		// 初回のみ、システム管理の「クーロンを使いますフラグ」をONにする対応
		$useCron = SiteSettingUtil::read('Mail.use_cron', false);
		if (! $useCron) {
			$this->SiteSetting->saveSiteSettingByKey('Mail.use_cron', 1);
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

		// キュー取得 - 行ロック
		// http://k-1blog.com/development/program/post-7407/
		// http://d.hatena.ne.jp/fat47/20140212/1392171784
		$sql = "SELECT * FROM " .
			"mail_queues MailQueue, " .
			"mail_queue_users MailQueueUser " .
			"WHERE " .
			"MailQueue.id = MailQueueUser.mail_queue_id " .
			"AND MailQueue.send_time <= ? " .
			"FOR UPDATE ";
		$mailQueues = $this->MailQueue->query($sql, array($now));

		if (empty($mailQueues)) {
			//CakeLog::debug("MailQueue is empty. [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
		}

		// SiteSettingからメール設定を取得する
		SiteSettingUtil::setup(array(
			'Mail.from',
			'Mail.from_name',
			'Mail.messageType',
			'Mail.transport',
			'Mail.smtp.host',
			'Mail.smtp.port',
			'Mail.smtp.user',
			'Mail.smtp.pass',
		), false);
		$from = SiteSettingUtil::read('Mail.from');

		// Fromが空ならメール未設定のため、メール送らない
		if (empty($from)) {
			LogError('From Address is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
		}

		$beforeId = $mailQueues[0]['MailQueue']['id'];

		foreach ($mailQueues as $mailQueue) {
			// idが変わったら、MailQueue削除
			if ($beforeId != $mailQueue['MailQueue']['id']) {
				$this->MailQueue->delete($beforeId);
			}

			$mail = new NetCommonsMail();
			$mail->initShell($mailQueue);

			//送信しない（デバッグ用）
			//			$config = $mail->config();
			//			$config['transport'] = 'Debug';
			//			$mail->config($config);
			//			$messages = $mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);
			//			CakeLog::debug(print_r($messages, true));

			$mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);

			// 送信後にMailQueueUser削除
			$this->MailQueueUser->delete($mailQueue['MailQueueUser']['id']);
			$beforeId = $mailQueue['MailQueue']['id'];
		}

		// 後始末 - MailQueue削除
		$this->MailQueue->delete($beforeId);
	}
}
