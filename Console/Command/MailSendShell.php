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
		'Blocks.Block',
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
 * Console/cake Mails.mailSend
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

			SiteSettingUtil::write('Mail.use_cron', 1, 0);
		}

		// メール送信
		$this->send();
	}

/**
 * メール送信
 * ### コマンド
 * ```
 * Console/cake Mails.mailSend send
 * ```
 *
 * @return void
 */
	public function send() {
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
			$this->out('<error>From Address is empty. [' . __METHOD__ . ']</error>');
			return $this->_stop();
		}

		$now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

		//メール送信する対象のメールキューの実行時間を更新する
		$result = $this->_updateExecuteTime($now);
		if (! $result) {
			return $this->_stop();
		}

		//対象の実行時間のメールキューのみ処理する
		$sql = 'SELECT * FROM ' .
			$this->MailQueue->tablePrefix . 'mail_queues MailQueue, ' .
			$this->MailQueueUser->tablePrefix . 'mail_queue_users MailQueueUser ' .
			'WHERE ' .
			'MailQueue.id = MailQueueUser.mail_queue_id ' .
			'AND MailQueue.execute_time = ? ';
		$mailQueues = $this->MailQueue->query($sql, array($now));
		if (empty($mailQueues)) {
			$this->out('MailQueue is empty. [' . __METHOD__ . '] ');
			return $this->_stop();
		}

		$beforeId = $mailQueues[0]['MailQueue']['id'];
		$isSend = null;

		foreach ($mailQueues as $mailQueue) {
			// idが変わったら、MailQueue削除
			if ($beforeId != $mailQueue['MailQueue']['id']) {
				$this->MailQueue->delete($beforeId);
				$isSend = null;
			}

			// ブロック非公開、期間限定の対応
			if (is_null($isSend)) {
				$isSend = $this->_isSendBlockType($mailQueue);
			}

			if ($isSend) {
				$mail = new NetCommonsMail();
				$mail->initShell($mailQueue);

				try {
					$mail->sendQueueMail($mailQueue['MailQueueUser'], $mailQueue['MailQueue']['language_id']);
				} catch (Exception $ex) {
					// SMTPの設定間違い等で送れなくても、処理を続行。メールは破棄（設定間違いでメールがキューに溜まる事を防ぐ）
					CakeLog::error($ex);
				}
			}

			// MailQueueUser削除
			$this->MailQueueUser->delete($mailQueue['MailQueueUser']['id']);
			$beforeId = $mailQueue['MailQueue']['id'];
		}

		// 後始末 - MailQueue削除
		$this->MailQueue->delete($beforeId);
	}

/**
 * ブロック状態によってメール送るか（ブロック非公開、期間外はメール送らない）
 * リマインダーや未来日メールでブロック公開後、後からブロック非公開にしたらメールが残るケースに対応
 *
 * @param array $mailQueue メールキューデータ
 * @return bool
 */
	protected function _isSendBlockType($mailQueue) {
		$query = array(
			'recursive' => -1,
			'conditions' => array(
				//'Block.language_id' => $mailQueue['MailQueue']['language_id'],
				'Block.plugin_key' => $mailQueue['MailQueue']['plugin_key'],
				'Block.key' => $mailQueue['MailQueue']['block_key'],
			),
		);
		$block = $this->Block->find('first', $query);

		// ブロック非公開、期間外はメール送らない
		return $this->MailQueue->isSendBlockType($block);
	}

/**
 * 実行時間を更新する
 *
 * @param string $now 現在時刻
 * @return bool
 */
	protected function _updateExecuteTime($now) {
		try {
			//トランザクションBegin
			$this->MailQueue->begin();

			// キュー取得＆ロック - シェル実行の排他を実現したいため、行ロックしている
			// http://k-1blog.com/development/program/post-7407/
			// http://d.hatena.ne.jp/fat47/20140212/1392171784
			$sql = 'SELECT COUNT(*) FROM ' .
				$this->MailQueue->tablePrefix . 'mail_queues MailQueue ' .
				'WHERE MailQueue.execute_time = ? ' .
				'FOR UPDATE ';
			$count = $this->MailQueue->query($sql, array($now));

			//全くの同時刻に実行されたものは無視する
			if (isset($count[0][0]['COUNT(*)']) &&
					$count[0][0]['COUNT(*)'] > 0) {
				$this->MailQueue->rollback();
				$this->out('MailQueue is executing ' . $now . ' [' . __METHOD__ . '] ');
				return false;
			}

			$update = [
				'MailQueue.execute_time' => "'" . $now . "'"
			];
			$conditions = [
				'MailQueue.execute_time' => null,
				'MailQueue.send_time <=' => $now
			];
			$this->MailQueue->updateAll($update, $conditions);

			//トランザクションCommit
			$this->MailQueue->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->MailQueue->rollback($ex);
			return false;
		}

		return true;
	}
}
