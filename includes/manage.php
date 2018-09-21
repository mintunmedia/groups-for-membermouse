<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;
$totalSql	= "SELECT COUNT(id) AS total FROM ".$wpdb -> prefix."group_sets WHERE 1 ORDER BY createdDate DESC";
$totalRes	= $wpdb -> get_row($totalSql);
$count		= $totalRes -> total;
$show		= 0;
if(isset($_GET["show"]) && !empty($_GET["show"])):
	$show = $_GET["show"];
endif;
			
if(!empty($show)):
	$limit = $show;
else:
	$limit = 10;
endif;

$page = 0;
if(isset($_GET["p"]) && !empty($_GET["p"])):
	$page 	= $_GET["p"];
	$start 	= ($page - 1) * $limit;
else:
	$start	= 0;	
endif;

if($page == 0):
	$page = 1;
endif;

$targetpage = 'admin.php?page=groupsformm&type=manage';
if(!empty($show)):
	$targetpage .= '&show='.$show;
endif;

$sql		= "SELECT * FROM ".$wpdb -> prefix."group_sets WHERE 1 ORDER BY createdDate DESC LIMIT $start, $limit";
$results	= $wpdb -> get_results($sql);
?>
<?php if(isset($_GET["msg"])):?>
	<div id="group_popup_msg">
		<?php if($_GET["msg"] == 1):?>
			<div class="group_success">The operation completed successfully.</div>
		<?php elseif($_GET["msg"] == 2):?>
			<div class="group_failure">An error occured. Please try again later.</div>
		<?php endif;?>
	</div>
<?php endif;?>
<h2>Manage Groups</h2>
<div class="membermousemanagegroupbuttoncontainer">
	<a href="javascript:MGROUP.GroupLeaderForm();" title="Create Group" class="group-button button-green button-small">Create Group</a>
</div>

<?php if(count($results) == 0) { ?>
<p><em>No groups created yet.</em></p>
<?php } else { ?>
<?php echo MemberMouseGroupAddon::MemberMouseGroupPagination($limit, $count, $page, $start, $targetpage, 'groups');?>
<table class="widefat" id="mm-data-grid" style="width:800px;">
	<thead>
		<tr>
			<th>Name</th>
			<th>Group Type</th>
			<th>Group Leader</th>
			<th># of Members</th>
			<th>Enrollment Link</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
<?php	foreach($results as $res):
			$userSql		= "SELECT user_email FROM ".$wpdb -> prefix."users WHERE ID = '".$res -> group_leader."'";
			$userResult		= $wpdb -> get_row($userSql);
			$groupTypeSql   = "SELECT name FROM ".$wpdb -> prefix."group_items WHERE id = '".$res -> group_template_id."'";
			$groupTypeResult = $wpdb -> get_row($groupTypeSql);
			$activeSql		= "SELECT count(id) AS active FROM ".$wpdb -> prefix."group_sets_members WHERE group_id = '".$res -> id."'";
			$activeResult	= $wpdb -> get_row($activeSql);
			?>
			<tr>
				<td><?php if(!empty($res -> group_name)): echo $res -> group_name;else: echo "Group";endif;?></td>
				<td><?php echo $groupTypeResult->name; ?></td>
				<td><a href="<?php echo MM_ModuleUtils::getUrl(MM_MODULE_MANAGE_MEMBERS, MM_MODULE_MEMBER_DETAILS_GENERAL).'&user_id='.$res -> group_leader; ?>" target="_blank"><?php echo $userResult -> user_email;?></a></td>
				<td><?php echo $activeResult -> active;?> of <?php echo $res -> group_size;?> members</td>
				<td>
					<a href="javascript:void(0)" title="Get Purchase Link" class="group-button button-small" onclick='javascript:MGROUP.showMemberPurchaseLink(<?php echo $res -> id;?>, <?php echo $res -> group_leader;?>);'>
						<?php echo MM_Utils::getIcon('money', '', '1.3em', '1px', '', 'margin-right:0px;'); ?>
					</a>
				</td>
				<td>
					<?php 
					$editActionUrl = 'onclick="javascript:MGROUP.editGroupForm(\''.$res -> id.'\');"';
					$deleteActionUrl = 'onclick="javascript:MGROUP.deleteGroupData(\''.$res -> id.'\');"';
					?>
					<?php echo MM_Utils::getEditIcon("Edit Group", 'margin-left:5px;', $editActionUrl); ?>
					<?php if($res -> group_status == 1):?>
						<a style="margin-left: 5px; cursor:pointer" title="Cancel Group" onclick="javascript:MGROUP.cancelGroup('<?php echo $res -> id;?>');" title="Cancel Group"><?php echo MM_Utils::getIcon('stop', 'red', '1.2em', '1px'); ?></a>
					<?php else:?>
						<?php echo MM_Utils::getDeleteIcon("Delete Group", 'margin-left:5px;', $deleteActionUrl); ?>
					<?php endif;?>
				</td>
			</tr>
<?php	endforeach;?>	
	</tbody>
</table>
<?php } ?>