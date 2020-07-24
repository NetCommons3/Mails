<?php
/**
 * メールの設定ファイル
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

$cachePath = CACHE . 'HTMLPurifier' . DS;
if (! file_exists($cachePath)) {
	//mkdirがumaskに依存して、ディレクトリが作成されてしまうため、
	//グループにapache権限を与えてるときに書き込みができなくなることがある。
	$old = umask(0);
	@mkdir($cachePath, 0775);
	umask($old);
}

$config['mail_cleansing_config'] = [
	'Attr' => [
		'AllowedFrameTargets' => [
			'_blank',
			'_self',
			'_parent',
			'_top',
		],
		'AllowedRel' => [
			'alternate',
			'author',
			'bookmark',
			'help',
			'icon',
			'license',
			'next',
			'nofollow',
			'noreferrer',
			'prefetch',
			'prev',
			'search',
			'stylesheet',
			'tag',
		],
		'EnableID' => true,
	],
//	'Cache' => [
//		'SerializerPath' => $cachePath,
//	],
	'CSS' => [
		'AllowDuplicates' => true,
		'AllowImportant' => true,
		'AllowTricky' => true,
		'DefinitionRev' => 1,
		'Proprietary' => true,
		'Trusted' => true,
		'AllowedProperties' => [
			'color' => true,
			'background-color' => true,
			'margin' => true,
			'text-align' => true,
			'margin-left' => true,
			'margin-right' => true,
			'margin-top' => true,
			'margin-bottom' => true,
			'padding' => true,
			'padding-left' => true,
			'padding-right' => true,
			'padding-top' => true,
			'padding-bottom' => true,
			'border' => true,
			'border-left' => true,
			'border-right' => true,
			'border-top' => true,
			'border-bottom' => true,
			'border-width' => true,
			'border-left-width' => true,
			'border-right-width' => true,
			'border-top-width' => true,
			'border-bottom-width' => true,
			'border-style' => true,
			'border-left-style' => true,
			'border-right-style' => true,
			'border-top-style' => true,
			'border-bottom-style' => true,
			'border-color' => true,
			'border-left-color' => true,
			'border-right-color' => true,
			'border-top-color' => true,
			'border-bottom-color' => true,
			'display' => true,
			'float' => true,
			'clear' => true,
			'width' => true,
			'height' => true,
			'vertical-align' => true,
			'overflow' => true,
			'visibility' => true,
			'background' => true,
			'background-image' => true,
			'background-repeat' => true,
			'background-attachment' => true,
			'background-position' => true,
			'font' => true,
			'font-style' => true,
			'font-variant' => true,
			'font-weight' => true,
			'font-size' => true,
			'line-height' => true,
			'font-family' => true,
			'text-indent' => true,
			'text-decoration' => true,
			'letter-spacing' => true,
			'text-transform' => true,
			'white-space' => true,
			'table-layout' => true,
			'border-spacing' => true,
			'border-collapse' => true,
		],
	],
	'Core' => [
		'AllowHostnameUnderscore' => true,
		'ConvertDocumentToFragment' => false,
		'DisableExcludes' => true,
		'Encoding' => 'UTF-8',
		'MaintainLineNumbers' => false,
	],
	'HTML' =>[
		'Doctype' => 'XHTML 1.0 Transitional',
		'SafeIframe' => true,
		'FlashAllowFullScreen' => true,
		'TargetNoreferrer' => false,
		'Trusted' => true,
		'Allowed' =>
			'div,' .
			'span,' .
			'h1[align],' .
			'h2[align],' .
			'h3[align],' .
			'h4[align],' .
			'h5[align],' .
			'h6[align],' .
			'br[clear],' .
			'img[src|vspace|hspace|border|alt|height|width|data-size|data-position|data-imgid],' .
			'ol[compact|start|type],' .
			'ul[compact|type],' .
			'li[type|value],' .
			'a[href|target],' .
			'hr[align|color|noshade|size|width],' .
			'table[cellspacing|cellpadding|border|align],' .
			'tbody[align|bgcolor|char|charoff|valign],' .
			'tr[colspan|rowspan],' .
			'td[colspan|rowspan|bgcolor|align|valign|height|width|nowrap|char|charoff' .
				'|abbr|axis|headers|scope],' .
			'blockquote[cite],' .
			'p[align],' .
			'th[colspan|rowspan|bgcolor|align|valign|height|width|nowrap|char|charoff' .
				'|abbr|axis|headers|scope],' .
			'strong,' .
			'caption[align|valign],' .
			'cite,' .
			'code,' .
			'kbd,' .
			'pre[cols|width|wrap],' .
			'q,' .
			'small,' .
			'sub,' .
			'sup,' .
			'em,' .
			'i,' .
			'col[span],' .
			'colgroup[span],' .
			'dl[compact],' .
			'dt,' .
			'dd,' .
			// HTML5から採用--ここから
			'rb,' .
			'ruby,' .
			'rp,' .
			'rt,' .
			'wbr,' .
			// HTML5から採用--ここまで
			// HTML5で廃止--ここから
			'font[size|color|face],' .
			'big,' .
			'center,' .
			'tt,' .
			'u,' .
			's,' .
			'strike,' .
			'noembed,' .
			// HTML5で廃止--ここまで
			// 全要素共通
			'*[class|id|title|cite|background|style|align|dir|lang|language]',
	],
	'URI' => [
		'AllowedSchemes' => [
			'http' => true,
			'https' => true,
			'mailto' => true,
			'ftp' => true,
		],
	],
	'Output' => [
		'FlashCompat' => true,
	],
];