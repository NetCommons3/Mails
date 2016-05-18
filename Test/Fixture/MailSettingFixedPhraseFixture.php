<?php
/**
 * MailSettingFixedPhraseFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * MailSettingFixedPhraseFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailSettingFixedPhraseFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mail_setting_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 6, 'unsigned' => false, 'key' => 'index'),
		'plugin_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'block_key' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'type_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文の種類', 'charset' => 'utf8mb4'),
		'mail_fixed_phrase_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 件名', 'charset' => 'utf8mb4'),
		'mail_fixed_phrase_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '定型文 本文', 'charset' => 'utf8mb4'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_mail_setting_fixed_phrases_plugins1_idx' => array('column' => 'plugin_key', 'unique' => 0, 'length' => array('plugin_key' => '191')),
			'fk_mail_setting_fixed_phrases_blocks1_idx' => array('column' => 'block_key', 'unique' => 0, 'length' => array('block_key' => '191')),
			'fk_mail_setting_fixed_phrases_languages1_idx' => array('column' => 'language_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// contents - 英, 日
		array(
			'id' => 1,
			'mail_setting_id' => 1,
			'language_id' => 1,
			'plugin_key' => 'dummy',
			'block_key' => 'block_1',
			'type_key' => 'contents',
			'mail_fixed_phrase_subject' => '[{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})',
			'mail_fixed_phrase_body' => "{X-PLUGIN_NAME} to let you know because the content has
been posted.
Room name: {X-ROOM}
Block Name: {X-BLOCK_NAME}
Title: {X-SUBJECT}
Posted: {X-USER}
Post time: {X-TO_DATE}

{X-BODY}

Please click on the link below to check this post content.
{X-URL}",
			'created_user' => 1,
			'created' => '2016-03-22 12:21:35',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:21:35'
		),
		array(
			'id' => 2,
			'mail_setting_id' => 1,
			'language_id' => 2,
			'plugin_key' => 'dummy',
			'block_key' => 'block_1',
			'type_key' => 'contents',
			'mail_fixed_phrase_subject' => '[{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})',
			'mail_fixed_phrase_body' => "{X-PLUGIN_NAME}にコンテンツが投稿されたのでお知らせします。
ルーム名:{X-ROOM}
ブロック名:{X-BLOCK_NAME}
タイトル:{X-SUBJECT}
投稿者:{X-USER}
投稿日時:{X-TO_DATE}

{X-BODY}

この投稿内容を確認するには下記のリンクをクリックして下さい。
{X-URL}",
			'created_user' => 1,
			'created' => '2016-03-22 12:21:35',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:21:35'
		),
		// answer - 英, 日
		array(
			'id' => 3,
			'mail_setting_id' => 1,
			'language_id' => 1,
			'plugin_key' => 'dummy',
			'block_key' => 'block_1',
			'type_key' => 'answer',
			'mail_fixed_phrase_subject' => '[{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})',
			'mail_fixed_phrase_body' => "{X-PLUGIN_NAME} we've been responded to.
Room name: {X-ROOM}
Title: {X-SUBJECT}
Respondents: {X-USER}
Answer time: {X-TO_DATE}

Answer to see the results click on the link below.
{X-URL}",
			'created_user' => 1,
			'created' => '2016-03-22 12:21:35',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:21:35'
		),
		array(
			'id' => 4,
			'mail_setting_id' => 1,
			'language_id' => 2,
			'plugin_key' => 'dummy',
			'block_key' => 'block_1',
			'type_key' => 'answer',
			'mail_fixed_phrase_subject' => '[{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})',
			'mail_fixed_phrase_body' => "{X-PLUGIN_NAME}に回答されたのでお知らせします。
ルーム名:{X-ROOM}
タイトル:{X-SUBJECT}
回答者:{X-USER}
回答日時:{X-TO_DATE}

回答結果を参照するには下記のリンクをクリックして下さい。
{X-URL}",
			'created_user' => 1,
			'created' => '2016-03-22 12:21:35',
			'modified_user' => 1,
			'modified' => '2016-03-22 12:21:35'
		),
	);

}
