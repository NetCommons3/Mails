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
 * メール設定
 *
 * #### Sample code
 * ##### template file(ctp file) - 通常パターン
 * ```php
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
 * ##### template file(ctp file) - 問合せ先メールアドレス, 通知する権限 承認メール通知機能を使う 非表示
 * ```php
 * <?php echo $this->MailForm->editFrom(
 *   array(
 *     array(
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *       'useNoticeAuthority' => 0, // 通知する権限 非表示
 *     )
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action'),
 *   0, // 問合せ先メールアドレス 非表示
 *   0, // メール通知機能を使う ヘルプメッセージ 非表示
 *   0 // 承認メール通知機能を使う 非表示
 * ); ?>
 * ```
 *
 * ##### template file(ctp file) - 回答メールありパターン
 * ```php
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
 * ##### template file(ctp file) - メール通知機能を使う ヘルプメッセージ表示
 * ```php
 * <?php echo $this->MailForm->editFrom(
 *   array(
 *     array(
 *       'mailBodyPopoverMessage' => __d('videos', 'MailSetting.mail_fixed_phrase_body.popover'),
 *     )
 *   ),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action'),
 *   1, // 問合せ先メールアドレス 表示
 *   1  // メール通知機能を使う ヘルプメッセージ 表示
 * ); ?>
 * ```
 *
 * ##### template file(ctp file) - 承認メール通知機能を使う のみ表示
 * ```php
 * <?php echo $this->MailForm->editFrom(
 *   array(),
 *   NetCommonsUrl::backToIndexUrl('default_setting_action'),
 *   0, // 問合せ先メールアドレス 非表示
 *   0, // メール通知機能を使う ヘルプメッセージ 非表示
 *   1, // 承認メール通知機能を使う 表示
 *   0 // メール通知機能を使う 非表示
 * ); ?>
 * ```
 *
 * @param array $editForms 編集フォーム設定
 * @param string $cancelUrl キャンセルボタン遷移先URL
 * @param int $useReplyTo 問合せ先メールアドレスを使う
 * @param int $isMailSendHelp メール通知機能を使うヘルプメッセージを表示するか
 * @param int $useMailSendApproval 承認メール通知機能を使う を表示するか
 * @param int $useMailSend メール通知機能を使う 及び関連項目を表示するか
 * @param array $options フォームオプション
 * @param string $action 決定ボタン遷移先URL
 * @return string HTML tags
 */
	public function editFrom($editForms = array(), $cancelUrl = null, $useReplyTo = 0,
								$isMailSendHelp = 0, $useMailSendApproval = 1, $useMailSend = 1,
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
					'permissionOnly' => false,
				),
				array(
					'mailTypeKey' => MailSettingFixedPhrase::ANSWER_TYPE,
					'panelHeading' => __d('mails', 'Answer mail'),
					'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover.answer'),
					'permission' => 'mail_answer_receivable',
					'useNoticeAuthority' => 1,
					'permissionOnly' => false,
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
					'permissionOnly' => false,
				),
			), $editForms);
		}

		$output .= $this->_View->element('Mails.edit_form', array(
			'editForms' => $editForms,
			'useReplyTo' => $useReplyTo,
			'isMailSendHelp' => $isMailSendHelp,
			'useMailSendApproval' => $useMailSendApproval,
			'useMailSend' => $useMailSend,
			'cancelUrl' => $cancelUrl,
			'options' => $options,
		));

		return $output;
	}
}
