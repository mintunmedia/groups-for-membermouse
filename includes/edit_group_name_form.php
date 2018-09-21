<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$name 		= "";
$group_id	= "";
if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
	
	$sql	= "SELECT id,group_name,group_leader FROM ".$wpdb -> prefix."group_sets WHERE id = '".$group_id."' AND group_leader = '".$member_id."'";
	$result	= $wpdb -> get_row($sql);
	if(count($result) > 0):
		$group_id	= $result -> id;
		$name 		= $result -> group_name;
		$member_id	= $result -> group_leader;
	endif;?>
	<div id="group_popup_container">
		<h2>
			<span class="group_title">Edit Group Name</span>
			<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
		</h2>
		<div id="group_popup_main">
			<div id="group_popup_msg" style="display:none;"></div>
			<table cellpadding="2" cellspacing="0" border="0" width="100%" style="float:left;">
				<tr>
					<td width="80">Name*</td>
					<td>
						<input type="text" name="name" value="<?php echo $name;?>" id="name" style="width:220px;" />
						<div class="groupError" id="nameErr"></div>
					</td>
				</tr>
			</table>
		</div>		
	<div id="popup_group_bottom">
		<div class="group-dialog-button-container">
			<a class="group-button button-blue" href="javascript:MGROUP.updateGroupName('<?php echo $group_id;?>','<?php echo $member_id;?>');">Save</a>&nbsp;&nbsp;
			<a class="group-button" href="javascript:MGROUP.closeGroupPopup();">Cancel</a>
			<input type="hidden" name="group_id" id="group_id" value="<?php echo $group_id;?>"/>
			<input type="hidden" name="member_id" id="member_id" value="<?php echo $member_id;?>"/>
		</div>
		<div class="group-loading-container" style="display:none;">
			<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
		</div>
	</div>
	</div>
</div>
</div>

<?php	
endif;
?>	