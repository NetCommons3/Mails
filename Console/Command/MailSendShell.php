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
		$mail = new NetCommonsMail();
		// 仮
		$mail->send2();

		//$this->out('メール送信済み');
	}
}