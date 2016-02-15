<?php
/**
 * Element of block edit form
 *   - $model: Model for edit request.
 *   - $action: Action for delete request.
 *   - $callback: Callback element for parameters and messages.
 *   - $callbackOptions: Callback options for element.
 *   - $cancelUrl: Cancel url.
 *   - $options: Options array for Form->create()
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

if (! isset($options)) {
	$options = array();
}
if (isset($action)) {
	$options['url'] = $action;
}
if (! isset($cancelUrl)) {
	$cancelUrl = null;
}
?>

<?php echo $this->NetCommonsForm->create($model, Hash::merge(array(), $options)); ?>
	<div class="panel panel-default">
		<div class="panel-body">


<?php
// debug now!
// copy to C:\projects\NetCommons3\app\Plugin\Blocks\View\Elements\edit_form.ctp
?>
<!--			--><?php //echo $this->element($callback, (isset($callbackOptions) ? $callbackOptions : array())); ?>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->inlineCheckbox('', array(
					'type' => 'checkbox',
					'label' => __d('mails', 'メール通知機能を使用する')
				)); ?>
			</div>

			<div class="col-xs-11 col-xs-offset-1">
				<?php echo $this->element('Mails.mail_setting', array(
					'settingPermissions' => array(
						'mail_content_receivable' => __d('mails', '通知する権限'),
					),
				)); ?>

				<?php echo $this->NetCommonsForm->input('', array(
					'type' => 'text',
					'label' => __d('mails', '返信を受けるメールアドレス'),
				)); ?>
				<div><p class="help-block"><?php echo __d('mails', '返信を受けるメールアドレスを変えたい場合に指定できます。'); ?></p></div>

				<?php echo $this->NetCommonsForm->input('', array(
					'type' => 'text',
					'label' => __d('mails', '件名'),
				)); ?>

				<?php echo $this->NetCommonsForm->input('', array(
					'type' => 'textarea',
					'label' => __d('mails', '本文'),
				)); ?>
				<div>
					<p class="help-block">
						件名と本文には、
						{X-SITE_NAME}、{X-PLUGIN_NAME}、{X-ROOM}、
						{X-CHANNEL_NAME}、{X-SUBJECT}、{X-USER}
						{X-TO_DATE}、{X-BODY}、{X-APPROVAL_COMMENT}
						{X-URL}
						というキーワードを使えます。
						それぞれのキーワードは、
						サイト名称、ルーム名称、プラグイン名
						チャンネル名、動画タイトル、投稿者
						投稿日時、登録内容、承認コメント
						登録内容のURL
						に変換されて送信されます。
					</p>
				</div>
			</div>
		</div>


		<div class="panel-footer text-center">
			<?php echo $this->Button->cancelAndSave(__d('net_commons', 'Cancel'), __d('net_commons', 'OK'), $cancelUrl); ?>
		</div>
	</div>
<?php echo $this->NetCommonsForm->end();
