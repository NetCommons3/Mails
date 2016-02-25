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

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('', array(
						'type' => 'text',
						'label' => __d('mails', '返信を受けるメールアドレス'),
						'div' => '',
					)); ?>
					<p class="help-block"><?php echo __d('mails', '返信を受けるメールアドレスを変えたい場合に指定できます。'); ?></p>
				</div>

				<?php echo $this->NetCommonsForm->input('', array(
					'type' => 'text',
					'label' => __d('mails', '件名'),
					'value' => '[{X-SITE_NAME}]{X-PLUGIN_NAME}投稿({X-ROOM} {X-CHANNEL_NAME})',
				)); ?>

				<div class="form-group">
					<?php echo $this->NetCommonsForm->input('', array(
						'type' => 'textarea',
						'label' => __d('mails', '本文'),
						'div' => '',
						'value' => '{X-PLUGIN_NAME}に投稿されたのでお知らせします。
ルーム名称:{X-ROOM}
チャンネル名:{X-CHANNEL_NAME}
動画タイトル:{X-SUBJECT}
投稿者:{X-USER}
投稿日時:{X-TO_DATE}


{X-BODY}

この投稿内容を確認するには下記のリンクをクリックして下さい。
{X-URL}',
					)); ?>
					<p class="help-block">
						<?php echo __d('mails', '件名と本文にはキーワードが使えます。'); ?>
						<a tabindex="0" id="nc-mail-body-<?php echo Current::read('Frame.id'); ?>" data-toggle="popover" data-placement="bottom" title="<?php echo __d('mails', 'キーワードとは？'); ?>" data-content="<?php echo __d('videos', 'それぞれのキーワードは、対応する内容に変換されて送信されます。<br />{X-SITE_NAME} : サイト名称<br />{X-PLUGIN_NAME} : プラグイン名称<br />{X-ROOM} : ルーム名称<br />{X-CHANNEL_NAME} : チャンネル名<br />{X-SUBJECT} : 動画タイトル<br />{X-USER} : 投稿者<br />{X-TO_DATE} : 投稿日時<br />{X-BODY} : 登録内容<br />{X-URL} : 登録内容のURL'); ?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						<br />
						--------------------<br />
						件名と本文にはキーワードが使えます。それぞれのキーワードは、対応する内容に変換されて送信されます。<br />
						{X-SITE_NAME} : サイト名称<br />
						{X-PLUGIN_NAME} : プラグイン名称<br />
						{X-ROOM} : ルーム名称<br />
						{X-CHANNEL_NAME} : チャンネル名<br />
						{X-SUBJECT} : 動画タイトル<br />
						{X-USER} : 投稿者<br />
						{X-TO_DATE} : 投稿日時<br />
						{X-BODY} : 登録内容<br />
						{X-URL} : 登録内容のURL<br />
						--------------------<br />
						件名と本文にはキーワードが使えます。それぞれのキーワードは、対応する内容に変換されて送信されます。
						{X-SITE_NAME} : サイト名称、
						{X-PLUGIN_NAME} : プラグイン名称、
						{X-ROOM} : ルーム名称、
						{X-CHANNEL_NAME} : チャンネル名、
						{X-SUBJECT} : 動画タイトル、
						{X-USER} : 投稿者、
						{X-TO_DATE} : 投稿日時、
						{X-BODY} : 登録内容、
						{X-URL} : 登録内容のURL
					</p>
				</div>
				<script>
					$(function() {
						$('#nc-mail-body-<?php echo Current::read('Frame.id'); ?>').popover({
							html: true
						});
					});
				</script>
			</div>
		</div>

		<div class="panel-footer text-center">
			<?php echo $this->Button->cancelAndSave(__d('net_commons', 'Cancel'), __d('net_commons', 'OK'), $cancelUrl); ?>
		</div>
	</div>
<?php echo $this->NetCommonsForm->end();
