<?php
/**
 * SiteSettingFixture
 *
 * @author Jun Nishikawa <topaz2@m0n0m0n0.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('SiteSettingFixture', 'SiteManager.Test/Fixture');

/**
 * Summary for SiteSettingFixture
 */
class SiteSettingForMailFixture extends SiteSettingFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'site_settings';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'language_id' => '0',
			'key' => 'Mail.from',
			'value' => 'from@dummy.com',
		),
		array(
			'id' => '2',
			'language_id' => '2',
			'key' => 'Mail.from_name',
			'value' => '管理者',
		),
		array(
			'id' => '3',
			'language_id' => '2',
			'key' => 'Mail.body_header',
			'value' => '※このメールに返信しても相手には届きませんのでご注意ください。',
		),
		array(
			'id' => '4',
			'language_id' => '2',
			'key' => 'Mail.signature',
			'value' => "-- \r\nPowered by NetCommons",
		),
		array(
			'id' => '5',
			'language_id' => '2',
			'key' => 'App.site_name',
			'value' => 'NetCommons3',
		),
		array(
			'id' => '6',
			'language_id' => '0',
			'key' => 'Mail.use_cron',
			'value' => '1',
		),
	);

}
