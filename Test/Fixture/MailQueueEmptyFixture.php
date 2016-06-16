<?php
/**
 * MailQueueFixture  0件
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailQueueFixture', 'Mails.Test/Fixture');

/**
 * MailQueueFixture 0件
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\Fixture
 */
class MailQueueEmptyFixture extends MailQueueFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'MailQueue';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'mail_queues';

/**
 * Records
 *
 * @var array
 */
	public $records = array();

}
