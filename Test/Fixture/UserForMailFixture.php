<?php
/**
 * UserForMailFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UserFixture', 'Users.Test/Fixture');

/**
 * UserForMailFixture
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Users\Model
 */
class UserForMailFixture extends UserFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'users';

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		parent::init();

		foreach ($this->records as $i => &$record) {
			$record['is_email_reception'] = true;

			//chief_editor のみ メール受信しないに設定
			if ($i == 2) {
				$record['is_email_reception'] = false;
			}
		}
	}
}
