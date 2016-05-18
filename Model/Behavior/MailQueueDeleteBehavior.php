<?php
/**
 * メールキュー削除 Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * メールキュー削除 Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
 */
class MailQueueDeleteBehavior extends ModelBehavior {

/**
 * @var bool 削除済み
 */
	private $__isDeleted = null;

/**
 * setup
 *
 * @param Model $model モデル
 * @param array $settings 設定値
 * @return void
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::setup
 */
	public function setup(Model $model, $settings = array()) {
		$this->settings[$model->alias] = $settings;
		$this->__isDeleted = false;
	}

/**
 * beforeDelete
 * コンテンツが削除されたら、キューに残っているメールも削除
 *
 * @param Model $model モデル
 * @param bool $cascade If true records that depend on this record will also be deleted
 * @return mixed False if the operation should abort. Any other result will continue.
 * @throws InternalErrorException
 * @link http://book.cakephp.org/2.0/ja/models/behaviors.html#ModelBehavior::beforedelete
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforedelete
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function beforeDelete(Model $model, $cascade = true) {
		// 多言語のコンテンツを key を使って、Model::deleteAll() で削除した場合を想定
		// 削除済みなら、もう処理をしない
		if ($this->__isDeleted) {
			return;
		}

		// コンテンツキー取得
		$content = $model->find('first', array(
			'recursive' => -1,
			'conditions' => array($model->alias . '.id' => $model->id),
			'callbacks' => false,
		));
		$contentKey = $content[$model->alias]['key'];

		$this->deleteQueue($model, $contentKey);

		$this->__isDeleted = true;
		return true;
	}

/**
 * キュー削除
 *
 * @param Model $model モデル
 * @param string $value 削除条件の値
 * @param string $deleteColum 削除カラム
 * @return void
 * @throws InternalErrorException
 */
	public function deleteQueue(Model $model, $value, $deleteColum = 'content_key') {
		$model->loadModels([
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
		]);

		// キューの配信先 削除
		$conditions = array($model->MailQueueUser->alias . '.' . $deleteColum => $value);
		if (! $model->MailQueueUser->deleteAll($conditions, false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// キュー 削除
		$conditions = array($model->MailQueue->alias . '.' . $deleteColum => $value);
		if (! $model->MailQueue->deleteAll($conditions, false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}
}
