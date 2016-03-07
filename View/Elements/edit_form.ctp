<?php
/**
 * Element of mail edit form
 *   - $action: Action for delete request.
 *   - $callback: Callback element for parameters and messages.
 *   - $callbackOptions: Callback options for element.
 *   - $cancelUrl: Cancel url.
 *   - $mailTypeKey: メールの種類
 *   - $options: Options array for Form->create()
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
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
if (! isset($mailTypeKey)) {
	$mailTypeKey = 'contents';
}
?>

<?php echo $this->NetCommonsForm->create('MailSetting', Hash::merge(array(), $options)); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.id'); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.block_key', array('value' => Current::read('Block.key'))); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.type_key', array('value' => $mailTypeKey)); ?>

	<div class="panel panel-default">
		<div class="panel-body">
			<?php //echo $this->element($callback, (isset($callbackOptions) ? $callbackOptions : array())); ?>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->inlineCheckbox('MailSetting.is_mail_send', array(
					'type' => 'checkbox',
					'label' => __d('mails', 'メール通知機能を使用する')
				)); ?>
			</div>

			<div class="col-xs-11 col-xs-offset-1">
				<?php echo $this->element('Mails.block_creatable_setting_inline', array(
					'settingPermissions' => array(
						'mail_content_receivable' => __d('mails', '通知する権限'),
					),
				)); ?>

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('MailSetting.replay_to', array(
						'type' => 'text',
						'label' => __d('mails', '返信を受けるメールアドレス'),
						'div' => '',
					)); ?>
					<p class="help-block"><?php echo __d('mails', '返信を受けるメールアドレスを変えたい場合に指定できます'); ?></p>
				</div>

				<?php echo $this->NetCommonsForm->input('MailSetting.mail_fixed_phrase_subject', array(
					'type' => 'text',
					'label' => __d('mails', '件名'),
					'required' => true,
				)); ?>

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('MailSetting.mail_fixed_phrase_body', array(
						'type' => 'textarea',
						'label' => __d('mails', '本文'),
						'required' => true,
						'div' => '',
					)); ?>
					<p class="help-block">
						<?php echo __d('mails', '件名と本文には埋め込みキーワードが使えます'); ?>
						<?php /* popover説明 */ ?>
						<?php $popoverHtmlId = 'nc-mail-body-' . Current::read('Frame.id'); ?>
						<a tabindex="0"
						   id="<?php echo $popoverHtmlId; ?>"
						   data-toggle="popover"
						   data-placement="bottom"
						   title="<?php echo __d('mails', '埋め込みキーワードとは？'); ?>"
						   data-content="<?php echo __d('videos', 'それぞれの埋め込みキーワードは、対応する内容に変換されて送信されます。<br />{X-SITE_NAME} : サイト名称<br />{X-PLUGIN_NAME} : プラグイン名称<br />{X-ROOM} : ルーム名称<br />{X-BLOCK_NAME} : チャンネル名<br />{X-SUBJECT} : 動画タイトル<br />{X-USER} : 投稿者<br />{X-TO_DATE} : 投稿日時<br />{X-BODY} : 登録内容<br />{X-URL} : 登録内容のURL'); ?>">
							<span class="glyphicon glyphicon-question-sign"></span>
						</a>
						<script>
							$(function() {
								$('#<?php echo $popoverHtmlId; ?>').popover({
									html: true
								});
							});
						</script>
					</p>
				</div>
			</div>
		</div>

		<div class="panel-footer text-center">
			<?php echo $this->Button->cancelAndSave(__d('net_commons', 'Cancel'), __d('net_commons', 'OK'), $cancelUrl); ?>
		</div>
	</div>
<?php echo $this->NetCommonsForm->end();
