<?php
/**
 * NetCommonsExtentionTag::getXWorkflowComment()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsExtentionTag', 'Mails.Utility');
App::uses('NetCommonsMailAssignTag', 'Mails.Utility');

/**
 * NetCommonsExtentionTag::getXWorkflowComment()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsExtentionTagGetXWorkflowCommentTest extends NetCommonsCakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * getXWorkflowComment()のテスト
 *
 * @return void
 */
	public function testGetXWorkflowComment() {
		//データ生成
		$comment = '承認お願いします';
		$data = array(
			'WorkflowComment' => array(
				'comment' => $comment,
			)
		);
		$fixedPhraseType = NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL;
		$useWorkflowBehavior = 1;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXWorkflowComment($data, $fixedPhraseType, $useWorkflowBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-WORKFLOW_COMMENT', $result[0], 'X-WORKFLOW_COMMENTなし');
		$resultComment = explode("\n", $result[1]);
		$this->assertTextContains($resultComment[1], $comment, 'セットしたコメントが含まれていない');
	}

/**
 * getXWorkflowComment()のテスト - コメント空
 *
 * @return void
 */
	public function testGetXWorkflowCommentEmpty() {
		//データ生成
		$data = null;
		$fixedPhraseType = null;
		$useWorkflowBehavior = null;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXWorkflowComment($data, $fixedPhraseType, $useWorkflowBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-WORKFLOW_COMMENT', $result[0], 'X-WORKFLOW_COMMENTなし');
		$this->assertEmpty($result[1], '空のはずのX-WORKFLOW_COMMENTに値あり');
	}

/**
 * getXWorkflowComment()のテスト - $fixedPhraseType その他 - コメント空
 *
 * @return void
 */
	public function testGetXWorkflowCommentFixedPhraseTypeEtc() {
		//データ生成
		$data = null;
		$fixedPhraseType = 'xxx';
		$useWorkflowBehavior = 1;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXWorkflowComment($data, $fixedPhraseType, $useWorkflowBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-WORKFLOW_COMMENT', $result[0], 'X-WORKFLOW_COMMENTなし');
		$this->assertEmpty($result[1], '空のはずのX-WORKFLOW_COMMENTに値あり');
	}

}
