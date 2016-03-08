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

/**
 * メール送信 Shell
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Console\Command
 * @property MailQueue $MailQueue
 * @property MailQueueUser $MailQueueUser
 * @property Language $Language
 * @property SiteSetting $SiteSetting
 */
class MailSendShell extends AppShell {

/**
 * @var bool デバッグON
 */
	//const IS_DEBUG = false;
	const IS_DEBUG = true;

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
	);

/**
 * Cronからメール送信 - Cronからコールするのはこちらを使ってください
 *
 * @return void
 * @link http://book.cakephp.org/2.0/ja/console-and-shells.html#id2
 */
	public function main() {
		// ここに、初回のみ、システム管理の「クーロンを使いますフラグ」をONにする対応 記述

		$this->main();
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
			if (self::IS_DEBUG) {
				CakeLog::debug('MailQueue is empty.');
			}
			return;
		}

		// SiteSettingからメール設定を取得する
		/** @see SiteSetting::getSiteSettingForEdit() */
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
				'Config.language',
			)
		));

		//$Language = ClassRegistry::init('M17n.Language');
		$languageCode = Hash::get($siteSetting['Config.language'], '0.value');

		// Language.id取得
		$languages = $this->Language->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'Language.code' => $languageCode,
			)
		));
		$languageId = Hash::get($languages, 'Language.id');

		foreach ($mailQueues as $mailQueue) {

			foreach ($mailQueue['MailQueueUser'] as $mailQueueUser) {
				$mail = new NetCommonsMail();
				$mail->initShell($siteSetting, $mailQueue, $languageId);

				if (self::IS_DEBUG) {
					//送信しない（デバッグ用）
					$config = $mail->config();
					$config['transport'] = 'Debug';
					$mail->config($config);
				}

				$messages = $mail->sendQueueMail($mailQueueUser);

				if (self::IS_DEBUG) {
					CakeLog::debug(print_r($messages, true));
				}

				// 送信後にキュー削除
				$this->MailQueueUser->deleteMailQueueUser($mailQueueUser['id']);
			}
			$this->MailQueue->deleteMailQueue($mailQueue['MailQueue']['id']);
		}

		// debug
		//$mail = new NetCommonsMail('sakura');
		//$mail->send2();
		//$this->out('メール送信済み');
	}
}