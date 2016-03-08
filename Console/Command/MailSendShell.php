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
 */
class MailSendShell extends AppShell {

/**
 * メール送信
 *
 * @return void
 * @link http://book.cakephp.org/2.0/ja/console-and-shells.html#id2
 */
	public function main() {
		$MailQueue = ClassRegistry::init('Mails.MailQueue');
		$now = NetCommonsTime::getNowDatetime();

		// キュー取得
		/** @link http://www.cpa-lab.com/tech/081 */
		$mailQueues = $MailQueue->find('all', array(
			'recursive' => 1,
			'conditions' => array(
				'MailQueue.send_time <=' => $now,
			)
		));
		if (empty($mailQueues)) {
			return;
		}

		$SiteSetting = ClassRegistry::init('SiteManager.SiteSetting');
		$Language = ClassRegistry::init('M17n.Language');

		// SiteSettingからメール設定を取得する
		/** @see SiteSetting::getSiteSettingForEdit() */
		$siteSettings = $SiteSetting->getSiteSettingForEdit(array(
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

		$languageCode = Hash::get($siteSettings['Config.language'], '0.value');

		// Language.id取得
		$languages = $Language->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'Language.code' => $languageCode,
			)
		));
		$languageId = Hash::get($languages, 'Language.id');

		foreach ($mailQueues as $mailQueue) {

			foreach ($mailQueue['MailQueueUser'] as $mailQueueUser) {
				$mail = new NetCommonsMail();
				$mail->initShell($siteSettings, $languageId);
				// まだ仮
				//$mail->send2();

			}
		}

		$this->out('メール送信済み');
	}
}