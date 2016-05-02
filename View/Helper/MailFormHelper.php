<?php
/**
 * MailFormHelper
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * MailFormHelper
 *
 * @package NetCommons\ContentComments\View\Helper
 */
class MailFormHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Mails.MailsHtml',
	);

/**
 * コメント一覧表示＆コメント登録
 *
 * #### Sample code
 * ##### template file(ctp file) - 通常パターン
 * ```
 * <?php echo $this->MailForm->editFrom(
 *   array(
 *     array(
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *     )
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action')
 * ); ?>
 * ```
 *
 * ##### template file(ctp file) - 回答メールありパターン
 * ```
 * <?php echo $this->MailForm->editFrom(
 *   array(
 *     array(
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *     ),
 *     array(
 *       'mailTypeKey' => MailSettingFixedPhrase::ANSWER_TYPE,
 *       'panelHeading' => __d('mails', '回答メール'),
 *       'mailBodyPopoverMessage' => __d('videos', '回答メールのpopover'),
 *       'permission' => 'mail_answer_receivable',
 *     ),
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action')
 * ); ?>
 * ```
 *
 * @param array $editForms 編集フォーム設定
 * @param string $cancelUrl キャンセルボタン遷移先URL
 * @param int $useNoticeAuthority 通知する権限を使う
 * @param int $useReplayTo 返信を受けるメールアドレスを使う
 * @param array $options フォームオプション
 * @param string $action 決定ボタン遷移先URL
 * @return string HTML tags
 */
	public function editFrom($editForms = array(), $cancelUrl = null, $useNoticeAuthority = 1,
								$useReplayTo = 1, $options = array(), $action = null) {
		$output = '';
		if (isset($action)) {
			$options['url'] = $action;
		}

		$editForms = Hash::merge(array(
			array(
				'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
				'panelHeading' => __d('mails', '投稿メール'),
				'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
				'permission' => 'mail_content_receivable',
			),
		), $editForms);

		$output .= $this->_View->element('Mails.edit_form', array(
			'editForms' => $editForms,
			'useNoticeAuthority' => $useNoticeAuthority,
			'useReplayTo' => $useReplayTo,
			'cancelUrl' => $cancelUrl,
			'options' => $options,
		));

		return $output;
	}
}
