<?php
global $wpdb;
if(!isset($wpdb)):
	require_once('../../../../wp-config.php');
    require_once('../../../../wp-includes/wp-db.php');
endif;
if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
endif;

$group			= "";
$user			= "";
$groupSize		= "";
$groupSql		= "SELECT id,name FROM ".$wpdb -> prefix."group_items ORDER BY createdDate DESC";
$groupResults	= $wpdb -> get_results($groupSql); 
?>
<div id="group_popup_container">
	<h2>
		<span class="group_title">Create Group</span>
		<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
	</h2>
	<div id="group_popup_main">
		<div id="group_popup_msg" style="display:none;"></div>
		<table cellpadding="2" cellspacing="0" border="0" width="100%" style="float:left;">
			<tr>
				<td width="140">Group Name*</td>
				<td>
					<div style="float:left;width:auto;">
						<input type="text" name="group_name" id="group_name" value=""  style="width:200px;"/>
					</div>
					<div class="groupError" id="groupNameErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td width="140">Group Type*</td>
				<td>
					<select name="group" id="group">
						<option value="">&mdash; select option &mdash;</option>
<?php					foreach($groupResults as $groupResult):?>
							<option value="<?php echo $groupResult -> id;?>" <?php if($groupResult -> id == $group): echo 'selected="selected"';endif;?>><?php echo $groupResult -> name;?></option>
<?php					endforeach;?>						
					</select>
					<div class="groupError" id="groupErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td width="140">Group Leader* <?php echo MM_Utils::getInfoIcon("Please enter the username or email address of the member to be add to this group. It cannot be an existing group leader, a member of another group or user with an administrator role.", "margin-right:4px;"); ?></td>
				<td>
					<div style="float:left;width:auto;">
						<input type="text" name="user" id="user" value="<?php echo $user;?>" onchange="javascript:MGROUP.checkGroupUser(this.value);" style="width:200px;"/>
						<input type="hidden" name="user_id" id="user_id" value="0"/>
					</div>
					<div id="userLoading" style="display:none;">
						<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
					</div>
					<div class="groupError" id="userErr"></div>
				</td>
			</tr>
		</table>
	</div>
	<div id="popup_group_bottom">
		<div class="group-dialog-button-container">
			<a class="group-button button-blue" href="javascript:MGROUP.createGroupLeader();">Create Group</a>&nbsp;&nbsp;
			<a class="group-button" href="javascript:MGROUP.closeGroupPopup();">Cancel</a>
		</div>
		<div class="group-loading-container" style="display:none;">
			<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
		</div>
	</div>
</div>