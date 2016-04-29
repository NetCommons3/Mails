<?php
/**
 * NetCommonsメール 埋め込みタグ Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji Masukawa
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('MailQueueBehavior', 'Mails.Model/Behavior');

/**
 * NetCommonsメール 埋め込みタグ Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 * @property SiteSetting $SiteSetting
 * @property RoomsLanguage $RoomsLanguage
 * @property Workflow $Workflow
 * @property User $User
 */
class NetCommonsMailAssignTag {

/**
 * @var int メール本文の1行の最大文字数
 */
	const MAX_LINE_LENGTH = 300;

/**
 * SiteSettingの定型文の種類
 *
 * @var string 承認依頼通知
 * @var string 差戻し通知
 * @var string 承認完了通知
 */
	const
		SITE_SETTING_FIXED_PHRASE_APPROVAL = 'approval',
		SITE_SETTING_FIXED_PHRASE_DISAPPROVAL = 'disapproval',
		SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION = 'approval_completion';

/**
 * @var string 件名(定型文)
 */
	public $fixedPhraseSubject = null;

/**
 * @var string 本文(定型文)
 */
	public $fixedPhraseBody = null;

/**
 * @var array 埋め込みタグ
 */
	public $assignTags = array();

/**
 * @var array SiteSetting model data
 */
	public $siteSetting = null;

/**
 * Constructor
 */
	public function __construct() {
		$this->RoomsLanguage = ClassRegistry::init('Rooms.RoomsLanguage');
		$this->User = ClassRegistry::init('Users.User');
	}

/**
 * 初期設定 プラグイン用
 *
 * @param int $languageId 言語ID
 * @param string $pluginName プラグイン名
 * @return void
 * @see CakeEmail::$charset
 */
	public function initPlugin($languageId, $pluginName = null) {
		// SiteSettingからメール設定を取得する
		SiteSettingUtil::setup(array(
			'Mail.from',
			'Mail.from_name',
			'Mail.messageType',
			'Mail.transport',
			'App.site_name',
			'Workflow.approval_mail_subject',
			'Workflow.approval_mail_body',
			'Workflow.disapproval_mail_subject',
			'Workflow.disapproval_mail_body',
			'Workflow.approval_completion_mail_subject',
			'Workflow.approval_completion_mail_body',
			'Mail.body_header',
			'Mail.signature',
		));

		$this->initTags($languageId, $pluginName);
	}

/**
 * 初期設定 タグ
 *
 * @param int $languageId 言語ID
 * @param string $pluginName プラグイン名
 * @return void
 */
	public function initTags($languageId, $pluginName = null) {
		if ($pluginName === null) {
			$pluginName = Current::read('Plugin.name');
		}
		$from = SiteSettingUtil::read('Mail.from');
		$fromName = SiteSettingUtil::read('Mail.from_name', null, $languageId);
		$siteName = SiteSettingUtil::read('App.site_name', null, $languageId);
		$bodyHeader = SiteSettingUtil::read('Mail.body_header', null, $languageId);
		$signature = SiteSettingUtil::read('Mail.signature', null, $languageId);

		$netCommonsTime = new NetCommonsTime();
		$siteimezone = $netCommonsTime->getSiteTimezone();
		$now = NetCommonsTime::getNowDatetime();
		$date = new DateTime($now);
		$date->setTimezone(new DateTimeZone($siteimezone));
		$siteNow = $date->format('Y/m/d H:i:s');

		$this->assignTag('X-FROM_EMAIL', $from);
		$this->assignTag('X-FROM_NAME', htmlspecialchars($fromName));
		$this->assignTag('X-SITE_NAME', htmlspecialchars($siteName));
		$this->assignTag('X-SITE_URL', Router::fullbaseUrl());
		$this->assignTag('X-PLUGIN_NAME', htmlspecialchars($pluginName));
		$this->assignTag('X-BLOCK_NAME', htmlspecialchars(Current::read('Block.name')));
		$this->assignTag('X-TO_DATE', $siteNow);
		$this->assignTag('X-BODY_HEADER', $bodyHeader);
		$this->assignTag('X-SIGNATURE', $signature);

		// X-ROOMタグ
		$roomId = Current::read('Room.id');
		$roomsLanguage = $this->RoomsLanguage->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'room_id' => $roomId,
				'language_id' => $languageId,
			),
			'callbacks' => false,
		));
		$roomName = Hash::get($roomsLanguage, 'RoomsLanguage.name');
		$this->assignTag('X-ROOM', htmlspecialchars($roomName));
	}

/**
 * サイト設定 セット
 *
 * @param int $languageId 言語ID
 * @param string $fixedPhraseType 定型文の種類
 * @param array $mailSettingPlugin プラグイン側のメール設定データ
 * @return void
 */
	public function setMailFixedPhrase($languageId,
										$fixedPhraseType = null,
										$mailSettingPlugin = null) {
		if (isset($fixedPhraseType)) {
			$this->setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType, $mailSettingPlugin);
		} else {
			$this->setMailFixedPhrasePlugin($mailSettingPlugin);
		}
	}

