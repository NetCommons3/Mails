<?php
/**
 * NetCommonsMailAssignTag::getFixedPhraseType()のテスト
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
 * NetCommonsMailAssignTag::getFixedPhraseType()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsMailAssignTagGetFixedPhraseTypeTest extends NetCommonsCakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * getFixedPhraseType()のテスト - 公開
 *
 * @return void
 */
	public function testGetFixedPhraseTypePublished() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = WorkflowComponent::STATUS_PUBLISHED;

		//テスト実施
		$result = $mailAssignTag->getFixedPhraseType($status);

		//チェック
		//debug($result);
		$expected = NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION;
		$this->assertEquals($expected, $result);
	}

/**
 * getFixedPhraseType()のテスト - 承認依頼
 *
 * @return void
 */
	public function testGetFixedPhraseTypeApproval() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = WorkflowComponent::STATUS_APPROVED;

		//テスト実施
		$result = $mailAssignTag->getFixedPhraseType($status);

		//チェック
		//debug($result);
		$expected = NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL;
		$this->assertEquals($expected, $result);
	}

/**
 * getFixedPhraseType()のテスト - 差し戻し
 *
 * @return void
 */
	public function testGetFixedPhraseTypeDisapproval() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = WorkflowComponent::STATUS_DISAPPROVED;

		//テスト実施
		$result = $mailAssignTag->getFixedPhraseType($status);

		//チェック
		//debug($result);
		$expected = NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL;
		$this->assertEquals($expected, $result);
	}

/**
 * getFixedPhraseType()のテスト - そのほか
 *
 * @return void
 */
	public function testGetFixedPhraseTypeEtc() {
		//データ生成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = 'xxx';

		//テスト実施
		$result = $mailAssignTag->getFixedPhraseType($status);

		//チェック
		//debug($result);
		$this->assertNull($result);
	}

}
