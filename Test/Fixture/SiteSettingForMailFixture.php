<?php
/**
 * SiteSettingFixture
 *
 * @author Jun Nishikawa <topaz2@m0n0m0n0.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('SiteSettingFixture', 'SiteManager.Test/Fixture');

/**
 * Summary for SiteSettingFixture
 */
class SiteSettingForMailFixture extends SiteSettingFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'site_settings';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// * サイト名
		// ** 日本語
		array(
			'language_id' => '2',
			'key' => 'App.site_name',
			'value' => 'NetCommons3',
		),
		// * 本文ヘッダー
		// ** 日本語
		array(
			'language_id' => '2',
			'key' => 'Mail.body_header',
			'value' => '※このメールに返信しても相手には届きませんのでご注意ください。

',
		),
		// * 署名
		// ** 日本語
		array(
			'language_id' => '2',
			'key' => 'Mail.signature',
			'value' => "-- \r\nPowered by NetCommons",	// テスト用
		),
		// * サイト名
		// ** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'App.site_name',
		//			'value' => 'NetCommons3',
		//		),
		// * 本文ヘッダー
		// ** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Mail.body_header',
		//			'value' => '- Please note even if you reply this mail directly, the mail\'s sender can not receive it.
		//
		//',
		//		),
		// * 署名
		// ** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Mail.signature',
		//			'value' => "-- \r\nPowered by NetCommons",	// テスト用
		//		),
		array(
			'language_id' => '0',
			'key' => 'Mail.from',
			'value' => 'from@dummy.com',
		),
		array(
			'language_id' => '2',
			'key' => 'Mail.from_name',
			'value' => '管理者',
		),
		array(
			'language_id' => '0',
			'key' => 'Mail.use_cron',
			'value' => '1',
		),
		array(
			'language_id' => '0',
			'key' => 'Mail.messageType',
			'value' => 'html',
		),
		//コンテンツ承認
		// * コンテンツ承認設定
		// ** 申請メールの件名
		array(
			'language_id' => '2',
			'key' => 'Workflow.approval_mail_subject',
			'value' => '(承認依頼){X-PLUGIN_MAIL_SUBJECT}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.approval_mail_subject',
		//			'value' => '(approval request){X-PLUGIN_MAIL_SUBJECT}[{X-SITE_NAME}]{X-PLUGIN_NAME}',
		//		),
		// ** 申請メールの本文
		// *** 日本語
		array(
			'language_id' => '2',
			'key' => 'Workflow.approval_mail_body',
			'value' => '{X-USER}さんから{X-PLUGIN_NAME}の承認依頼があったことをお知らせします。

{X-WORKFLOW_COMMENT}


{X-PLUGIN_MAIL_BODY}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.approval_mail_body',
		//			'value' => '{X-USER} we let you know that there was an approval request of {X-PLUGIN_NAME} from.
		//
		//{X-WORKFLOW_COMMENT}
		//
		//
		//{X-PLUGIN_MAIL_BODY}',
		//		),
		// ** 差し戻しメールの件名
		// *** 日本語
		array(
			'language_id' => '2',
			'key' => 'Workflow.disapproval_mail_subject',
			'value' => '(差し戻し){X-PLUGIN_MAIL_SUBJECT}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.disapproval_mail_subject',
		//			'value' => '(remand){X-PLUGIN_MAIL_SUBJECT} [{X-SITE_NAME}]{X-PLUGIN_NAME}',
		//		),
		// ** 差し戻しメールの本文
		// *** 日本語
		array(
			'language_id' => '2',
			'key' => 'Workflow.disapproval_mail_body',
			'value' => '{X-USER}さんの{X-PLUGIN_NAME}が差し戻しされたことをお知らせします。
もし{X-USER}さんの{X-PLUGIN_NAME}に覚えがない場合はこのメールを破棄してください。

{X-WORKFLOW_COMMENT}


{X-PLUGIN_MAIL_BODY}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.disapproval_mail_body',
		//			'value' => '{X-USER} \'s {X-PLUGIN_NAME} is to inform you that it has been remanded.
		//Please discard this email if you\'re still unable to recognize the if {X-USER} \'s {X-PLUGIN_NAME}.
		//
		//{X-WORKFLOW_COMMENT}
		//
		//
		//{X-PLUGIN_MAIL_BODY}',
		//		),
		// ** 承認完了メールの件名
		// *** 日本語
		array(
			'language_id' => '2',
			'key' => 'Workflow.approval_completion_mail_subject',
			'value' => '(承認完了){X-PLUGIN_MAIL_SUBJECT}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.approval_completion_mail_subject',
		//			'value' => '(approval completion){X-PLUGIN_MAIL_SUBJECT}',
		//		),
		// ** 承認完了メールの本文
		// *** 日本語
		array(
			'language_id' => '2',
			'key' => 'Workflow.approval_completion_mail_body',
			'value' => '{X-USER}さんの{X-PLUGIN_NAME}の承認が完了されたことをお知らせします。
もし{X-USER}さんの{X-PLUGIN_NAME}に覚えがない場合はこのメールを破棄してください。

{X-WORKFLOW_COMMENT}


{X-PLUGIN_MAIL_BODY}',
		),
		// *** 英語
		//		array(
		//			'language_id' => '1',
		//			'key' => 'Workflow.approval_completion_mail_body',
		//			'value' => 'To inform you that approval of {X-USER} \'s {X-PLUGIN_NAME} has been completed.
		//Please discard this email if you\'re still unable to recognize the if {X-USER} \'s {X-PLUGIN_NAME}.
		//
		//{X-WORKFLOW_COMMENT}
		//
		//
		//{X-PLUGIN_MAIL_BODY}',
		//		),
	);

}
