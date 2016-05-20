<?php
/**
 * MailQueueUser::addMailQueueUsers()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('MailQueueUserFixture', 'Mails.Test/Fixture');

/**
 * MailQueueUser::addMailQueueUsers()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueueUser
 */
class MailQueueUserAddMailQueueUsersTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'MailQueueUser';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'addMailQueueUsers';

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$data['MailQueueUser'] = (new MailQueueUserFixture())->records[0];

		$results = array();
		// * 新規の登録処理
		$results[0] = array($data, 'user_id', [3, 4]);
		$results[0] = Hash::insert($results[0], '0.MailQueueUser.id', null);
		$results[0] = Hash::remove($results[0], '0.MailQueueUser.created_user');
		$results[0] = Hash::remove($results[0], '0.MailQueueUser.user_id');

		return $results;
	}

/**
 * Saveのテスト
 *
 * @param array $data 登録データ
 * @param string $filed 一部だけ変更するフィールド
 * @param array $values 一部だけ変更するフィールドの値(配列)
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($data, $filed, $values) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model->$method($data, $filed, $values);

		$result = $this->$model->find('all', array(
			'recursive' => -1,
			'conditions' => array($filed => $values),
		));

		//debug($result);
		$this->assertCount(count($values), $result);
	}

/**
 * SaveのExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnExceptionError() {
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Mails.MailQueueUser', 'validates', 'user_id', [3, 4]),
		);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param string $filed 一部だけ変更するフィールド
 * @param array $values 一部だけ変更するフィールドの値(配列)
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($data, $mockModel, $mockMethod, $filed, $values) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($data, $filed, $values);
	}

}