/**
 * プラグインの定型文 セット
 *
 * @param array $mailSettingPlugin メール設定データ
 * @return void
 */
	public function setMailFixedPhrasePlugin($mailSettingPlugin) {
		$subject = Hash::get($mailSettingPlugin, 'MailSettingFixedPhrase.mail_fixed_phrase_subject');
		$body = Hash::get($mailSettingPlugin, 'MailSettingFixedPhrase.mail_fixed_phrase_body');

		// 定型文
		$this->setFixedPhraseSubject($subject);
		$this->setFixedPhraseBody($body);
	}

/**
 * サイト設定の定型文 セット
 *
 * @param int $languageId 言語ID
 * @param string $fixedPhraseType 定型文の種類
 * @param array $mailSettingPlugin プラグイン側のメール設定データ
 * @return void
 */
	public function setMailFixedPhraseSiteSetting($languageId, $fixedPhraseType,
													$mailSettingPlugin = null) {
		$subject = SiteSettingUtil::read('Workflow.' . $fixedPhraseType . '_mail_subject',
			null, $languageId);
		$body = SiteSettingUtil::read('Workflow.' . $fixedPhraseType . '_mail_body',
			null, $languageId);

		// 定型文
		$this->setFixedPhraseSubject($subject);
		$this->setFixedPhraseBody($body);

		if ($mailSettingPlugin === null) {
			return;
		}

		$pluginSubject = Hash::get($mailSettingPlugin,
			'MailSettingFixedPhrase.mail_fixed_phrase_subject');
		$pluginBody = Hash::get($mailSettingPlugin, 'MailSettingFixedPhrase.mail_fixed_phrase_body');
		$this->assignTag('X-PLUGIN_MAIL_SUBJECT', $pluginSubject);
		$this->assignTag('X-PLUGIN_MAIL_BODY', $pluginBody);
	}

/**
 * 件名(定型文)をセットする
 *
 * @param string $fixedPhraseSubject 件名
 * @return void
 */
	public function setFixedPhraseSubject($fixedPhraseSubject) {
		$this->fixedPhraseSubject = trim($fixedPhraseSubject);
	}

/**
 * 本文(定型文)をセットする
 *
 * @param string $fixedPhraseBody 本文
 * @return void
 */
	public function setFixedPhraseBody($fixedPhraseBody) {
		$this->fixedPhraseBody = trim($fixedPhraseBody);
	}

/**
 * 埋め込みタグの追加
 *
 * @param string $tag タグ
 * @param string $value 変換する値
 * @return array タグ
 */
	public function assignTag($tag, $value = null) {
		if (empty($tag)) {
			return;
		}
		// $tagあり、$valueなしで、タグの値取得
		if ($value === null) {
			return Hash::get($this->assignTags, $tag);
		}
		// タグの両端空白なくして、大文字に変換
		$tag = strtoupper(trim($tag));

		// 頭に X- 付タグならセット
		if (substr($tag, 0, 2) == 'X-') {
			$this->assignTags[$tag] = $value;
		}
	}

/**
 * 埋め込みタグを配列で追加
 *
 * @param array $tags タグ配列
 * @return void
 */
	public function assignTags($tags) {
		foreach ($tags as $key => $value) {
			$this->assignTag($key, $value);
		}
	}

/**
 * 埋め込みタグをデータから参照して追加
 *
 * @param array $tags タグ配列
 * @param array $data タグ配列
 * @return void
 */
	public function assignTagDatas($tags, $data) {
		foreach ($tags as $tag => $dataKey) {
			$dataValue = Hash::get($data, $dataKey);
			$this->assignTag($tag, $dataValue);
		}
	}

