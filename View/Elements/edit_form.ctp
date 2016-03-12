<?php
/**
 * Element of mail edit form
 *   - $action: Action for delete request.
 *   - $callback: Callback element for parameters and messages.
 *   - $callbackOptions: Callback options for element.
 *   - $cancelUrl: Cancel url.
 *   - $mailTypeKey: メールの種類
 *   - $mailBodyPopoverMessage: メール定型文ポップオーバー内の説明文（HTML可）
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
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->inlineCheckbox('MailSetting.is_mail_send', array(
					'type' => 'checkbox',
					'label' => __d('mails', 'Use the mail notification function')
				)); ?>
			</div>

			<div class="col-xs-11 col-xs-offset-1">
				<?php /* 暫定対応 */
				echo $this->element('Blocks.block_permission_setting', array(
					'settingPermissions' => array(
						'mail_content_receivable' => __d('mails', 'Notification to the authority'),
					),
				)); ?>

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('MailSetting.replay_to', array(
						'type' => 'text',
						'label' => __d('mails', 'E-mail address to receive a reply'),
						'div' => '',
					)); ?>
					<p class="help-block"><?php echo __d('mails', 'You can specify if you want to change the e-mail address to receive a reply'); ?></p>
				</div>

				<?php echo $this->NetCommonsForm->input('MailSetting.mail_fixed_phrase_subject', array(
					'type' => 'text',
					'label' => __d('mails', 'Subject'),
					'required' => true,
				)); ?>

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('MailSetting.mail_fixed_phrase_body', array(
						'type' => 'textarea',
						'label' => __d('mails', 'Body'),
						'required' => true,
						'div' => '',
					)); ?>
					<p class="help-block">
						<?php echo __d('mails', 'Can use an embedded keyword in the subject line and body'); ?>
						<?php /* popover説明 */ ?>
						<?php $popoverHtmlId = 'nc-mail-body-' . Current::read('Frame.id'); ?>
						<a tabindex="0"
						   id="<?php echo $popoverHtmlId; ?>"
						   data-toggle="popover"
						   data-placement="bottom"
						   title="<?php echo __d('mails', 'Embedded keyword?'); ?>"
						   data-content="<?php echo __d('mails', 'Each of the embedded keywords, will be sent is converted to the corresponding content. <br />') . $mailBodyPopoverMessage; ?>">
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
