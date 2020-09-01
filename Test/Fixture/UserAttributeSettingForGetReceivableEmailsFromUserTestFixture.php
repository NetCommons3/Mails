<?php
/**
 * UserAttributeSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UserAttributeSettingFixture', 'UserAttributes.Test/Fixture');

/**
 * UserAttributeSettingFixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\UserAttributes\Test\Fixture
 * @codeCoverageIgnore
 */
class UserAttributeSettingForGetReceivableEmailsFromUserTestFixture extends UserAttributeSettingFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'UserAttributeSetting';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'user_attribute_settings';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'user_attribute_key' => 'email',
			'data_type_key' => 'email',
			'row' => '1',
			'col' => '1',
			'weight' => '1',
			'required' => true,
			'display' => true,
			'only_administrator_readable' => false,
			'only_administrator_editable' => false,
			'is_system' => false,
			'display_label' => true,
			'display_search_result' => true,
			'self_public_setting' => true,
			'self_email_setting' => false,
			'is_multilingualization' => true,
		),
		[
			'user_attribute_key' => 'moblie_mail',
			'data_type_key' => 'email'
		],
		[
			'user_attribute_key' => 'other_mail',
			'data_type_key' => 'email'
		],
		//array(
		//	'id' => '2',
		//	'user_attribute_key' => 'radio_attribute_key',
		//	'data_type_key' => 'radio',
		//	'row' => '1',
		//	'col' => '1',
		//	'weight' => '2',
		//	'required' => true,
		//	'display' => true,
		//	'only_administrator_readable' => false,
		//	'only_administrator_editable' => true,
		//	'is_system' => false,
		//	'display_label' => true,
		//	'display_search_result' => true,
		//	'self_public_setting' => true,
		//	'self_email_setting' => false,
		//	'is_multilingualization' => true,
		//),
		//array(
		//	'id' => '3',
		//	'user_attribute_key' => 'system_attribute_key',
		//	'data_type_key' => 'text',
		//	'row' => '1',
		//	'col' => '1',
		//	'weight' => '3',
		//	'required' => true,
		//	'display' => false,
		//	'only_administrator_readable' => true,
		//	'only_administrator_editable' => true,
		//	'is_system' => true,
		//	'display_label' => true,
		//	'display_search_result' => true,
		//	'self_public_setting' => true,
		//	'self_email_setting' => false,
		//	'is_multilingualization' => true,
		//),
	);

}
