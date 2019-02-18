<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;
$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	$groupSql		= "SELECT * FROM ".$wpdb -> prefix."group_sets WHERE id = '".$gId."'";
	$groupResult	= $wpdb -> get_row($groupSql);
	$gId			= $groupResult -> id;
	$group_size		= $groupResult -> group_size;
	$group_name 	= $groupResult -> group_name ? $groupResult -> group_name : 'Group';
	$group_leader = $groupResult -> group_leader;

	$groupTypeSql   = "SELECT * FROM ".$wpdb->prefix."group_items WHERE id = '".$groupResult->group_template_id."'";
	$groupTypeResult = $wpdb -> get_row($groupTypeSql);
	$group_type = $groupTypeResult->name;


	// NEW -- query group lider
	$sql		= "SELECT user_email FROM ".$wpdb -> prefix."group_sets AS gs, ".$wpdb -> prefix."users AS wu WHERE wu.id = gs.group_leader AND gs.id=".$gId;
	$leader_email	= $wpdb -> get_var($sql);

?>
	<div id="group_popup_container">
		<h2>
			<span class="group_title">Edit Group</span>
			<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
		</h2>
		<div id="group_popup_main">
			<div id="group_popup_msg" style="display:none;"></div>
			<table cellpadding="2" cellspacing="0" border="0" width="100%" style="float:left;">
				<tr>
					<td width="140">Group Name*</td>
					<td>
						<input type="text" name="group_name" class="long-text" value="<?php echo $group_name;?>" id="group_name" style="width:125px;"/>
						<div class="groupError" id="groupNameErr"></div>
					</td>
				</tr>
				<tr>
				<tr>
					<td width="140">Group Leader*</td>
					<td>
						<input type="text" name="group_leader" class="long-text" value="<?php echo $leader_email;?>" disabled style="width:125px;"/>
						<div class="groupError" id="groupLeaderErr"></div>
					</td>
				</tr>
				<tr>
					<td width="140">Size*</td>
					<td>
						<input type="text" name="group_size" class="long-text" value="<?php echo $group_size;?>" id="group_size" style="width:125px;"/>
						<div class="groupError" id="groupSizeErr"></div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="group-divider"></div>
					</td>
				</tr>
				<tr>
					<td valign="top" style="padding-top:6px;">Add Member to Group <?php echo MM_Utils::getInfoIcon("Enter the username or email address of the member to be add to this group. It cannot be an existing group leader, a member of another group or user with an administrator role.", "margin-right:4px;"); ?></td>
					<td>
						<div id="add_user_input_container">
							<input type="text" name="username" value="" id="username" onchange="MGROUP.checkUsername('<?php echo $gId;?>');" style="width:200px;" />
							<input type="hidden" name="user_id" value="0" id="user_id"/>
						</div>
						<div id="add_user_container">
							<a class="group-button" title="Check Availability" onclick="MGROUP.checkUsername('<?php echo $gId;?>');">Check Availability</a>
						</div>
						<div id="add_user_loading" style="display:none;">
							<i class="fa fa-circle-o-notch fa-spin fa-2x" aria-hidden="true"></i>
						</div>
						<div id="add_user_msg" style="display:none;"></div>
					</td>
				</tr>
			</table>
		</div>
		<div id="popup_group_bottom">
			<div class="group-dialog-button-container">
				<a class="group-button button-blue" href="javascript:MGROUP.updateGroup('<?php echo $gId;?>');">Update</a>&nbsp;&nbsp;
				<a class="group-button" href="javascript:MGROUP.closeGroupPopup();">Cancel</a>
				<input type="hidden" name="gId" id="gId" value="<?php echo $gId;?>"/>
			</div>
			<div class="group-loading-container" style="display:none;">
				<i class="fa fa-circle-o-notch fa-spin fa-2x" aria-hidden="true"></i>
			</div>
		</div>
	</div>
</div>
<?php
endif;
?>