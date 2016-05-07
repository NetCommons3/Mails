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
App::uses('MailSettingFixedPhrase', 'Mails.Model');

/**
 * MailFormHelper
 *
 * @package NetCommons\ContentComments\View\Helper
 */
class MailFormHelper extends AppHelper {

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
 * ##### template file(ctp file) - 返信を受けるメールアドレス, 通知する権限 非表示
 * ```
 * <?php echo $this->MailForm->editFrom(
 *   array(
 *     array(
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *       'useNoticeAuthority' => 0, // 通知する権限 非表示
 *     )
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action'),
 *   0 // 返信を受けるメールアドレス 非表示
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
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *     ),
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action')
 * ); ?>
 * ```
 *
 * @param array $editForms 編集フォーム設定
 * @param string $cancelUrl キャンセルボタン遷移先URL
 * @param int $useReplayTo 返信を受けるメールアドレスを使う
 * @param array $options フォームオプション
 * @param string $action 決定ボタン遷移先URL
 * @return string HTML tags
 */
	public function editFrom($editForms = array(), $cancelUrl = null, $useReplayTo = 1,
								$options = array(), $action = null) {
		$output = '';
		if (isset($action)) {
			$options['url'] = $action;
		}

		if (count($editForms) == 2) {
			// $editForms 2件は、回答メールありと推定
			$editForms = Hash::merge(array(
				array(
					'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
					'panelHeading' => __d('mails', 'Posting mail'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
					'permission' => 'mail_content_receivable',
					'useNoticeAuthority' => 1,
				),
				array(
					'mailTypeKey' => MailSettingFixedPhrase::ANSWER_TYPE,
					'panelHeading' => __d('mails', 'Answer mail'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover.answer'),
					'permission' => 'mail_answer_receivable',
					'useNoticeAuthority' => 1,
				),
			), $editForms);

		} else {
			// 通常
			$editForms = Hash::merge(array(
				array(
					'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
					'panelHeading' => __d('mails', 'Posting mail'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
					'permission' => 'mail_content_receivable',
					'useNoticeAuthority' => 1,
				),
			), $editForms);
		}

		$output .= $this->_View->element('Mails.edit_form', array(
			'editForms' => $editForms,
			'useReplayTo' => $useReplayTo,
			'cancelUrl' => $cancelUrl,
			'options' => $options,
		));

		return $output;
	}
}
