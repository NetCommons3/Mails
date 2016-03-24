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
	);

/**
 * メール設定 登録,編集
 *
 * @return mixed
 */
	public function edit() {
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->MailSetting->saveMailSetting($this->request->data)) {
				$this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
				return;
			}
			$this->NetCommons->handleValidationError($this->MailSetting->validationErrors);
			$this->request->data['BlockRolePermission'] = Hash::merge(
				$this->viewVars['permissions']['BlockRolePermissions'],
				$this->request->data['BlockRolePermission']
			);
		} else {
			$mailSetting = $this->MailSetting->getMailSettingPlugin();
			$this->request->data['MailSetting'] = $mailSetting['MailSetting'];
			$this->request->data['BlockRolePermission'] = $this->viewVars['permissions']['BlockRolePermissions'];
			$this->request->data['Frame'] = Current::read('Frame');
		}
	}

}
