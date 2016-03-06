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

/**
 * Summary for Mail Setting Behavior
 */
class MailSettingBehavior extends ModelBehavior {

///**
// * afterSave is called after a model is saved.
// *
// * @param Model $model Model using this behavior
// * @param bool $created True if this save created a new record
// * @param array $options Options passed from Model::save().
// * @return bool
// * @see Model::save()
// */
//	public function afterSave(Model $model, $created, $options = array()) {
//	}

/**
 * 複数人の送信先ユーザ取得する
 * ※まだ決められない実装
 */
	public function setSendMailUsers(Model $model, $blockKey) {
  		// blockeyをセットしたら、複数人を取得して、セットするまでやる。
		//$users = $this->getSendMailUsers($wwww, $zzzz);
		//$this->setSendMailUsers($blockKey);
	}

///**
// * メール送信する定型文をセットする
// */
//	public function setSendMailSetting(Model $model, $blockKey = null, $typeKey = 'contents') {
//		// $pluginKey = $Model->plugin();
//		// 'plugin_key' => Inflector::underscore($model->plugin),
//		$pluginKey = Inflector::underscore($model->plugin);
//
//		// 定型文を取得する
//		if (!empty($blockKey)) {
//			// 通常のプラグインはこちら
//			$mailSetting = $this->getMailSettingPlugin($blockKey, $typeKey);
//		} else {
//			// システム管理系はこちら
//			$mailSetting = $this->getMailSettingSystem($pluginKey, $typeKey);
//		}
//
//		// メール通知フラグをセットする
//		$this->setIsMailSend($mailSetting['mail_setting']['is_mail_send']);
//
//		// 通知する場合
//		if ($this->getIsMailSend()) {
//
//			// ブロックキー、プラグインキーをセットする
//			$this->setMailSettingBlockKey($mailSetting['mail_setting']['block_key']);
//			$this->setMailSettingPlaginKey($mailSetting['mail_setting']['plugin_key']);
//
//			// 定型文をセットする
//			$this->setMailSubject($mailSetting['mail_setting']['mail_fixed_phrase_subject']);
//			$this->setMailBody($mailSetting['mail_setting']['mail_fixed_phrase_body']);
//
//			// 返信先アドレスをセットする
//			$this->setMailReplayTo($mailSetting['mail_setting']['replay_to']);
//		}
//	}

///**
// * プラグインの定型文を取得する
// */
//	public function getMailSettingPlugin(Model $model, $blockKey, $typeKey = 'contents') {
//		// $blockKey, $typeKeyで、mail_settings を SELECT する
//	}

///**
// * システム管理(カレンダー含む)の定型文を取得する
// */
//	public function getMailSettingSystem(Model $model, $pluginKey, $typeKey = 'contents') {
//		// $pluginKey, $typeKeyで、mail_settings を SELECT する
//	}

///**
// * メール通知フラグをセットする
// */
//	public function setIsMailSend(Model $model, $isMailSend) {
//		$this->isMailSend = $isMailSend;
//	}
//
///**
// * メール通知フラグを取得する
// */
//	public function getIsMailSend(Model $model) {
//		return $this->isMailSend;
//	}

/**
 * 件名をセットする
 *
 * 値の例）
 * $mailSubject = [{X-SITE_NAME}]予定の通知
 *
 * @param Model $model モデル
 * @param string $mailSubject 件名
 * @return void
 */
	public function setMailSubject(Model $model, $mailSubject) {
		$this->mailSubject = trim($mailSubject);
	}

/**
 * 本文をセットする
 *
 * 値の例）
 * $mailBody = "カレンダーに予定が追加されたのでお知らせします。
 *
 * 件名:{X-TITLE}
 * 公開対象:{X-PLAN_FLAG}
 * 開始日時:{X-START_TIME}
 * 終了日時:{X-END_TIME}
 * 場所:{X-LOCATION}
 * 連絡先:{X-CONTACT}
 * 繰返し:{X-RRULE}
 * 記入者:{X-USER}
 * 記入日時:{X-INPUT_TIME}
 *
 * {X-BODY}
 *
 * この予定を見るには、下記アドレスへ
 * {X-URL}
 *
 * "
 *
 * @param Model $model モデル
 * @param string $mailBody 本文
 * @return void
 */
	public function setMailBody(Model $model, $mailBody) {
		$this->mailBody = trim($mailBody);
		$this->mailBody = str_replace("\n", "<br />", $this->mailBody). "<br />";

		// WYSIWYGエディターで出力するテキストをエスケープ
		//$escapeText =& $commonMain->registerClass(WEBAPP_DIR . '/components/escape/Text.class.php', 'Escape_Text', 'escapeText');
		//$this->mailBody = $escapeText->escapeWysiwyg($this->mailBody);
	}

/**
 * 件名を取得する
 *
 * @param Model $model モデル
 * @return void
 */
	public function getMailSubject(Model $model) {
		// 定型文のタグ変換
		foreach ($this->_assignedTags as $k => $v) {
			if (substr($k, 0, 4) == "X-TO" || $k == "X-URL") {
				continue;
			}

			// HtmlからText変換処理
			//$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
			//$this->mailSubject = str_replace("{".$k."}", $convertHtml->convertHtmlToText($v), $this->mailSubject);

			$this->mailSubject = str_replace("{".$k."}", $v, $this->mailSubject);
		}

		return $this->mailSubject;
	}

/**
 * 本文を取得する
 *
 * @param Model $model モデル
 * @return void
 */
	public function getMailBody(Model $model) {
		// 定型文のタグ変換
		foreach ($this->_assignedTags as $k => $v) {
			if (substr($k, 0, 4) == "X-TO" || $k == "X-URL") {
				continue;
			}
			$this->mailBody = str_replace("{".$k."}", $v, $this->mailBody);
		}

		return $this->mailBody;
	}

/**
 * 返信先アドレスをセットする
 */
	public function setMailReplayTo(Model $model, $replayTo) {
		// 空なら、システム管理の送信者メールアドレスをセットする
		if (empty($replayTo)) {
			$replayTo = Configure::read('SiteSetting.fromAddress');
		}

		$this->replayTo = $replayTo;
	}

/**
 * 返信先アドレスを取得する
 *
 * @param Model $model モデル
 * @return void
 */
	public function getMailReplayTo(Model $model) {
		return $this->replayTo;
	}

/**
 * 定型文の変換タグの追加
 *
 * 値の例）
 * $tags = Array
 * (
 *     [X-TITLE] => 予定
 *     [X-PLAN_FLAG] => oレポート
 *     [X-START_TIME] => 2015年10月08日(木) 10:00
 *     [X-END_TIME] => 2015年10月08日(木) 11:00
 *     [X-LOCATION] => 場所
 *     [X-CONTACT] => 連絡先
 *     [X-USER] => admin
 *     [X-INPUT_TIME] => 2015/10/08 08:24:04
 *     [X-BODY] => 内容１<br />内容２<br />内容３
 *     [X-RRULE] => 毎日<br />3回
 *     [X-URL] => http://localhost/index.php?action=pages_view_main&active_action=calendar_view_main_init&calendar_id=1&date=20151008&block_id=32#_32
 * )
 *
 * @param Model $model モデル
 * @param array $tag タグ名 or 変換する値の配列
 * @param string $value タグの変換する値
 * @return void
 */
	public function assignMail(Model $model, $tag, $value = null) {
		if (is_array($tag)) {
			foreach ($tag as $k => $v) {
				$this->assign($k, $v);
			}
		} else {
			if (!empty($tag) && isset($value)) {
				$tag = strtoupper(trim($tag));

				if (substr($tag, 0, 2) == "X-") {
					$this->_assignedTags[$tag] = $value;
				}
			}
		}
	}

/**
 * 送信先ユーザのメール追加
 *
 * @param Model $model モデル
 * @param string $mailAddress 送信先メールアドレス
 * @return void
 */
	public function addToUserMail(Model $model, $mailAddress) {
		$user[]["to_address"] = $mailAddress;		// TODO ここはまだ変わる予定
		$this->toUsers[] = $user;
	}

/**
 * 複数人の送信先ユーザ追加
 *
 * @param Model $model モデル
 * @param array $users ユーザ情報配列（複数人）
 * @return void
 */
	public function addMailToUsers(Model $model, $users) {
		$this->toUsers = array_merge_recursive($this->toUsers ,$users);
	}

/**
 * 複数人の送信先ユーザ設定
 *
 * 値の例）    ※ まだ変わる予定
 * $users = Array
 * (
 *     [0] => Array
 *         (
 *             [handle] => ippan
 *             [lang_dirname] => japanese
 *             [to_address] => mutaguchi@yyyy.jp
 *         )
 *
 *     [1] => Array
 *         (
 *             [handle] => test
 *             [lang_dirname] => japanese
 *             [to_address] => mutaguchi@xxxx.jp
 *         )
 *
 *     [2] => Array
 *         (
 *             [handle] => admin
 *             [lang_dirname] => japanese
 *             [to_address] => muttamuta@xxxx.ne.jp
 *         )
 * )
 *
 * @param Model $model モデル
 * @param array $users ユーザ情報配列（複数人）
 * @return void
 */
	public function setMailToUsers(Model $model, $users) {
		$this->toUsers = $users;
	}

/**
 * 送信先ユーザ追加
 * 「会員登録, パスワード再発行, 会員受付（ユーザ自身の承認が必要） 、会員登録（自動登録-承認処理）」用
 *
 * 値の例）
 * $user = Array
 * (
 *             [handle] => ippan
 *             [lang_dirname] => japanese
 *             [to_address] => mutaguchi@xxxx.jp
 * )
 *
 * @param Model $model モデル
 * @param array $user ユーザ情報配列（一人）
 * @return void
 */
	public function addToUser(Model $model, $user) {
		$this->toUsers[] = $user;
	}

