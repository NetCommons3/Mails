<?php
/**
 * MailFormHelper::editFrom()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsHelperTestCase', 'NetCommons.TestSuite');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ComponentCollection', 'Controller');

/**
 * MailFormHelper::editFrom()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\View\Helper\MailFormHelper
 */
class MailFormHelperEditFromTest extends NetCommonsHelperTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.rooms.default_role_permission4test',
		'plugin.rooms.room_role',
		//'plugin.rooms.room_role_permission4test',
		'plugin.mails.room_role_permission_for_mail',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストデータ生成
		$roomId = 1;
		$blockKey = null;
		$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
		$permissions = $WorkflowComponent->getBlockRolePermissions(array('mail_content_receivable'),
			$roomId, $blockKey);

		$viewVars = array(
			'roles' => $permissions['Roles'],
		);
		$requestData = array();
		$params = array();

		//Helperロード
		$this->loadHelper('Mails.MailForm', $viewVars, $requestData, $params);

		//BlockRolePermissionFormロード
		$this->MailForm->_View->Helpers->load('Blocks.BlockRolePermissionForm');
	}

/**
 * editFrom()のテスト
 *
 * @return void
 */
	public function testEditFrom() {
		//データ生成
		$editForms = array();
		$cancelUrl = null;
		$useReplyTo = 1;
		$isMailSendHelp = 1;
		$useMailSendApproval = 1;
		$options = array();
		$action = null;

		//テスト実施
		$result = $this->MailForm->editFrom($editForms, $cancelUrl, $useReplyTo,
			$isMailSendHelp, $useMailSendApproval, $options, $action);

		//チェック
		//debug($result);
		$this->assertTextContains(__d('mails', 'Posting mail'), $result);
	}

/**
 * editFrom()の定型文複数テスト
 *
 * @return void
 */
	public function testEditFroms() {
		//データ生成
		$editForms = array(
			array(
				'useNoticeAuthority' => 0, //OFF
			),
			array(
				'useNoticeAuthority' => 0, //OFF
			),
		);
		$cancelUrl = null;
		$useReplyTo = 0;
		$isMailSendHelp = 0;
		$useMailSendApproval = 1;
		$options = array();
		$action = 'http://localhost';

		//テスト実施
		$result = $this->MailForm->editFrom($editForms, $cancelUrl, $useReplyTo,
			$isMailSendHelp, $useMailSendApproval, $options, $action);

		//チェック
		//debug($result);
		$this->assertTextContains(__d('mails', 'Posting mail'), $result);
		$this->assertTextContains(__d('mails', 'Answer mail'), $result);
	}
}
