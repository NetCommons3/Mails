<?php
/**
 * NetCommonsメール 埋め込みタグ Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailQueueBehavior', 'Mails.Model/Behavior');
App::uses('NetCommonsUrl', 'NetCommons.Utility');
App::uses('NetCommonsMailAssignTag', 'Mails.Utility');

/**
 * NetCommonsメール 埋め込みタグ Utility
 *
 * NetCommonsMailAssignTag::__call() より呼び出される想定
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 * @see NetCommonsMailAssignTag::__call()
 */
class NetCommonsExtentionTag {

/**
 * Constructor
 */
	public function __construct() {
		$this->RoomsLanguage = ClassRegistry::init('Rooms.RoomsLanguage');
		$this->User = ClassRegistry::init('Users.User');
	}

/**
 * 埋め込みタグ{X-USER}にセットする値 を取得
 *
 * @param int $createdUserId 登録者ID
 * @return array
 */
	public function getXUser($createdUserId) {
		if (empty($createdUserId)) {
			// コンテンツコメントで、参観者まで投稿を許可していると、ログインしていない人もコメント書ける。その時はuser_idなし
			$handlename = __d('mails', 'not login');
		} else {
			$user = $this->User->findById($createdUserId);
			$handlename = Hash::get($user, 'User.handlename');
		}
		return array('X-USER', $handlename);
	}

/**
 * 埋め込みタグ{X-URL}にセットする値 を取得
 *
 * @param string $contentKey コンテンツキー
 * @param array $urlParams X-URLのurlパラメータ
 * @return array
 */
	public function getXUrl($contentKey, $urlParams = array()) {
		// fullpassのURL
		if (is_array($urlParams)) {
			$url = NetCommonsUrl::actionUrl(Hash::merge(
				array(
					'controller' => Current::read('Plugin.key'),
					'action' => 'view',
					'block_id' => Current::read('Block.id'),
					'frame_id' => Current::read('Frame.id'),
					'key' => $contentKey
				),
				$urlParams
			));
			$url = NetCommonsUrl::url($url, true);
		} else {
			$url = $urlParams;
		}
		return array('X-URL', $url);
	}

/**
 * 埋め込みタグ{X-WORKFLOW_COMMENT}にセットする値 を取得
 *
 * @param array $data saveしたデータ
 * @param string $fixedPhraseType コンテンツキー
 * @param int $useWorkflowBehavior ワークフロービヘイビアを使う
 * @return array
 */
	public function getXWorkflowComment($data, $fixedPhraseType, $useWorkflowBehavior) {
		$result = array('X-WORKFLOW_COMMENT', '');
		if (!$useWorkflowBehavior) {
			return $result;
		}

		if ($fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL ||
			$fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_DISAPPROVAL ||
			$fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_APPROVAL_COMPLETION ||
			$fixedPhraseType == NetCommonsMailAssignTag::SITE_SETTING_FIXED_PHRASE_CONTACT_AFTER_APPROVAL) {

			$workflowComment = Hash::get($data, 'WorkflowComment.comment');
			$commentLabel = __d('net_commons', 'Comments to the person in charge.');
			$workflowComment = $commentLabel . ":\n" . $workflowComment;
			$result = array('X-WORKFLOW_COMMENT', $workflowComment);
		}

		return $result;
	}

/**
 * 埋め込みタグ{X-TAGS}にセットする値 を取得
 *
 * @param array $data saveしたデータ
 * @param string $workflowType ワークフロータイプ
 * @param int $useTagBehavior タグビヘイビアを使う
 * @return array
 */
	public function getXTags($data, $workflowType, $useTagBehavior) {
		$result = array('X-TAGS', '');
		if (!$useTagBehavior) {
			return $result;
		}

		if ($workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_NONE ||
				$workflowType == MailQueueBehavior::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW) {

			$tags = Hash::extract($data, 'Tag.{n}.name');
			$tags = implode(',', $tags);
			$tagLabel = __d('tags', 'tag');
			$tags = $tagLabel . ':' . $tags;
			$result = array('X-TAGS', $tags);
		}

		return $result;
	}

/**
 * 埋め込みタグ{X-ROOM}にセットする値 を取得
 *
 * @param int $languageId 言語ID
 * @return array
 */
	public function getXRoom($languageId) {
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
		$value = h($roomName);

		return array('X-ROOM', $value);
	}

}
