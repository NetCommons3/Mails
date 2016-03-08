<?php
/**
 * メールキュー Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * メールキュー Behavior
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Model\Behavior
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
}
