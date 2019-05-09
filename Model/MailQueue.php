<?php
/**
 * MailQueue Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailsAppModel', 'Mails.Model');
App::uses('Block', 'Blocks.Model');

/**
 * メールキュー
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model
 */
class MailQueue extends MailsAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * beforeValidate
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/ja/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate, array(
			'plugin_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'reply_to' => array(
				'email' => array(
					// 2ループ以上すると Validation.php の $regex に別項目の validateチェックの $regex
					// が代入されて下記エラーになったので、明示的に初期化
					//   preg_match(): Delimiter must not be alphanumeric or backslash
					//   /var/www/app/vendors/cakephp/cakephp/lib/Cake/Utility/Validation.php : 880
					'rule' => array('email', false, null),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => true,
				),
			),
			'mail_subject' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'mail_body' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			// 暫定コメントアウト対応：datetimeのvalidateがコア側でエラーになる。セットした値：'send_time' => string '2016-03-20 15:37:12' (length=19)
			// 'rule' => array('datetime') => Notice (8): Undefined index: datetime [CORE/Cake/Utility/Validation.php, line 361]
			// 'rule' => array('datetime', 'ymd') => Warning (2): preg_match(): Delimiter must not be alphanumeric or backslash [CORE/Cake/Utility/Validation.php, line 880]
			//			'send_time' => array(
			//				'datetime' => array(
			//					'rule' => array('datetime'),
			//					//'rule' => array('datetime', 'ymd'),
			//					'message' => __d('net_commons', 'Invalid request.'),
			//					'required' => true,
			//				),
			//			),
		));

		return parent::beforeValidate($options);
	}

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Mails.MailQueueUser',
	);

/**
 * save時のメール送信日時 ゲット
 *
 * @param string $sendTime 送信日時
 * @return string 送信日時
 */
	public function getSaveSendTime($sendTime = null) {
		if (empty($sendTime)) {
			$sendTime = NetCommonsTime::getNowDatetime();
		}
		return $sendTime;
	}

/**
 * メールキューデータ保存 - （メール生文を）
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveMailQueue($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			$this->rollback();
			return false;
		}

		try {
			// 保存
			if (! $mailQueue = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $mailQueue;
	}

/**
 * ブロック状態によってメール送る、送らないチェック
 * （ブロック非公開、期間限定チェック）
 *
 * @param array $block ブロックデータ
 * @param string $alias ブロックデータのテーブル別名
 * @return bool
 */
	public function isSendBlockType($block, $alias = 'Block.') {
		if (!$block) {
			// 空はメール送る
			return true;
		}

		$publicType = Hash::get($block, $alias . 'public_type');

		if ($publicType == Block::TYPE_PUBLIC) {
			// ブロック公開はメール送る
			return true;
		}

		// ブロック非公開はメール送らない
		if ($publicType === Block::TYPE_PRIVATE) {
			CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
			return false;
		}

		$publishStart = Hash::get($block, $alias . 'publish_start');
		$publishEnd = Hash::get($block, $alias . 'publish_end');

		$now = NetCommonsTime::getNowDatetime();

		// ブロック期間限定で期間外はメール送らない
		if ($publicType == Block::TYPE_LIMITED) {
			// 開始日=空はチェックしない
			if ($publishStart && strtotime($now) < strtotime($publishStart)) {
				CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				return false;
			}
			// 終了日=空はチェックしない
			if ($publishEnd && strtotime($publishEnd) < strtotime($now)) {
				CakeLog::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
				return false;
			}
		}

		return true;
	}

/**
 * メール送信日時で送るかどうか
 *
 * @param date $sendTime メール送信日時
 * @param int $useReminder リマインダーを使うか
 * @return bool
 */
	public function isMailSendTime($sendTime, $useReminder) {
		if ($sendTime === null) {
			return true;
		}

		// SiteSettingからメール設定を取得する
		$useCron = SiteSettingUtil::read('Mail.use_cron');
		$now = NetCommonsTime::getNowDatetime();

		// クーロンが使えなくて未来日なら、未来日メールなので送らない
		if (empty($useCron) && strtotime($now) < strtotime($sendTime)) {
			return false;
		}

		//$useReminder = $this->settings[$model->alias]['reminder']['useReminder'];
		if (! $useReminder) {
			return true;
		}

		// リマインダーで日時が過ぎてたら、メール送らない
		if (strtotime($now) > strtotime($sendTime)) {
			return false;
		}

		return true;
	}
}
