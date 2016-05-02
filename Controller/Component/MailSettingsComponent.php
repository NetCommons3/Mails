<?php
/**
 * MailSettings Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');

/**
 * MailSettings Component
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Mails\Controller\Component
 */
class MailSettingsComponent extends Component {

/**
 * パーミッション
 *
 * #### サンプルコード
 * ##### Controller
 * ```
 * public $components = array(
 *	'Mails.MailSettings' => array(
 * 		'permission' => array('mail_content_receivable')
 *	)
 * ```
 * デフォルト：array('mail_content_receivable')
 *
 * @var array
 */
	public $permission = array('mail_content_receivable', 'mail_answer_receivable');

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::startup
 */
	public function startup(Controller $controller) {
		$permissions = $controller->Workflow->getBlockRolePermissions($this->permission);
		$controller->set('permissions', $permissions);
		$controller->set('roles', $controller->viewVars['permissions']['Roles']);
	}
}
