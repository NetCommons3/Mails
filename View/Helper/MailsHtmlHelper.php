<?php
/**
 * MailsHtmlHelper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * MailsHtmlHelper
 *
 * @package NetCommons\Mails\View\Helper
 */
class MailsHtmlHelper extends AppHelper {

/**
 * ヘルプの表示
 *
 * #### Sample code
 * ##### template file(ctp file) - 通常パターン
 * ```
 * <?php echo $this->MailsHtml->help($editForm['mailBodyPopoverMessage']); ?>
 * ```
 *
 * @param string $content メッセージ内容(オリジナルタグの内容)
 * @param string $placement ポジション
 * @return string ヘルプHTML出力
 */
	public function help($content = '', $placement = 'bottom') {
		$html = '';

		$content = __d('mails', 'Each of the embedded keywords, will be sent is converted ' .
				'to the corresponding content. <br />') . $content;

		$html .= __d('mails', 'Can use an embedded keyword in the subject line and body') . ' ';
		$html .= '<a href="" data-toggle="popover" data-placement="' . $placement . '"' .
					' title="' . __d('mails', 'Embedded keyword?') . '"' . ' data-content="' . $content . '">';
		$html .= '<span class="glyphicon glyphicon-info-sign"></span>';
		$html .= '</a>';
		$html .= '<script type="text/javascript">' .
			'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });</script>';

		return $html;
	}

}
