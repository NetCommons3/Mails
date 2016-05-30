<?php
/**
 * NetCommonsMailAssignTag::setMailFixedPhraseSiteSetting()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsMailAssignTag', 'Mails.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * NetCommonsMailAssignTag::setMailFixedPhraseSiteSetting()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsMailAssignTagSetMailFixedPhraseSiteSettingTest extends
	NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_setting',
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.mails.site_setting_for_mail',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->MailSetting = ClassRegistry::init('Mails.MailSetting', true);
	}

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * setMailFixedPhraseSiteSetting()のテスト
 *
 * @return void
 */
	public function testSetMailFixedPhraseSiteSetting() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$languageId = 2;
		$status = WorkflowComponent::STATUS_APPROVED;
		$fixedPhraseType = $mailAssignTag->getFixedPhraseType($status);

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingPlugin = $this->MailSetting->getMailSettingPlugin($languageId);

		//テスト実施
		$mailAssignTag->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType,
			$mailSettingPlugin);

		//チェック
		//debug($mailAssignTag->fixedPhraseSubject);
		//debug($mailAssignTag->fixedPhraseBody);
		//debug($mailAssignTag->assignTags);
		$this->assertNotEmpty($mailAssignTag->fixedPhraseSubject, '件名取得できず');
		$this->assertNotEmpty($mailAssignTag->fixedPhraseBody, '本文取得できず');
		$this->assertArrayHasKey('X-PLUGIN_MAIL_SUBJECT', $mailAssignTag->assignTags,
			'埋め込みタグ X-PLUGIN_MAIL_SUBJECTなし');
		$this->assertArrayHasKey('X-PLUGIN_MAIL_BODY', $mailAssignTag->assignTags,
			'埋め込みタグ X-PLUGIN_MAIL_BODYなし');
	}

/**
 * setMailFixedPhraseSiteSetting()のテスト - $mailSettingPlugin なし
 *
 * @return void
 */
	public function testSetMailFixedPhraseSiteSettingNoMailSettingPlugin() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$languageId = 2;
		$status = WorkflowComponent::STATUS_APPROVED;
		$fixedPhraseType = $mailAssignTag->getFixedPhraseType($status);
		$mailSettingPlugin = null;

		//テスト実施
		$mailAssignTag->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType,
			$mailSettingPlugin);

		//チェック
		//debug($mailAssignTag->fixedPhraseSubject);
		//debug($mailAssignTag->fixedPhraseBody);
		$this->assertNotEmpty($mailAssignTag->fixedPhraseSubject, '件名取得できず');
		$this->assertNotEmpty($mailAssignTag->fixedPhraseBody, '本文取得できず');
	}
}
