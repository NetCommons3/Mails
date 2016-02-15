<?php
/**
 * Element of block role setting
 *   - $roles:
 *       The results `Roles` data of NetCommonsBlockComponent->getBlockRolePermissions().
 *   - $settingPermissions: Permissions data of creatable panel
 *       - key: permission
 *       - value: label
 *   - $panelLabel: Panel Label
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

// TODO debug now!

echo $this->NetCommonsHtml->script('/blocks/js/block_role_permissions.js');

//ラベル
if (! isset($panelLabel)) {
	$panelLabel = __d('blocks', 'Creatable settings');
}

//Camel形式に変換
$initializeParams = NetCommonsAppController::camelizeKeyRecursive(array('roles' => $roles));
?>

<div ng-controller="BlockRolePermissions"
	ng-init="initializeRoles(<?php echo h(json_encode($initializeParams, JSON_FORCE_OBJECT)); ?>)">

	<?php foreach ($settingPermissions as $permission => $label) : ?>
		<div class="form-group">
			<?php echo $this->NetCommonsForm->label('BlockRolePermission.' . $permission, h($label)); ?>
			<?php echo $this->BlockRolePermissionForm->checkboxBlockRolePermission('BlockRolePermission.' . $permission); ?>
			<?php // debug now!! ?>
			<div class="form-inline"><span class="checkbox-separator"></span><div class="form-group"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][room_administrator][value]" id="BlockRolePermissionContentCommentCreatableRoomAdministratorValue_" value="0" disabled="disabled"><input type="checkbox" name="data[BlockRolePermission][content_comment_creatable][room_administrator][value]" disabled="disabled" value="1" id="BlockRolePermissionContentCommentCreatableRoomAdministratorValue" checked="checked"><label for="BlockRolePermissionContentCommentCreatableRoomAdministratorValue">ルーム管理者</label></div><span class="checkbox-separator"></span><div class="form-group"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][chief_editor][value]" id="BlockRolePermissionContentCommentCreatableChiefEditorValue_" value="0" disabled="disabled"><input type="checkbox" name="data[BlockRolePermission][content_comment_creatable][chief_editor][value]" disabled="disabled" value="1" id="BlockRolePermissionContentCommentCreatableChiefEditorValue" checked="checked"><label for="BlockRolePermissionContentCommentCreatableChiefEditorValue">編集長</label></div><span class="checkbox-separator"></span><div class="form-group"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][editor][id]" id="BlockRolePermissionContentCommentCreatableEditorId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][editor][roles_room_id]" value="3" id="BlockRolePermissionContentCommentCreatableEditorRolesRoomId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][editor][block_key]" value="47d26e7ca2e7d92327935b5af4971fb0" id="BlockRolePermissionContentCommentCreatableEditorBlockKey"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][editor][permission]" value="content_comment_creatable" id="BlockRolePermissionContentCommentCreatableEditorPermission"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][editor][value]" id="BlockRolePermissionContentCommentCreatableEditorValue_" value="0"><input type="checkbox" name="data[BlockRolePermission][content_comment_creatable][editor][value]" ng-click="clickRole($event, 'content_comment_creatable', 'editor')" value="1" id="BlockRolePermissionContentCommentCreatableEditorValue" checked="checked"><label for="BlockRolePermissionContentCommentCreatableEditorValue">編集者</label></div><span class="checkbox-separator"></span><div class="form-group"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][general_user][id]" id="BlockRolePermissionContentCommentCreatableGeneralUserId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][general_user][roles_room_id]" value="4" id="BlockRolePermissionContentCommentCreatableGeneralUserRolesRoomId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][general_user][block_key]" value="47d26e7ca2e7d92327935b5af4971fb0" id="BlockRolePermissionContentCommentCreatableGeneralUserBlockKey"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][general_user][permission]" value="content_comment_creatable" id="BlockRolePermissionContentCommentCreatableGeneralUserPermission"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][general_user][value]" id="BlockRolePermissionContentCommentCreatableGeneralUserValue_" value="0"><input type="checkbox" name="data[BlockRolePermission][content_comment_creatable][general_user][value]" ng-click="clickRole($event, 'content_comment_creatable', 'generalUser')" value="1" id="BlockRolePermissionContentCommentCreatableGeneralUserValue" checked="checked"><label for="BlockRolePermissionContentCommentCreatableGeneralUserValue">一般</label></div><span class="checkbox-separator"></span><div class="form-group"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][visitor][id]" id="BlockRolePermissionContentCommentCreatableVisitorId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][visitor][roles_room_id]" value="5" id="BlockRolePermissionContentCommentCreatableVisitorRolesRoomId"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][visitor][block_key]" value="47d26e7ca2e7d92327935b5af4971fb0" id="BlockRolePermissionContentCommentCreatableVisitorBlockKey"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][visitor][permission]" value="content_comment_creatable" id="BlockRolePermissionContentCommentCreatableVisitorPermission"><input type="hidden" name="data[BlockRolePermission][content_comment_creatable][visitor][value]" id="BlockRolePermissionContentCommentCreatableVisitorValue_" value="0"><input type="checkbox" name="data[BlockRolePermission][content_comment_creatable][visitor][value]" ng-click="clickRole($event, 'content_comment_creatable', 'visitor')" value="1" id="BlockRolePermissionContentCommentCreatableVisitorValue"><label for="BlockRolePermissionContentCommentCreatableVisitorValue">参観者</label></div></div>
		</div>
	<?php endforeach; ?>
</div>
