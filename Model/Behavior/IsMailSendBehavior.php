<?php
/**
 * メール送信する・しない Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');
App::uses('MailSend', 'Mails.Utility');
App::uses('BlockSettingBehavior', 'Blocks.Model/Behavior');

/**
 * メール送信する・しない Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
 */
class IsMailSendBehavior extends ModelBehavior {

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

		$model->loadModels([
			'MailSetting' => 'Mails.MailSetting',
			'MailQueue' => 'Mails.MailQueue',
			'MailQueueUser' => 'Mails.MailQueueUser',
			'SiteSetting' => 'SiteManager.SiteSetting',
			'BlockSetting' => 'Blocks.BlockSetting',
		]);
	}

/**
 * 通常の投稿メールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @param string $contentKey コンテンツキー
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @return bool
 */
	public function isMailSend(Model $model,
								$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
								$contentKey = null,
								$settingPluginKey = null) {
		if (! $this->isMailSendCommon($model, $typeKey, $settingPluginKey)) {
			return false;
		}

		// 投稿メールOFFなら、メール送らない
		$isMailSendPost = Hash::get($this->settings,
			$model->alias . '.' . MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST);
		if (isset($isMailSendPost) && $isMailSendPost == '0') {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 承認コメントありなら、編集であっても通知メールを送る
		$comment = Hash::get($model->data, 'WorkflowComment.comment');
		if ($comment) {
			return true;
		}

		// 公開許可あり（承認者、承認OFF時の一般）の編集 and 投稿メールフラグが未設定の場合、メール送らない
		// 公開記事 編集フラグ
		$isPublishableEdit = $this->isPublishableEdit($model, $contentKey);
		if ($isPublishableEdit && $isMailSendPost === null) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * リマインダーメールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @return bool
 */
	public function isMailSendReminder(Model $model,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
										$settingPluginKey = null) {
		$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];
		if (! $useReminder) {
			return false;
		}

		if (! $this->isMailSendCommon($model, $typeKey, $settingPluginKey)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// リマインダーの公開以外はメール送らない
		$status = Hash::get($model->data, $model->alias . '.status');
		if ($status != WorkflowComponent::STATUS_PUBLISHED) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// リマインダーが複数日あって、全て日時が過ぎてたら、メール送らない
		$isMailSendReminder = false;
		$sendTimeReminders = $this->settings[$model->alias]['reminder']['sendTimes'];
		foreach ($sendTimeReminders as $sendTime) {
			if ($this->isMailSendTime($model, $sendTime)) {
				$isMailSendReminder = true;
			}
		}
		if (! $isMailSendReminder) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * 公開許可あり（承認者、承認OFF時の一般）の編集か ゲット
 *
 * @param Model $model モデル
 * @param string $contentKey コンテンツキー
 * @return bool
 */
	public function isPublishableEdit(Model $model, $contentKey) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		// --- コンテンツコメント
		if ($workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			if (!Current::permission('content_comment_publishable')) {
				// 公開権限なし
				return false;
			}

			// 登録日時
			$created = Hash::get($model->data, $model->alias . '.created');
			$isApproveAction = Hash::get($this->settings, $model->alias . '.isCommentApproveAction');
			if (isset($created)) {
				// 新規登録
				return false;
			}
			if ($isApproveAction) {
				// 承認時
				return false;
			}
			return true;
		}

		// --- 通常
		if (!Current::permission('content_publishable')) {
			// 公開権限なし
			return false;
		}

		//$contentKey = $this->__getContentKey($model);
		$keyField = $this->settings[$model->alias]['keyField'];
		$conditions = array($model->alias . '.' . $keyField => $contentKey);
		$result = $model->find('all', array(
			'recursive' => -1,
			'conditions' => $conditions,
			'order' => array($model->alias . '.modified DESC'),
			'callbacks' => false,
		));

		if (count($result) <= 1) {
			// 新規登録
			return false;
		}

		// keyに対して2件以上記事がある = 編集
		// 1つ前のコンテンツのステータス
		$beforeStatus = $result[1][$model->alias]['status'];
		$status = $result[0][$model->alias]['status'];

		// 承認ONでもOFFでも、公開中の記事を編集して、公開だったら、公開の編集
		// ・承認ONで、承認者が公開中の記事を編集しても、公開許可ありの編集で、メール送らない
		// ・承認OFFで、公開中の記事を編集しても、公開許可ありの編集で、メール送らない
		// ・・公開中の記事（１つ前の記事のstatus=1）
		// ・・編集した記事が公開（status=1）
		// ※承認ONで公開中の記事を編集して、編集した記事が公開なのは、承認者だけ
		if ($beforeStatus == WorkflowComponent::STATUS_PUBLISHED &&
			$status == WorkflowComponent::STATUS_PUBLISHED) {
			// 公開の編集
			return true;
		}

		// 公開以外の編集
		return false;
	}

/**
 * メールを送るかどうか - 共通処理
 *
 * @param Model $model モデル
 * @param string $typeKey メールの種類
 * @param string $settingPluginKey 設定を取得するプラグインキー
 * @return bool
 */
	public function isMailSendCommon(Model $model,
										$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
										$settingPluginKey = null) {
		if ($settingPluginKey === null) {
			$settingPluginKey = Current::read('Plugin.key');
		}

		$from = SiteSettingUtil::read('Mail.from');

		// Fromが空ならメール未設定のため、メール送らない
		if (empty($from)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// cakeコマンドに実行権限なければ、メール送らない
		if (!MailSend::isExecutableCake()) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		/** @see MailSetting::getMailSettingPlugin() */
		$mailSettingPlugin = $model->MailSetting->getMailSettingPlugin(null, $typeKey, $settingPluginKey);
		$isMailSend = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send');
		$isMailSendApproval = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send_approval');

		// プラグイン設定でメール通知 and 承認メール通知をどちらも使わないなら、メール送らない
		if (!$isMailSend && !$isMailSendApproval) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$status = Hash::get($model->data, $model->alias . '.status');

		// 一時保存はメール送らない
		if ($status == WorkflowComponent::STATUS_IN_DRAFT) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$block = Current::read('Block');

		// ブロック非公開、期間外はメール送らない
		if (!$model->MailQueue->isSendBlockType($block, '')) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * メール送信日時で送るかどうか
 *
 * @param Model $model モデル
 * @param date $sendTime メール送信日時
 * @return bool
 */
	public function isMailSendTime(Model $model, $sendTime) {
		$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];
		/** @see MailQueue::isMailSendTime() */
		return $model->MailQueue->isMailSendTime($sendTime, $useReminder);
	}

/**
 * 承認通知メールを送るかどうか
 *
 * @param Model $model モデル
 * @param int $isMailSendApproval 承認メール通知機能を使うフラグ
 * @param int $modifiedUserId 更新ユーザID
 * @param string $pluginKey プラグインキー
 * @return bool
 */
	public function isSendMailQueueNotice(Model $model, $isMailSendApproval, $modifiedUserId,
											$pluginKey) {
		$workflowType = Hash::get($this->settings, $model->alias . '.workflowType');
		if ($workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {
			// --- ワークフロー
			// 承認しないなら、通知メール送らない
			$fieldNameApproval = BlockSettingBehavior::FIELD_USE_WORKFLOW;

		} elseif ($workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			// --- コンテンツコメント
			// コメント承認しないなら、通知メール送らない
			$fieldNameApproval = BlockSettingBehavior::FIELD_USE_COMMENT_APPROVAL;
		}
		/** @see BlockSetting::getBlockSettingValue() */
		$useApproval = $model->BlockSetting->getBlockSettingValue($fieldNameApproval, $pluginKey);
		if (! $useApproval) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 承認メール使わないなら、通知メール送らない
		if (! $isMailSendApproval) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 承認コメントありなら、承認者であっても通知メールを送る
		$comment = Hash::get($model->data, 'WorkflowComment.comment');
		if ($comment) {
			return true;
		}

		$permissionKey = $this->settings[$model->alias]['publishablePermissionKey'];

		// 投稿者がルーム内の承認者だったら、通知メール送らない
		/** @see MailQueueUser::getRolesRoomsUsersByPermission() */
		$rolesRoomsUsers = $model->MailQueueUser->getRolesRoomsUsersByPermission($permissionKey);
		$rolesRoomsUserIds = Hash::extract($rolesRoomsUsers, '{n}.RolesRoomsUser.user_id');
		if (in_array($modifiedUserId, $rolesRoomsUserIds)) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}

/**
 * 公開メールを送るかどうか
 *
 * @param Model $model モデル
 * @param string $isMailSend メール通知機能を使うフラグ
 * @param string $contentKey コンテンツキー
 * @return bool
 */
	public function isSendMailQueuePublish(Model $model, $isMailSend, $contentKey) {
		// メール送らないなら、公開メール送らない
		if (! $isMailSend) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		// 公開以外はメール送らない
		$status = Hash::get($model->data, $model->alias . '.status');
		if ($status != WorkflowComponent::STATUS_PUBLISHED) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$isMailSendPost = Hash::get($this->settings,
			$model->alias . '.' . MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST);

		// 公開メールだけども、編集時にもここを通るようになったので、編集チェックを追加
		// ・公開許可あり（承認者、承認OFF時の一般）の編集 and 投稿メールフラグが未設定の場合、メール送らない
		// ・公開記事 編集フラグ
		$isPublishableEdit = $this->isPublishableEdit($model, $contentKey);
		if ($isPublishableEdit && $isMailSendPost === null) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		return true;
	}
}
