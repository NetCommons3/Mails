<?php
/**
 * Element of mail edit form
 *   - $editForms: 編集フォーム設定
 *   - $cancelUrl: Cancel url.
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
?>

<?php echo $this->NetCommonsForm->create('MailSetting', Hash::merge(array(), $options)); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.id'); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.block_key', array('value' => Current::read('Block.key'))); ?>

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

				<?php foreach ($editForms as $index => $editForm) : ?>
					<?php
					$mailSettingFixedPhrase = hash::get($this->request->data, 'MailSettingFixedPhrase.' . $editForm['mailTypeKey']);
					$id = hash::get($this->request->data, 'MailSettingFixedPhrase.' . $editForm['mailTypeKey'] . '.id');
					?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.id', array('value' => $id)); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.language_id', array('value' => Current::read('Language.id'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.block_key', array('value' => Current::read('Block.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.type_key', array('value' => $editForm['mailTypeKey'])); ?>

					<div class="panel panel-default">
						<div class="panel-heading">
							<?php echo $editForm['panelHeading']; ?>
						</div>
						<div class="panel-body">
							<?php if ($editForm['useNoticeAuthority']): ?>
								<?php echo $this->element('Blocks.block_permission_setting', array(
									'settingPermissions' => array(
										$editForm['permission'] => __d('mails', 'Notification to the authority'),
									),
								)); ?>
							<?php endif; ?>

							<?php echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_subject', array(
								'type' => 'text',
								'label' => __d('mails', 'Subject'),
								'required' => true,
								'value' => $mailSettingFixedPhrase['mail_fixed_phrase_subject'],
							)); ?>

							<div class="form-group">
								<?php echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_body', array(
									'type' => 'textarea',
									'label' => __d('mails', 'Body'),
									'required' => true,
									'value' => $mailSettingFixedPhrase['mail_fixed_phrase_body'],
									'div' => '',
								)); ?>
								<div class="help-block">
									<?php /* popover説明 */ ?>
									<?php echo $this->NetCommonsHtml->mailHelp($editForm['mailBodyPopoverMessage']); ?>
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
