<?php
/**
 * MailQueueBehavior::save()テスト用Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppModel', 'Model');

/**
 * MailQueueBehavior::save()テスト用Model
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Test\test_app\Plugin\TestMails\Model
 */
class TestMailQueueBehaviorSaveModel extends AppModel {

/**
 * 使用ビヘイビア
 *
 * @var array
 */
	public $actsAs = array(
		'Mails.MailQueue' => array(
			'embedTags' => array(
				'X-SUBJECT' => 'TestMailQueueBehaviorSaveModel.title',
				'X-BODY' => 'TestMailQueueBehaviorSaveModel.content',
			),
			// 暫定対応：メールで承認するフラグ取得用（今後設定不要になる見込み）
			//'useWorkflow' => 'VideoBlockSetting.use_workflow',
		),
	);

}
