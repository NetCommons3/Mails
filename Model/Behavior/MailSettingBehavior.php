<?php
/**
 * Mail Setting Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * Mail Setting Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
 */
class MailSettingBehavior extends ModelBehavior {

/**
 * @var bool 削除済みか
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
 * @param Model $model Model using this behavior
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

		// コンテンツ取得
		$content = $model->find('first', array(
			'conditions' => array($model->alias . '.id' => $model->id)
		));

		$model->loadModels([
			'MailSetting' => 'Mails.MailSetting',
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
		]);

		// キューの配信先 削除
		$conditions = array(
			$model->MailQueueUser->alias . '.block_key' => $content[$model->alias]['key']
		);
		if (! $model->MailQueueUser->deleteAll($conditions, false)) {
			throw new InternalErrorException('Failed - MailQueueUser ' . __METHOD__);
		}

		// キュー 削除
		$conditions = array($model->MailQueue->alias . '.block_key' => $content[$model->alias]['key']);
		if (! $model->MailQueue->deleteAll($conditions, false)) {
			throw new InternalErrorException('Failed - MailQueue ' . __METHOD__);
		}

		// メール設定 削除
		$conditions = array($model->MailSetting->alias . '.block_key' => $content[$model->alias]['key']);
		if (! $model->MailSetting->deleteAll($conditions, false)) {
			throw new InternalErrorException('Failed - MailSetting ' . __METHOD__);
		}

		$this->__isDeleted = true;
		return true;
	}
}
