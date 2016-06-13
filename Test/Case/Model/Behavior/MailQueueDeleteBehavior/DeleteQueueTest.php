<?php
/**
 * MailQueueDeleteBehavior::deleteQueue()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * MailQueueDeleteBehavior::deleteQueue()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\Behavior\MailQueueDeleteBehavior
 */
class MailQueueDeleteBehaviorDeleteQueueTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.mails.mail_queue',
		'plugin.mails.mail_queue_user',
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

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Mails', 'TestMails');
		$this->TestModel = ClassRegistry::init('TestMails.TestMailQueueDeleteBehaviorModel');
	}

/**
 * deleteQueue()テストのDataProvider
 *
 * ### 戻り値
 *  - value 削除条件の値
 *  - deleteColum 削除カラム
 *
 * @return array データ
 */
	public function dataProvider() {
		$result[0] = array();
		$result[0]['value'] = 'content_1';
		$result[0]['deleteColum'] = 'content_key';

		return $result;
	}

/**
 * deleteQueue()のテスト
 *
 * @param string $value 削除条件の値
 * @param string $deleteColum 削除カラム
 * @dataProvider dataProvider
 * @return void
 */
	public function testDeleteQueue($value, $deleteColum) {
		//テスト実施
		$this->TestModel->deleteQueue($value, $deleteColum);

		//チェック
		//debug($result);
	}

/**
 * deleteQueue()例外テストのDataProvider
 *
 * ### 戻り値
 *  - value 削除条件の値
 *  - deleteColum 削除カラム
 *  - mockModel Mockのモデル
 *
 * @return array データ
 */
	public function dataProviderExeption() {
		$result[0] = array();
		$result[0]['value'] = 'content_1';
		$result[0]['deleteColum'] = 'content_key';
		$result[0]['mockModel'] = 'MailQueue.MailQueue';
		$result[1]['value'] = 'content_1';
		$result[1]['deleteColum'] = 'content_key';
		$result[1]['mockModel'] = 'Mails.MailQueueUser';

		return $result;
	}

/**
 * deleteQueue()の例外テスト
 *
 * @param string $value 削除条件の値
 * @param string $deleteColum 削除カラム
 * @param string $mockModel Mockのモデル
 * @dataProvider dataProviderExeption
 * @return void
 */
	public function testDeleteQueueOnExeptionError($value, $deleteColum, $mockModel) {
		//テストデータ
		$this->_mockForReturnFalse('TestModel', $mockModel, 'deleteAll');

		//テスト実施
		$this->setExpectedException('InternalErrorException');
		$this->TestModel->deleteQueue($value, $deleteColum);
	}
}
