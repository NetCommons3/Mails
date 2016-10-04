<?php
/**
 * MailQueue::isSendBlockType()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');
App::uses('Block', 'Blocks.Model');

/**
 * MailQueue::isSendBlockType()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Model\MailQueue
 */
class MailQueueIsSendBlockTypeTest extends NetCommonsGetTest {

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
	protected $_modelName = 'MailQueue';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'isSendBlockType';

/**
 * isSendBlockType()の空テスト
 *
 * @return void
 */
	public function testIsSendBlockTypeEmpty() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = null;
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertTrue($result);
	}

/**
 * isSendBlockType()のブロック公開テスト
 *
 * @return void
 */
	public function testIsSendBlockTypePublic() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = array(
			'Block' => array(
				'public_type' => Block::TYPE_PUBLIC,
			)
		);
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertTrue($result);
	}

/**
 * isSendBlockType()のブロック非公開テスト
 *
 * @return void
 */
	public function testIsSendBlockTypePrivate() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = array(
			'Block' => array(
				'public_type' => Block::TYPE_PRIVATE,
			)
		);
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertFalse($result);
	}

/**
 * isSendBlockType()のブロック期限付き公開で空テスト
 *
 * @return void
 */
	public function testIsSendBlockTypeLimitedEmpty() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = array(
			'Block' => array(
				'public_type' => Block::TYPE_LIMITED,
				'publish_start' => '',
				'publish_end' => '',
			)
		);
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertTrue($result);
	}

/**
 * isSendBlockType()のブロック期限付き公開で開始日到達前テスト
 *
 * @return void
 */
	public function testIsSendBlockTypeLimitedStart() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = array(
			'Block' => array(
				'public_type' => Block::TYPE_LIMITED,
				'publish_start' => '2099-09-13 05:00:00',
				'publish_end' => '',
			)
		);
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertFalse($result);
	}

/**
 * isSendBlockType()のブロック期限付き公開で終了日到達テスト
 *
 * @return void
 */
	public function testIsSendBlockTypeLimitedEnd() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		Current::$current['Plugin']['key'] = 'dummy';

		//データ生成
		$block = array(
			'Block' => array(
				'public_type' => Block::TYPE_LIMITED,
				'publish_start' => '',
				'publish_end' => '2000-09-13 05:00:00',
			)
		);
		$alias = 'Block.';

		//テスト実施
		/** @see MailQueue::isSendBlockType() */
		$result = $this->$model->$methodName($block, $alias);

		//チェック
		//debug($result);
		$this->assertFalse($result);
	}

}
