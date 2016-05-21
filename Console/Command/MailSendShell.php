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

App::uses('Shell', 'Console');
App::uses('AppShell', 'Console/Command');
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
		// SiteSettingからメール設定を取得する
		SiteSettingUtil::setup(array(
			'Mail.use_cron',
		));

		// 初回のみ、システム管理の「クーロンを使いますフラグ」をONにする対応
		$useCron = SiteSettingUtil::read('Mail.use_cron', false);
		if (! $useCron) {
			$data['SiteSetting'] =
				$this->SiteSetting->getSiteSettingForEdit(array('key' => 'Mail.use_cron'));
			$data['SiteSetting']['Mail.use_cron'][0]['value'] = 1;
			$this->SiteSetting->saveSiteSetting($data);
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
		$isDebug = Hash::get($this->args, 0);
		//$isDebug = 1;
		$now = NetCommonsTime::getNowDatetime();

		// キュー取得 - 行ロック
		// http://k-1blog.com/development/program/post-7407/
		// http://d.hatena.ne.jp/fat47/20140212/1392171784
		$sql = 'SELECT * FROM ' .
			'mail_queues MailQueue, ' .
			'mail_queue_users MailQueueUser ' .
			'WHERE ' .
			'MailQueue.id = MailQueueUser.mail_queue_id ' .
			'AND MailQueue.send_time <= ? ' .
			'FOR UPDATE ';
		$mailQueues = $this->MailQueue->query($sql, array($now));

		if (empty($mailQueues)) {
			//CakeLog::debug("MailQueue is empty. [" . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
			//			$this->out('MailQueue is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			//exit;
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
			'App.site_name',
		));
		$from = SiteSettingUtil::read('Mail.from');

		// Fromが空ならメール未設定のため、メール送らない
		if (empty($from)) {
			LogError('From Address is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return;
			//			$this->out('<error>From Address is empty. [' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')</error>');
			//			exit;
		}

		$beforeId = $mailQueues[0]['MailQueue']['id'];

		foreach ($mailQueues as $mailQueue) {
			// idが変わったら、MailQueue削除
			if ($beforeId != $mailQueue['MailQueue']['id']) {
				$this->__delete($this->MailQueue, $beforeId, $isDebug);
			}

			$mail = new NetCommonsMail();
			$mail->initShell($mailQueue);

			if ($isDebug) {
				//送信しない（デバッグ用）
				$config = $mail->config();
				$config['transport'] = 'Debug';
				$mail->config($config);
				$mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);
				//$messages = $mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);
				//CakeLog::debug(print_r($messages, true));
			} else {
				$mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);
			}

			// 送信後にMailQueueUser削除
			$this->__delete($this->MailQueueUser, $mailQueue['MailQueueUser']['id'], $isDebug);
			$beforeId = $mailQueue['MailQueue']['id'];
		}

		// 後始末 - MailQueue削除
		$this->__delete($this->MailQueue, $beforeId, $isDebug);
	}

/**
 * 削除
 *
 * @param Model $model モデル
 * @param int $id ID
 * @param int $isDebug デバッグONフラグ
 * @return void
 */
	private function __delete($model, $id, $isDebug) {
		if ($isDebug) {
			return;
		}
		$model->delete($id);
	}
}
