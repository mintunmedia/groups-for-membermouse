<?php
global $wpdb;
if(!isset($wpdb)):
	require_once('../../../../wp-config.php');
    require_once('../../../../wp-includes/wp-db.php');
endif;
include_once("../../membermouse/includes/mm-constants.php");
include_once("../../membermouse/includes/init.php");
$groupId			= "";
$name				= "";
$leader_memlevel	= 2;
$member_memlevel	= 2;
$group_leader_cost	= '';
$group_member_cost	= '';
$group_size			= "";
$description		= "";
if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;

	if(!empty($groupId)):
		$groupSql			= "SELECT id,name,leader_memlevel,member_memlevel,group_leader_cost,group_member_cost,group_size,description FROM ".$wpdb -> prefix."group_items WHERE id = '".$groupId."'";
		$groupResult		= $wpdb -> get_row($groupSql);
		$groupId			= $groupResult -> id;
		$name				= $groupResult -> name;
		$leader_memlevel	= $groupResult -> leader_memlevel;
		$member_memlevel	= $groupResult -> member_memlevel;
		$group_leader_cost	= $groupResult -> group_leader_cost;
		$group_member_cost	= $groupResult -> group_member_cost;
		$group_size			= $groupResult -> group_size;
		$description		= $groupResult -> description;	
	endif;
endif;
$leaderSql		= "SELECT lp.product_id AS product_id,p.id AS id,p.name AS name FROM mm_membership_level_products AS lp LEFT JOIN mm_products AS p ON lp.product_id = p.id WHERE lp.membership_id ='".$leader_memlevel."' ORDER BY p.name ASC";
$leaderResults	= $wpdb -> get_results($leaderSql);

$memberSql		= "SELECT lp.product_id AS product_id,p.id AS id,p.name AS name FROM mm_membership_level_products AS lp LEFT JOIN mm_products AS p ON lp.product_id = p.id WHERE lp.membership_id ='".$member_memlevel."' ORDER BY p.name ASC";
$memberResults	= $wpdb -> get_results($memberSql);
?>
<div id="group_popup_container">
	<h2>
		<span class="group_title"><?php if(!empty($groupId)):?>Edit<?php else:?>Create<?php endif;?> Group Type</span>
		<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
	</h2>
	<div id="group_popup_main">
		<div id="group_popup_msg" style="display:none;"></div>
		<table cellpadding="2" cellspacing="0" border="0" width="100%" style="float:left;">
			<tr>
				<td width="140">Name*</td>
				<td>
					<input type="text" name="name" class="long-text" value="<?php echo $name;?>" id="name"/>
					<div class="groupError" id="nameErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td><strong>Group Leader</strong></td>
				<td></td>
			</tr>	
			<tr>
				<td>Associated Access*</td>
				<td>
					<div id="group_membership_access_container">
						<div style="float:left;width:auto;">
							<select id='leader_memlevel' name='leader_memlevel' onchange="javascript:MGROUP.changeGroupLeaderCost(this.value);">
				<?php			echo MM_HtmlUtils::getMemberships($leader_memlevel, true); ?>
							</select>
						</div>
						
						<?php echo MM_Utils::getIcon('warning', 'red', '1.2em', '1px', "IMPORTANT: Make sure that the membership level you select here is not setting the WordPress role. Do this by editing the membership level in MemberMouse and seting the WordPress role option to '&mdash; Don't set or change role &mdash;'.", "padding-top:6px; padding-left:5px;"); ?>
						<div id="leadermemLoading" style="display:none;">
							<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
						</div>
	
					</div>
					<div class="groupError" id="leadermemlevelErr"></div>
				</td>
			</tr>
			<tr id="leader_associated_cost">
				<td><?php if(count($leaderResults) > 0):?>Associated Cost*<?php endif;?></td>
				<td>
<?php				if(count($leaderResults) > 0):?>				
						<select name="group_leader_cost" id="group_leader_cost">
							<option value="">&mdash; select option &mdash;</option>
<?php						foreach($leaderResults as $leaderResult):?>
								<option value="<?php echo $leaderResult -> id;?>" <?php if($group_leader_cost == $leaderResult -> id): echo 'selected="selected"';endif;?>><?php echo $leaderResult -> name;?></option>
<?php						endforeach;?>							
						</select>
						<input type="hidden" id="leaderCost" name="leaderCost" value="1"/>
<?php				else:?>
						<input type="hidden" name="leaderCost" id="leaderCost" value="0"/>
<?php				endif;?>					
					<div class="groupError" id="groupLeaderCostErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td><strong>Group Member</strong></td>
				<td></td>
			</tr>
			<tr>
				<td>Associated Access*</td>
				<td>
					<div id="group_membership_access_container">
						<div style="float:left;width:auto;">
							<select id='member_memlevel' name='member_memlevel' onchange="javascript:MGROUP.changeGroupMemberCost(this.value);">
				<?php			echo MM_HtmlUtils::getMemberships($member_memlevel, true); ?>
							</select>
						</div>
						<div id="memberLoading" style="display:none;">
							<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
						</div>	
					</div>
					<div class="groupError" id="membermemlevelErr"></div>
				</td>
			</tr>
			<tr id="member_associated_cost">
				<td><?php if(count($memberResults) > 0):?>Associated Cost*<?php endif;?></td>
				<td>
<?php				if(count($memberResults) > 0):?>				
						<select name="group_member_cost" id="group_member_cost">
							<option value="">&mdash; select option &mdash;</option>
<?php						foreach($memberResults as $memberResult):?>
								<option value="<?php echo $memberResult -> id;?>" <?php if($group_member_cost == $memberResult -> id): echo 'selected="selected"';endif;?>><?php echo $memberResult -> name;?></option>
<?php						endforeach;?>					
						</select>
						<input type="hidden" id="memberCost" name="memberCost" value="1"/>
<?php				else:?>
						<input type="hidden" id="memberCost" name="memberCost" value="0"/>
<?php				endif;?>					
					<div class="groupError" id="groupMemberCostErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td>Group Size*</td>
				<td>
					<input type="text" style="width: 125px;" value="<?php echo $group_size;?>" id="group_size" name="group_size"/>
					<div class="groupError" id="groupSizeErr"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="group-divider"></div>
				</td>
			</tr>
			<tr>
				<td>Description*</td> 
				<td>
					<textarea rows="3" cols="55" id="description" name="description"><?php echo $description;?></textarea>
					<div class="groupError" id="descriptionErr"></div>
				</td>
			</tr>
		</table>
	</div>
	<div id="popup_group_bottom">
		<div class="group-dialog-button-container">
			<?php if(!empty($groupId)):?>
				<a class="group-button button-blue" href="javascript:MGROUP.saveGroupForm('<?php echo $groupId;?>');">Edit Group Type</a>&nbsp;&nbsp;
			<?php else:?>
				<a class="group-button button-blue" href="javascript:MGROUP.saveGroupForm('0');">Save Group Type</a>&nbsp;&nbsp;
			<?php endif;?>
			<a class="group-button" href="javascript:MGROUP.closeGroupPopup();">Cancel</a>
			<input type="hidden" name="groupId" id="groupId" value="<?php echo $groupId;?>"/>
		</div>
		<div class="group-loading-container" style="display:none;">
			<img src="<?php echo MGROUP_IMG;?>loading.gif" alt=""/>
		</div>
	</div>
</div>