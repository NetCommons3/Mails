<?php
/**
 * Element of block role permission setting inline
 *   - $roles:
 *       The results `Roles` data of NetCommonsBlockComponent->getBlockRolePermissions().
 *   - $settingPermissions: Permissions data of creatable panel
 *       - key: permission
 *       - value: label
 *   - $panelLabel: Panel Label
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->script('/blocks/js/block_role_permissions.js');

//Camel形式に変換
$initializeParams = NetCommonsAppController::camelizeKeyRecursive(array('roles' => $roles));
?>

<div ng-controller="BlockRolePermissions"
	ng-init="initializeRoles(<?php echo h(json_encode($initializeParams, JSON_FORCE_OBJECT)); ?>)">

	<?php foreach ($settingPermissions as $permission => $label) : ?>
		<div class="form-group">
			<?php echo $this->NetCommonsForm->label('BlockRolePermission.' . $permission, h($label)); ?>
			<?php echo $this->BlockRolePermissionForm->checkboxBlockRolePermission('BlockRolePermission.' . $permission); ?>
		</div>
	<?php endforeach; ?>
</div>
