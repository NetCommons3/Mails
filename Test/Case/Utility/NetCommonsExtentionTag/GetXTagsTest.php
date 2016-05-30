<?php
/**
 * NetCommonsExtentionTag::getXTags()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsExtentionTag', 'Mails.Utility');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');

/**
 * NetCommonsExtentionTag::getXTags()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsExtentionTagGetXTagsTest extends NetCommonsCakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * getXTags()のテスト
 *
 * @return void
 */
	public function testGetXTags() {
		//データ生成
		$tag1 = '犬';
		$tag2 = '動物';
		$data = array(
			'Tag' => array(
				array('name' => $tag1),
				array('name' => $tag2),
			)
		);
		$workflowType = MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW;
		$useTagBehavior = 1;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXTags($data, $workflowType, $useTagBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-TAGS', $result[0], 'X-TAGSなし');
		$this->assertTextContains($tag1, $result[1],
			'X-TAGSにセットした値なし:' . $tag1);
		$this->assertTextContains($tag2, $result[1],
			'X-TAGSにセットした値なし:' . $tag2);
	}

/**
 * getXTags()のテスト - $workflowType その他 - タグ空
 *
 * @return void
 */
	public function testGetXTagsWorkflowTypeEtc() {
		//データ生成
		$data = null;
		$workflowType = null;
		$useTagBehavior = 1;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXTags($data, $workflowType, $useTagBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-TAGS', $result[0], 'X-TAGSなし');
		$this->assertEmpty($result[1], '空のはずのX-TAGSに値あり');
	}

/**
 * getXTags()のテスト - タグ空
 *
 * @return void
 */
	public function testGetXTagsEmpty() {
		//データ生成
		$data = null;
		$workflowType = null;
		$useTagBehavior = null;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXTags($data, $workflowType, $useTagBehavior);

		//チェック
		//debug($result);
		$this->assertEquals('X-TAGS', $result[0], 'X-TAGSなし');
		$this->assertEmpty($result[1], '空のはずのX-TAGSに値あり');
	}
}
