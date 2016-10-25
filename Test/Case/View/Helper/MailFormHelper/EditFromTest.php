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
		// travis-ci上での Undefined index: type エラー対応
		//		/home/travis/build/NetCommons3/NetCommons3/app/Plugin/NetCommons/Utility/Current.php:512
		//		/home/travis/build/NetCommons3/NetCommons3/app/Plugin/NetCommons/Utility/NetCommonsUrl.php:35
		//		/home/travis/build/NetCommons3/NetCommons3/app/Plugin/NetCommons/View/Helper/ButtonHelper.php:231
		//		/home/travis/build/NetCommons3/NetCommons3/app/Plugin/Mails/View/Elements/edit_form.ctp:121
		Current::write('Plugin.type', '');

		$roomId = '2';
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
		$useMailSend = 1;
		$options = array();
		$action = null;

		//テスト実施
		/** @see MailFormHelper::editFrom() */
		$result = $this->MailForm->editFrom($editForms, $cancelUrl, $useReplyTo,
			$isMailSendHelp, $useMailSendApproval, $useMailSend, $options, $action);

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
		$useMailSend = 1;
		$options = array();
		$action = 'http://localhost';

		//テスト実施
		/** @see MailFormHelper::editFrom() */
		$result = $this->MailForm->editFrom($editForms, $cancelUrl, $useReplyTo,
			$isMailSendHelp, $useMailSendApproval, $useMailSend, $options, $action);

		//チェック
		//debug($result);
		$this->assertTextContains(__d('mails', 'Posting mail'), $result);
		$this->assertTextContains(__d('mails', 'Answer mail'), $result);
	}

/**
 * editFrom()のメール承認を使うのみ表示テスト
 *
 * @return void
 */
	public function testUseMailSendApprovalOnly() {
		//データ生成
		$editForms = array();
		$cancelUrl = null;
		$useReplyTo = 0;
		$isMailSendHelp = 0;
		$useMailSendApproval = 1;
		$useMailSend = 0;	// 非表示
		$options = array();
		$action = 'http://localhost';

		//テスト実施
		/** @see MailFormHelper::editFrom() */
		$result = $this->MailForm->editFrom($editForms, $cancelUrl, $useReplyTo,
			$isMailSendHelp, $useMailSendApproval, $useMailSend, $options, $action);

		//チェック
		//debug($result);
		$this->assertTextNotContains(__d('mails', 'Posting mail'), $result);
		$this->assertTextNotContains(__d('mails', 'Answer mail'), $result);
		$this->assertTextContains(__d('mails', 'Use the approval mail notification function'), $result);
	}
}
