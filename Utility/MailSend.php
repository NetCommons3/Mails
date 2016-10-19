<?php
/**
 * メール送信 Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * メール送信 Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 */
class MailSend {

/**
 * メール送信呼び出し
 *
 * @return void
 */
	public static function send() {
		// バックグラウンドでメール送信
		// コマンド例) Console/cake Mails.mailSend send
		MailSend::execInBackground(APP . 'Console' . DS . 'cake Mails.mailSend send');
	}

/**
 * バックグラウンド実行
 *
 * @param string $cmd コマンド
 * @return void
 */
	public static function execInBackground($cmd) {
		if (MailSend::isWindows()) {
			// Windowsの場合
			pclose(popen('cd ' . APP . ' && start /B ' . $cmd, 'r'));
		} else {
			// Linuxの場合
			// logrotate問題対応 http://dqn.sakusakutto.jp/2012/08/php_exec_nohup_background.html
			exec('nohup ' . $cmd . ' > /dev/null &');
		}
	}

/**
 * 動作しているOS がWindows かどうかを返す。
 *
 * @return bool
 */
	public static function isWindows() {
		if (DIRECTORY_SEPARATOR == '\\') {
			return true;
		}
		return false;
	}

/**
 * cakeコマンドに実行権限あるか
 *
 * @return bool
 */
	public static function isExecutableCake() {
		if (MailSend::isWindows()) {
			// Windowsの場合
			// is_executable()はwindowsの場合、exeのみしか判定できないため、一律trueを返す
			return true;
		} else {
			// Linuxの場合
			return is_executable(APP . 'Console' . DS . 'cake');
		}
	}
}
