<?php
/**
 * Element of mail edit form
 *   - $action: Action for delete request.
 *   - $cancelUrl: Cancel url.
 *   - $mailTypeKey: メールの種類
 *   - $mailBodyPopoverMessage: メール定型文ポップオーバー内の説明文（HTML可）
 *   - $useNoticeAuthority: 通知する権限を使うか
 *   - $useReplayTo: 返信を受けるメールアドレスを使うか
 *   - $options: Options array for Form->create()
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//if (! isset($options)) {
//	$options = array();
//}
//if (isset($action)) {
//	$options['url'] = $action;
//}
//if (! isset($cancelUrl)) {
//	$cancelUrl = null;
//}
//if (! isset($useNoticeAuthority)) {
//	$useNoticeAuthority = 1;
//}
//if (! isset($useReplayTo)) {
//	$useReplayTo = 1;
//}
//$editForms = Hash::merge(array(
//	array(
//		'mailTypeKey' => MailSettingFixedPhrase::DEFAULT_TYPE,
//		'panelHeading' => __d('mails', '投稿メール'),
//		'mailBodyPopoverMessage' => __d('mails', 'MailSetting.mail_fixed_phrase_body.popover'),
//		'permission' => 'mail_content_receivable',
//	),
//), $editForms);
?>

<?php echo $this->NetCommonsForm->create('MailSetting', Hash::merge(array(), $options)); ?>

	<div class="panel panel-default">
		<div class="panel-body">
			<div class="col-xs-12">
				<div class="form-inline">
					<?php echo $this->NetCommonsForm->inlineCheckbox('MailSetting.is_mail_send', array(
						'type' => 'checkbox',
						'label' => __d('mails', 'Use the mail notification function'),
					)); ?>
					<div class="help-block"><?php echo __d('mails', 'If you do not want to use, and removes any mail was scheduled to be sent to the future'); ?></div>
				</div>
			</div>

			<div class="col-xs-11 col-xs-offset-1">

				<?php if ($useReplayTo): ?>
					<div class="form-group">
						<?php echo $this->NetCommonsForm->input('MailSetting.replay_to', array(
							'type' => 'text',
							'label' => __d('mails', 'E-mail address to receive a reply'),
							'div' => '',
						)); ?>
						<p class="help-block"><?php echo __d('mails', 'You can specify if you want to change the e-mail address to receive a reply'); ?></p>
					</div>
				<?php endif; ?>

				<?php foreach ($editForms as $editForm) : ?>

					<?php echo $this->NetCommonsForm->hidden('MailSetting.id'); ?><?php /* 多重対応予定 */ ?>
					<?php echo $this->NetCommonsForm->hidden('MailSetting.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSetting.block_key', array('value' => Current::read('Block.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.id'); ?><?php /* 多重対応予定 */ ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.language_id', array('value' => Current::read('Language.id'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.block_key', array('value' => Current::read('Block.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.type_key', array('value' => $editForm['mailTypeKey'])); ?>

					<div class="panel panel-default">
						<div class="panel-heading">
							<?php echo $editForm['panelHeading']; ?>
						</div>
						<div class="panel-body">
							<?php if ($useNoticeAuthority): ?>
								<?php echo $this->element('Blocks.block_permission_setting', array(
									'settingPermissions' => array(
										$editForm['permission'] => __d('mails', 'Notification to the authority'),
									),
								)); ?>
							<?php endif; ?>

							<?php echo $this->NetCommonsForm->input('MailSettingFixedPhrase.mail_fixed_phrase_subject', array(
								'type' => 'text',
								'label' => __d('mails', 'Subject'),
								'required' => true,
							)); ?>

							<div class="form-group">
								<?php echo $this->NetCommonsForm->input('MailSettingFixedPhrase.mail_fixed_phrase_body', array(
									'type' => 'textarea',
									'label' => __d('mails', 'Body'),
									'required' => true,
									'div' => '',
								)); ?>
								<div class="help-block">
									<?php /* popover説明 */ ?>
									<?php echo $this->MailsHtml->help($editForm['mailBodyPopoverMessage']); ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>

			</div>
		</div>

		<div class="panel-footer text-center">
			<?php echo $this->Button->cancelAndSave(__d('net_commons', 'Cancel'), __d('net_commons', 'OK'), $cancelUrl); ?>
		</div>
	</div>
<?php echo $this->NetCommonsForm->end();
