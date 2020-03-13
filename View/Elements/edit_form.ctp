<?php
/**
 * Element of mail edit form
 *   - $editForms: 編集フォーム設定
 *   - $cancelUrl: Cancel url.
 *   - $useReplyTo: 問合せ先メールアドレスを使うか
 *   - $isMailSendHelp: メール通知機能を使うヘルプメッセージを表示するか
 *   - $useMailSendApproval: 承認メール通知機能を使う を表示するか
 *   - $useMailSend: メール通知機能を使う を表示するか
 *   - $options: Options array for Form->create()
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->css(array(
	'/mails/css/style.css',
));
?>

<?php echo $this->NetCommonsForm->create('MailSetting', Hash::merge(array(), $options)); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.id'); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
	<?php echo $this->NetCommonsForm->hidden('MailSetting.block_key', array('value' => Current::read('Block.key'))); ?>

	<div class="panel panel-default">
		<div class="panel-body">
			<div class="form-inline <?php echo !$isMailSendHelp ? 'mail-is-mail-send-row' : ''; ?>">
				<?php
				if ($useMailSend) {
					echo $this->NetCommonsForm->inlineCheckbox('MailSetting.is_mail_send', array(
						'type' => 'checkbox',
						'label' => __d('mails', 'Use the mail notification function'),
					));
				}
				if ($useMailSendApproval) {
					echo $this->NetCommonsForm->inlineCheckbox('MailSetting.is_mail_send_approval', array(
						'type' => 'checkbox',
						'div' => '',
						'label' => __d('mails', 'Use the approval mail notification function'),
					));
				}
				?>
			</div>
			<?php
			if ($isMailSendHelp) {
				echo $this->NetCommonsForm->help(__d('mails', 'If you do not want to use, and removes any mail was scheduled to be sent to the future'));
			}
			?>

			<?php if ($useMailSend): ?>
				<div class="row">
					<div class="col-xs-11 col-xs-offset-1">

						<?php if ($useReplyTo): ?>
							<div class="form-group">
								<?php echo $this->NetCommonsForm->input('MailSetting.reply_to', array(
									'type' => 'text',
									'label' => __d('mails', 'E-mail address to receive a reply'),
									'div' => '',
									'help' => __d('mails', 'You can specify if you want to change the e-mail address to receive a reply'),
								)); ?>
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

									<?php
										if ($editForm['permissionOnly']) {
											//echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_subject', array(
											//	'type' => 'hidden',
											//	'value' => $mailSettingFixedPhrase['mail_fixed_phrase_subject'],
											//));
										} else {
											echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_subject', array(
												'type' => 'text',
												'label' => __d('mails', 'Subject'),
												'required' => true,
												'value' => (isset($mailSettingFixedPhrase['mail_fixed_phrase_subject']) ?
															$mailSettingFixedPhrase['mail_fixed_phrase_subject'] : null),
												'div' => false,
											));
										}
									?>

									<?php
										if ($editForm['permissionOnly']) {
											//echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_body', array(
											//	'type' => 'hidden',
											//	'value' => $mailSettingFixedPhrase['mail_fixed_phrase_body'],
											//));
										} else {
											echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_body', array(
												'type' => 'textarea',
												'label' => __d('mails', 'Body'),
												'required' => true,
												'value' => (isset($mailSettingFixedPhrase['mail_fixed_phrase_body']) ?
															$mailSettingFixedPhrase['mail_fixed_phrase_body'] : null),
												'div' => false,
											));
											$mailHelp = $this->NetCommonsHtml->mailHelp($editForm['mailBodyPopoverMessage']);
											echo $this->NetCommonsForm->help($mailHelp);
										}
									?>
								</div>
							</div>
						<?php endforeach; ?>

					</div>
				</div>
			<?php else: ?>
				<?php /* メール通知機能を使う系を表示しない */ ?>
				<?php foreach ($editForms as $index => $editForm) : ?>
					<?php
					$mailSettingFixedPhrase = hash::get($this->request->data, 'MailSettingFixedPhrase.' . $editForm['mailTypeKey']);
					$id = hash::get($this->request->data, 'MailSettingFixedPhrase.' . $editForm['mailTypeKey'] . '.id');
					?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.id', array('value' => $id)); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.language_id', array('value' => Current::read('Language.id'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.plugin_key', array('value' => Current::read('Plugin.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.block_key', array('value' => Current::read('Block.key'))); ?>
					<?php echo $this->NetCommonsForm->hidden(
							'MailSettingFixedPhrase.' . $index . '.type_key',
							array('value' => (isset($editForm['mailTypeKey']) ? $editForm['mailTypeKey'] : null))
					); ?>
					<?php echo $this->NetCommonsForm->hidden('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_subject', array('value' => $mailSettingFixedPhrase['mail_fixed_phrase_subject'])); ?>
					<?php
					// hiddenに複数行入れるとセキュリティコンポーネントに引っかかるので、display:none;で対応
					echo $this->NetCommonsForm->input('MailSettingFixedPhrase.' . $index . '.mail_fixed_phrase_body', array(
						'type' => 'textarea',
						'style' => 'display:none;',
						'value' => $mailSettingFixedPhrase['mail_fixed_phrase_body'],
						'div' => false,
					));
					?>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>

		<div class="panel-footer text-center">
			<?php echo $this->Button->cancelAndSave(__d('net_commons', 'Cancel'), __d('net_commons', 'OK'), $cancelUrl); ?>
		</div>
	</div>
<?php echo $this->NetCommonsForm->end();
