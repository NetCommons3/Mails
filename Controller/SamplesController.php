<?php
/**
 * メール送信サンプル Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('NetCommonsMail', 'Mails.Utility');

/**
 * メール送信サンプル Controller
 * debug用
 * 削除する予定
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Controller
 */
class SamplesController extends AppController {

/**
 * メール送信サンプル
 *
 * @return CakeResponse
 */
	public function mailtest() {
		$mail = new CakeEmail('sakura');						// インスタンス化

		//送信しない（デバッグ用）
		$config = $mail->config();
		$config['transport'] = 'Debug';
		$mail->config($config);

		$mail->to('mutaguchi@opensource-workshop.jp');			// 送信先
		$mail->subject('メールタイトル');						// メールタイトル

		$messages = $mail->send('メール本文');								// メール送信
		var_dump($messages);
	}

/**
 * メール送信サンプル２
 *
 * @return CakeResponse
 */
	public function mailtest2() {
		$mail = new NetCommonsMail();
		$mail->send2();

		//		MailSend::MailSend();
	}

/**
 * メール送信サンプル３
 *
 * @return CakeResponse
 */
	public function mailtest3() {
		$mail = new NetCommonsMail();
		$blockKey = '47d26e7ca2e7d92327935b5af4971fb0';
		$mail->send3($blockKey);
	}
}