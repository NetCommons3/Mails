<?php
/**
 * メール設定 Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * メール設定 Controller
 *
 * 各プラグインで継承して使用する
 *
 * #### サンプルコード
 * ##### Controller
 * ```
 * class LinkMailSettingsController extends MailSettingsController {
 *
 * }
 * ```
 * ##### View
 * ```
 * Links/View/LinkMailSettings/edit.ctpファイルを作成する
 * ```
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Mails\Controller
 * @property MailSetting $MailSetting
 * @property MailSettingFixedPhrase $MailSettingFixedPhrase
 */
class MailSettingsController extends AppController {

/**
 * 使用レイアウト
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * 使用コンポーネントの定義
 *
 * @var array
 */
	public $components = array(
		'Mails.MailSettings',
		'NetCommons.Permission' => array(
			'allow' => array(
				'edit' => 'mail_editable',
			),
		),
		'Pages.PageLayout',
		'Security',
	);

/**
 * 使用モデルの定義
 *
 * @var array
 */
	public $uses = array(
		'Mails.MailSetting',
		'Mails.MailSettingFixedPhrase',
	);

/**
 * メール設定 登録,編集
 *
 * @return mixed
 */
	public function edit() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$mailSetting = $this->MailSetting->saveMailSetting($this->request->data);

			$data['MailSettingFixedPhrase'] = $this->request->data['MailSettingFixedPhrase'];
			$data['MailSettingFixedPhrase']['mail_setting_id'] = $mailSetting['MailSetting']['id'];
			$mailFixedPhrase = $this->MailSettingFixedPhrase->saveMailSettingFixedPhrase($data);

			if ($mailSetting && $mailFixedPhrase) {
				$this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
				return;
			}
			$this->NetCommons->handleValidationError($this->MailSetting->validationErrors);
			$this->NetCommons->handleValidationError($this->MailSettingFixedPhrase->validationErrors);
			$this->request->data['BlockRolePermission'] = Hash::merge(
				$this->viewVars['permissions']['BlockRolePermissions'],
				$this->request->data['BlockRolePermission']
			);
		} else {
			$mailSettingPlugin = $this->MailSetting->getMailSettingPlugin();
			$this->request->data['MailSetting'] = $mailSettingPlugin['MailSetting'];
			$this->request->data['MailSettingFixedPhrase'] = $mailSettingPlugin['MailSettingFixedPhrase'];
			$this->request->data['BlockRolePermission'] = $this->viewVars['permissions']['BlockRolePermissions'];
			$this->request->data['Frame'] = Current::read('Frame');
		}
	}

}