/**
 * 埋め込みタグ変換：定型文の埋め込みタグを変換して、メール生文にする
 *
 * @return array タグ
 */
	public function assignTagReplace() {
		// 承認系メールのタグは先に置換
		if (isset($this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->assignTags['X-PLUGIN_MAIL_BODY'])) {
			$this->fixedPhraseBody = str_replace('{X-PLUGIN_MAIL_BODY}',
				$this->assignTags['X-PLUGIN_MAIL_BODY'], $this->fixedPhraseBody);
			$this->fixedPhraseSubject = str_replace('{X-PLUGIN_MAIL_SUBJECT}',
				$this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->fixedPhraseSubject);
			unset($this->assignTags['X-PLUGIN_MAIL_SUBJECT'], $this->assignTags['X-PLUGIN_MAIL_BODY']);
		}

		// メール本文の共通ヘッダー文、署名追加
		$this->fixedPhraseBody = $this->assignTags['X-BODY_HEADER'] . "\n" . $this->fixedPhraseBody .
			"\n" . $this->assignTags['X-SIGNATURE'];
		unset($this->assignTags['X-BODY_HEADER'], $this->assignTags['X-SIGNATURE']);

		// html or text
		$messageType = SiteSettingUtil::read('Mail.messageType');

		// URL
		if (isset($this->assignTags['X-URL'])) {
			if ($messageType == 'text') {
				$this->fixedPhraseBody = str_replace('{X-URL}', $this->assignTags['X-URL'],
					$this->fixedPhraseBody);
			} else {
				$this->fixedPhraseBody = str_replace('{X-URL}',
					'<a href=\'' . $this->assignTags['X-URL'] . '\'>' . $this->assignTags['X-URL'] . '</a>',
					$this->fixedPhraseBody);
			}
			unset($this->assignTags['X-URL']);
		}

		// 本文
		if (isset($this->assignTags['X-BODY'])) {
			$this->fixedPhraseBody = str_replace('{X-BODY}', h($this->assignTags['X-BODY']),
				$this->fixedPhraseBody);
			unset($this->assignTags['X-BODY']);
		}

		foreach ($this->assignTags as $key => $value) {
			if ($messageType == 'text') {
				$this->fixedPhraseBody = str_replace('{' . $key . '}', h($value), $this->fixedPhraseBody);
			} else {
				$this->fixedPhraseBody = str_replace('{' . $key . '}', $value, $this->fixedPhraseBody);
			}
			$this->fixedPhraseSubject = str_replace('{' . $key . '}', h($value), $this->fixedPhraseSubject);
		}

		$this->fixedPhraseBody = str_replace("\r\n", "\n", $this->fixedPhraseBody);
		$this->fixedPhraseBody = str_replace("\r", "\n", $this->fixedPhraseBody);
		// テキストのブロックを決められた幅で折り返す
		// http://book.cakephp.org/2.0/ja/core-utility-libraries/string.html#CakeText::wrap
		// 各行末空白も自動削除するため、メール署名"-- "(RFC2646)を書いても機能しなくなる
		$this->fixedPhraseBody = CakeText::wrap($this->fixedPhraseBody, $this::MAX_LINE_LENGTH);
	}

/**
 * 埋め込みタグ{X-USER} セット
 *
 * @param int $createdUserId 登録者ID
 * @return void
 */
	public function setXUser($createdUserId) {
		$user = $this->User->findById($createdUserId);
		$handlename = Hash::get($user, 'User.handlename');
		$this->assignTag('X-USER', $handlename);
	}

/**
 * 埋め込みタグ{X-URL} セット
 *
 * @param string $contentKey コンテンツキー
 * @return void
 */
	public function setXUrl($contentKey) {
		// fullpassのURL
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => Current::read('Plugin.key'),
			'action' => 'view',
			'block_id' => Current::read('Block.id'),
			'frame_id' => Current::read('Frame.id'),
			'key' => $contentKey
		));
		$url = NetCommonsUrl::url($url, true);
		$this->assignTag('X-URL', $url);
	}

/**
 * 埋め込みタグ{X-WORKFLOW_COMMENT} セット
 *
 * @param array $data saveしたデータ
 * @param string $fixedPhraseType コンテンツキー
 * @param int $useWorkflowBehavior ワークフロービヘイビアを使う
 * @return void
 */
	public function setXWorkflowComment($data, $fixedPhraseType, $useWorkflowBehavior) {
		$this->assignTag('X-WORKFLOW_COMMENT', '');
		if (!$useWorkflowBehavior) {
			return;
		}

		if ($fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL ||
			$fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL ||
			$fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION) {

			$workflowComment = Hash::get($data, 'WorkflowComment.comment');
			$commentLabel = __d('net_commons', 'Comments to the person in charge.');
			$workflowComment = $commentLabel . ":\n" . $workflowComment;
			$this->assignTag('X-WORKFLOW_COMMENT', $workflowComment);
		}
	}

/**
 * 埋め込みタグ{X-TAGS}の値 セット
 *
 * @param array $data saveしたデータ
 * @param string $workflowType ワークフロータイプ
 * @param int $useTagBehavior タグビヘイビアを使う
 * @return void
 */
	public function setXTags($data, $workflowType, $useTagBehavior) {
		$this->assignTag('X-TAGS', '');
		if (!$useTagBehavior) {
			return;
		}

		if ($workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_NONE ||
				$workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {

			$tags = Hash::extract($data, 'Tag.{n}.name');
			$tags = implode(',', $tags);
			$tagLabel = __d('blogs', 'tag');
			$tags = $tagLabel . ':' . $tags;
			$this->assignTag('X-TAGS', $tags);
		}
	}

/**
 * SiteSettingの定型文の種類 ゲット
 *
 * @param string $status 承認ステータス
 * @return string
 * @throws InternalErrorException
 */
	public function getFixedPhraseType($status) {
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			// --- 公開
			// 承認完了通知メール
			return self::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION;

		} elseif ($status == WorkflowComponent::STATUS_APPROVED) {
			// --- 承認依頼
			// 承認依頼通知メール
			return self::SITE_SETTING_FIXED_PHRASE_APPROVAL;

		} elseif ($status == WorkflowComponent::STATUS_DISAPPROVED) {
			// --- 差戻し
			// 差戻し通知メール
			return self::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL;
		}
		return null;
	}
}