	///**
	// * キューに保存する
	// */
	//	public function saveQueue(Model $model, $contentKey, $languageId, $roomId = null, $sendTime = null) {
	//		// TODOO $sendTime、時間取得って何か共通メソッドありませんでしたっけ？
	//		// メールキューの送信依頼テーブル(mail_queue_send_requests)保存
	//		// メールキュー送信先テーブル(mail_queue_delivers)保存
	//		if ($sendTime === null) {
	//			$sendTime = new DateTime();
	//		}
	//
	//		// ブロックキー、プラグインキーを取得する
	//		$blockKey = $this->getMailSettingBlockKey();
	//		$plaginKey = $this->getMailSettingPlaginKey();
	//
	//		// 返信先アドレスを取得する
	//		$mailReplayTo = $this->getMailReplayTo();
	//
	//		// 件名、本文を取得する
	//		$mailSubject = $this->getMailSubject();
	//		$mailBody = $this->getMailBody();
	//
	//		// ※ 通知する権限は、block_role_permissionにもつ想定
	//		// ※ mail_queue_delivers 値をセットするパターンが３つある。いずれかをセットする
	//		// 　　・user_id 　　：　個別パターン1。パスワード再発行等
	//		// 　　　⇒ $this->toUsersに情報あるだろう。
	//		// 　　・room_id + ロール（block_role_permission）　：　複数人パターン
	//		// 　　　⇒ $roomId 引数で取得, $blockKeyでロール取得
	//		// 　　・to_address　：　個別パターン2。その他に通知するメールアドレス
	//		// 　　　⇒ $this->toUsersにセットしてる
	//	}

/**
 * メールを送信する
 *
 * @param Model $model モデル
 * @return void
 */
	public function sendMail(Model $model) {
	}

/**
 * メールの形式をセットする
 * 設定に関わらず、text/plain, htmlを強制的に指定できる
 *
 * @param Model $model モデル
 * @param bool $isHtml true=html形式, false=text/plain形式
 * @return void
 */
	public function setIsHtml(Model $model, $isHtml) {
	}
}
