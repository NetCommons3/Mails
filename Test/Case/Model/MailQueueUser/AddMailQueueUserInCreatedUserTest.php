<?php
/**
 * MailQueueUser::addMailQueueUserInCreatedUser()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * MailQueueUser::addMailQueueUserInCreatedUser()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueueUser
 */
class MailQueueUserAddMailQueueUserInCreatedUserTest extends NetCommonsModelTestCase {

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
	protected $_methodName = 'addMailQueueUserInCreatedUser';

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$results = array();
		$results['登録'] = array(1, 5, 'content_key', null, 1);
		$results['createdUserId:空'] = array(1, null, 'content_key', null, 0);

		return $results;
	}

/**
 * Saveのテスト
 *
 * @param int $mailQueueId メールキューID
 * @param Model $createdUserId 登録ユーザID
 * @param string $contentKey 各プラグイン側のコンテンツのキー
 * @param string $pluginKey プラグインキー
 * @param int $expectedCount 想定の登録件数
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($mailQueueId, $createdUserId, $contentKey,
								$pluginKey = null, $expectedCount = 0) {
		$model = $this->_modelName;
		$method = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';
		Current::$current['Block']['key'] = 'block_key999';

		$this->$model->$method($mailQueueId, $createdUserId, $contentKey,
			$pluginKey);

		$result = $this->$model->find('all', array(
			'recursive' => -1,
			'conditions' => array('block_key' => Current::read('Block.key')),
		));

		//debug($result);
		$this->assertCount($expectedCount, $result);
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
		$result = $this->dataProviderSave()['登録'];

		$mailQueueId = $result[0];
		$createdUserId = $result[1];
		$contentKey = $result[2];

		return array(
			array('Mails.MailQueueUser', 'validates', $mailQueueId, $createdUserId, $contentKey),
		);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param int $mailQueueId メールキューID
 * @param Model $createdUserId 登録ユーザID
 * @param string $contentKey 各プラグイン側のコンテンツのキー
 * @param string $pluginKey プラグインキー
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($mockModel, $mockMethod, $mailQueueId, $createdUserId, $contentKey,
												$pluginKey = null) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($mailQueueId, $createdUserId, $contentKey,
			$pluginKey);
	}

}
