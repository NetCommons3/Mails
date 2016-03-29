<?php
/**
 * ConvertHtml::convertHtmlToText()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('ConvertHtml', 'Mails.Utility');

/**
 * ConvertHtml::convertHtmlToText()のテスト
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Case\Utility\ConvertHtml
 */
class MailsUtilityConvertHtmlConvertHtmlToTextTest extends NetCommonsCakeTestCase {

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'mails';

/**
 * convertHtmlToText()のテスト
 *
 * @return void
 */
	public function testConvertHtmlToText() {
		//データ生成
		$str = '<b>ああああ</b>いいいい';
		$convertHtml = new ConvertHtml();

		//テスト実施
		$result = $convertHtml->convertHtmlToText($str);

		//チェック
		$this->assertNotRegExp('/<b>.*?<\/b>/', $result);
	}

}
