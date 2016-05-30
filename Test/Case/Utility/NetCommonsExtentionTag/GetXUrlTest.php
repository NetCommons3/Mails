<?php
/**
 * NetCommonsExtentionTag::getXUrl()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('NetCommonsExtentionTag', 'Mails.Utility');

/**
 * NetCommonsExtentionTag::getXUrl()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsExtentionTagGetXUrlTest extends NetCommonsCakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * getXUrl()のテスト
 *
 * @return void
 */
	public function testGetXUrl() {
		//データ生成
		$contentKey = 'content_key';
		Current::$current['Plugin']['key'] = 'dummy';
		Current::$current['Block']['id'] = 1;
		Current::$current['Frame']['id'] = 2;
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXUrl($contentKey);

		//チェック
		//debug($result);
		$this->assertEquals('X-URL', $result[0], 'X-URLなし');
		$this->assertTextContains($contentKey, $result[1],
			'X-URLにcontent_key含んでいない');
		$this->assertTextContains(Current::read('Plugin.key'), $result[1],
			'X-URLにPlugin.key含んでいない');
		$this->assertTextContains('/' . Current::read('Block.id'), $result[1],
			'X-URLにBlock.id含んでいない');
		$this->assertTextContains('frame_id=' . Current::read('Frame.id'), $result[1],
			'X-URLにframe_id含んでいない');
	}

/**
 * getXUrl()のテスト - URL直設定
 *
 * @return void
 */
	public function testGetXUrlSetUrl() {
		//データ生成
		$contentKey = 'content_key';
		$url = 'http://localhost/';
		$extentionTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $extentionTag->getXUrl($contentKey, $url);

		//チェック
		//debug($result);
		$this->assertEquals('X-URL', $result[0], 'X-URLなし');
		$this->assertEquals($url, $result[1], 'セットしたURLと違う');
	}

}
