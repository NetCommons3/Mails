<?php
/**
 * MailQueueBehaviorテスト用Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppModel', 'Model');

/**
 * MailQueueBehaviorテスト用Model
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\test_app\Plugin\TestMails\Model
 */
class TestMailQueueBehaviorModel extends AppModel {

/**
 * テーブル名
 *
 * @var mixed
 */
	public $useTable = false;

/**
 * 使用ビヘイビア
 *
 * @var array
 */
	public $actsAs = array(
		'Mails.MailQueue'
	);

}
