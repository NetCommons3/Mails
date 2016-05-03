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
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *     ),
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action')
 * ); ?>
 * ```
 *
 * ##### Controller - 返信を受けるメールアドレス, 通知する権限 非表示
 * ```
 * 	public $helpers = array(
 * 		'Mails.MailForm' => array(
 * 			'useReplayTo' => 0,
 * 			'useNoticeAuthority' => 0,
 * 		)
 * 	);
 * ```
 * デフォルト：'useReplayTo' => 1, 'useNoticeAuthority' => 1
 *
 * @param array $editForms 編集フォーム設定
 * @param string $cancelUrl キャンセルボタン遷移先URL
 * @param array $options フォームオプション
 * @param string $action 決定ボタン遷移先URL
 * @return string HTML tags
 */
	public function editFrom($editForms = array(), $cancelUrl = null, $options = array(),
							$action = null) {
		$output = '';
		if (isset($action)) {
			$options['url'] = $action;
		}
		// 返信を受けるメールアドレスを使う
		$useReplayTo = Hash::get($this->settings, 'useReplayTo');
		if ($useReplayTo === null) {
			$useReplayTo = 1;
		}
		// 通知する権限を使う
		$useNoticeAuthority = Hash::get($this->settings, 'useNoticeAuthority');
		if ($useReplayTo === null) {
			$useNoticeAuthority = 1;
		}

		if (count($editForms) == 2) {
			// $editForms 2件は、回答メールありと推定
			$editForms = Hash::merge(array(
				array(
					'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
					'panelHeading' => __d('mails', '投稿メール'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
					'permission' => 'mail_content_receivable',
				),
				array(
					'mailTypeKey' => MailSettingFixedPhrase::ANSWER_TYPE,
					'panelHeading' => __d('mails', '回答メール'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
					'permission' => 'mail_answer_receivable',
				),
			), $editForms);

		} else {
			// 通常
			$editForms = Hash::merge(array(
				array(
					'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
					'panelHeading' => __d('mails', '投稿メール'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
					'permission' => 'mail_content_receivable',
				),
			), $editForms);
		}

		$output .= $this->_View->element('Mails.edit_form', array(
			'editForms' => $editForms,
			'useReplayTo' => $useReplayTo,
			'useNoticeAuthority' => $useNoticeAuthority,
			'cancelUrl' => $cancelUrl,
			'options' => $options,
		));

		return $output;
	}
}
