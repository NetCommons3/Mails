<?php
/**
 * NetCommonsメールの本文のエスケープ Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('HTMLPurifier_Filter_Comment', 'Wysiwyg.Utility/Filter');
App::uses('WysiwygBehavior', 'Wysiwyg.Model/Behavior');

/**
 * NetCommonsメールの本文のエスケープ Utility
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 */
class NetCommonsMailHtmlCleansing {

/**
 * 設定値
 *
 * @var array
 */
	private static $__config = null;

/**
 * HTMLクレンジングコンフィグ
 *
 * @var HTMLPurifier_Config
 */
	private $__HTMLPurifierConfig;

/**
 * コンストラクタ
 */
	public function __construct() {
		if (is_null(self::$__config)) {
			Configure::load('Mails.mail_cleansing_config');
			self::$__config = \Configure::read('mail_cleansing_config');
		}

		$this->__HTMLPurifierConfig = HTMLPurifier_Config::createDefault();
		foreach (self::$__config as $namespace => $values) {
			foreach ($values as $key => $value) {
				$this->__HTMLPurifierConfig->set("{$namespace}.{$key}", $value);
			}
		}
		$this->__addCssDef($this->__HTMLPurifierConfig);
	}

/**
 * クレンジングする
 *
 * @param string $mailBody メール本文
 * @return string
 */
	public function cleanse(string $mailBody) {
		$baseUrl = h(substr(Router::url('/', true), 0, -1));
		$mailBody = $this->__replaceContent(
			WysiwygBehavior::REPLACE_BASE_URL, $baseUrl, $mailBody
		);

		$HTMLPurifier = new HTMLPurifier($this->__HTMLPurifierConfig);
		$mailBody = $HTMLPurifier->purify($mailBody);
		return $mailBody;
	}

/**
 * Wysiwygフィールド内の「ファイル／画像」のパスの変換処理
 *
 * @param String $search 検索する文字列
 * @param String $replace 置換する文字列
 * @param string $content 置換対象文字列
 * @return string 置換した内容を返す
 */
	private function __replaceContent($search, $replace, $content) {
		// 検索対象に / があるとデリミタエラーが発生するので置換する
		$search = str_replace('/', '\/', $search);

		// 定義フィールドが存在しない場合は無視する
		if ($content) {
			$pattern = sprintf('/%s\/(%s)\/([0-9]*)/', $search, WysiwygBehavior::WYSIWYG_REPLACE_PATH);
			$replacement = sprintf('%s/\1/\2', $replace);

			$content = preg_replace($pattern, $replacement, $content);
		}

		return $content;
	}
/**
 * CSS定義を追加
 *
 * @param HTMLPurifier_Config $HTMLPurifierConfig HTMLPurifier_Config instance
 * @return void
 */
	private function __addCssDef(HTMLPurifier_Config $HTMLPurifierConfig) {
		$CSSDefinition = $HTMLPurifierConfig->getCSSDefinition();
		if ($CSSDefinition) {
			$CSSDefinition->info['position'] = new HTMLPurifier_AttrDef_Enum(
				array('absolute', 'fixed', 'relative', 'static')
			);
			$CSSDefinition->info['top'] =
			$CSSDefinition->info['bottom'] =
			$CSSDefinition->info['left'] =
			$CSSDefinition->info['right'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_CSS_Length(),
					new HTMLPurifier_AttrDef_CSS_Percentage(),
					new HTMLPurifier_AttrDef_Enum(array('auto'))
				)
			);
			$CSSDefinition->info['z-index'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_CSS_Number(),
					new HTMLPurifier_AttrDef_Enum(array('auto')),
				)
			);
			$CSSDefinition->info['direction'] = new HTMLPurifier_AttrDef_Enum(
				array('ltr', 'rtl')
			);
			$CSSDefinition->info['unicode-bidi'] = new HTMLPurifier_AttrDef_Enum(
				array('normal', 'embed', 'bidi-override')
			);
			$CSSDefinition->info['width'] =
			$CSSDefinition->info['height'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_CSS_Length(),
					new HTMLPurifier_AttrDef_CSS_Percentage(),
					new HTMLPurifier_AttrDef_Enum(array('auto')),
				)
			);
			$CSSDefinition->info['min-width'] =
			$CSSDefinition->info['min-height'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_CSS_Length(),
					new HTMLPurifier_AttrDef_CSS_Percentage(),
				)
			);
			$CSSDefinition->info['max-width'] =
			$CSSDefinition->info['max-height'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_CSS_Length(),
					new HTMLPurifier_AttrDef_CSS_Percentage(),
					new HTMLPurifier_AttrDef_Enum(array('none')),
				)
			);
			$CSSDefinition->info['text-justify'] = new HTMLPurifier_AttrDef_Enum(
				array('auto', 'distribute', 'distribute-all-lines', 'inter-cluster',
					'inter-ideograph', 'inter-word', 'kashida', 'newspaper')
			);
			$CSSDefinition->info['text-underline-position'] = new HTMLPurifier_AttrDef_Enum(
				array('above', 'below')
			);
			$CSSDefinition->info['empty-cells'] = new HTMLPurifier_AttrDef_Enum(
				array('show', 'hide')
			);
			$CSSDefinition->info['cursor'] = new HTMLPurifier_AttrDef_CSS_Composite(
				array(
					new HTMLPurifier_AttrDef_Enum(array('auto', 'default', 'pointer', 'crosshair',
						'move', 'text', 'wait', 'help', 'n-resize', 's-resize', 'w-resize',
						'e-resize', 'ne-resize', 'nw-resize', 'se-resize', 'sw-resize',
						'progress', 'hand', 'no-drop', 'all-scroll', 'col-resize', 'row-resize',
						'not-allowed', 'vertical-text')),
					new HTMLPurifier_AttrDef_CSS_URI(),
				)
			);
		}
	}

}
