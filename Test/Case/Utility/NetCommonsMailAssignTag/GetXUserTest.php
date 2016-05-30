<?php
/**
 * NetCommonsMailAssignTag::getXUser()のテスト
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
 * NetCommonsMailAssignTag::getXUser()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\NetCommonsMailAssignTag
 */
class MailsUtilityNetCommonsMailAssignTagGetXUserTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.user_attributes.user_role_setting4test',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * getXUser()のテスト
 *
 * @return void
 */
	public function testGetXUser() {
		//データ生成
		$createdUserId = 1;
		$mailAssignTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $mailAssignTag->getXUser($createdUserId);

		//チェック
		//debug($result);
		$this->assertEquals('X-USER', $result[0]);
		$this->assertNotEmpty($result[1]);
	}

/**
 * getXUser()のテスト - user_id空
 *
 * @return void
 */
	public function testGetXUserEmptyUserId() {
		//データ生成
		$createdUserId = null;
		$mailAssignTag = new NetCommonsExtentionTag();

		//テスト実施
		$result = $mailAssignTag->getXUser($createdUserId);

		//チェック
		//debug($result);
		$this->assertEquals('X-USER', $result[0]);
		$this->assertNotEmpty($result[1]);
	}

}
