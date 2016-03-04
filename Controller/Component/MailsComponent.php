<?php
/**
 * Mails Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

App::uses('ComponentCollection', 'Controller');
App::uses('SessionComponent', 'Controller/Component');

/**
 * Mails Component
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Controller\Component
 */
class MailsComponent extends Component {
/**
 * @var int start limit
 */
	const START_LIMIT = 5;

/**
 * @var int max limit
 */
	const MAX_LIMIT = 100;

/**
 * Other components
 *
 * @var array
 */
	public $components = array('Session');

/**
 * @var Controller コントローラ
 */
	protected $_controller = null;

/**
 * Called before the Controller::beforeFilter().
 *
 * @param Controller $controller Instantiating controller
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::initialize
 */
	public function initialize(Controller $controller) {
		// どのファンクションでも $controller にアクセスできるようにクラス内変数に保持する
		$this->_controller = $controller;
	}

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::startup
 */
	public function startup(Controller $controller) {
		$controller->ContentComment = ClassRegistry::init('ContentComments.ContentComment');

		// コンテントコメントからエラーメッセージを受け取る仕組み
		/* @link http://skgckj.hateblo.jp/entry/2014/02/09/005111 */
		$controller->ContentComment->validationErrors = $this->Session->read('ContentComments.forRedirect.errors');
	}

/**
 * Called before the Controller::beforeRender(), and before
 * the view class is loaded, and before Controller::render()
 *
 * コンテンツコメントの一覧データをPaginatorで取得する
 *
 * @param Controller $controller Controller with components to beforeRender
 * @return void
 * @link http://book.cakephp.org/2.0/ja/controllers/components.html#Component::beforeRender
 * @throws Exception Paginatorによる例外
 */
	public function beforeRender(Controller $controller) {
		$useComment = Hash::get($controller->viewVars, $this->settings['viewVarsKey']['useComment']);

		// コンテンツキー
		$contentKey = Hash::get($controller->viewVars, $this->settings['viewVarsKey']['contentKey']);

		// 許可アクション
		$allow = $this->settings['allow'];

		// コメントを利用しない
		if (! $useComment) {
			return;
		}

		// コンテンツキーのDB項目名なし
		if (! isset($contentKey)) {
			return;
		}

		// 許可アクションなし
		if (! isset($allow) || ! in_array($controller->request->params['action'], $allow)) {
			return;
		}

		// 条件
		/* @see ContentComment::getConditions() */
		$query['conditions'] = $controller->ContentComment->getConditions($contentKey);

		//ソート
		$query['order'] = array('ContentComment.created' => 'desc');

		//表示件数
		$query['limit'] = $this::START_LIMIT;

		$controller->Paginator->settings = $query;
		try {
			$contentComments = $controller->Paginator->paginate('ContentComment');
		} catch (Exception $ex) {
			CakeLog::error($ex);
			throw $ex;
		}

		$controller->request->data['ContentComments'] = $contentComments;

		if (!in_array('ContentComments.ContentComment', $controller->helpers) &&
			!array_key_exists('ContentComments.ContentComment', $controller->helpers)
		) {
			$controller->helpers[] = 'ContentComments.ContentComment';
		}
	}

/**
 * Called after Controller::render() and before the output is printed to the browser.
 *
 * @param Controller $controller Controller with components to shutdown
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::shutdown
 */
	public function shutdown(Controller $controller) {
		// 表示は遷移・リロードまでの1回っきりなので消す
		$this->Session->delete('ContentComments.forRedirect');
	}

/**
 * コメントする
 *
 * @return bool 成功 or 失敗
 */
	public function comment() {
		// パーミッションがあるかチェック
		if (!$this->__checkPermission()) {
			return false;
		}

		// 登録・編集・承認
		if ($this->_controller->action == 'add' ||
			$this->_controller->action == 'edit' ||
			$this->_controller->action == 'approve') {

			// dataの準備
			$data = $this->__readyData();

			// コンテンツコメントのデータ保存
			if (!$this->_controller->ContentComment->saveContentComment($data)) {
				$this->_controller->NetCommons->handleValidationError($this->_controller->ContentComment->validationErrors);

				// 別プラグインにエラーメッセージとどの処理を送るため
				/* @link http://skgckj.hateblo.jp/entry/2014/02/09/005111 */
				$sessionValue = array(
					'errors' => $this->_controller->ContentComment->validationErrors,
					'requestData' => $this->_controller->request->data('ContentComment')
				);
				$this->Session->write('ContentComments.forRedirect', $sessionValue);
			}

			// 削除
		} elseif ($this->_controller->action == 'delete') {
			// コンテンツコメントの削除
			if (!$this->_controller->ContentComment->deleteContentComment($this->_controller->request->data('ContentComment.id'))) {
				return false;
			}
		}

		return true;
	}

/**
 * パーミッションがあるかチェック
 *
 * @return bool true:パーミッションあり or false:パーミッションなし
 */
	private function __checkPermission() {
		// 登録処理 and 投稿許可あり
		if ($this->_controller->action == 'add' && Current::permission('content_comment_creatable')) {
			return true;

			// (編集処理 or 削除処理) and (編集許可あり or 自分で投稿したコメントなら、編集・削除可能)
		} elseif (($this->_controller->action == 'edit' || $this->_controller->action == 'delete') && (
				Current::permission('content_comment_editable') ||
				$this->_controller->data['ContentComment']['created_user'] == (int)AuthComponent::user('id')
		)) {
			return true;

			// 承認処理 and 承認許可あり
		} elseif ($this->_controller->action == 'approve' && Current::permission('content_comment_publishable')) {
			return true;

		}
		return false;
	}

/**
 * dataの準備
 *
 * @return array data
 */
	private function __readyData() {
		$data['ContentComment'] = $this->_controller->request->data('ContentComment');
		$data['ContentComment']['block_key'] = Current::read('Block.key');

		return $data;
	}
}
