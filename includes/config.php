<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;
$totalSql	= "SELECT COUNT(id) AS total FROM ".$wpdb -> prefix."group_items WHERE 1";
$totalRes	= $wpdb -> get_row($totalSql);
$count		= $totalRes -> total;
$show		= 0;
if(isset($_GET["notice"]) && !empty($_GET["notice"])):
	$notice 	= $_GET["notice"];
	$delSql		= "DELETE FROM ".$wpdb -> prefix."group_notices WHERE id = '".$notice."'";
	$delQuery	= $wpdb -> query($delSql);
	if($delQuery):?>
		<script type="text/javascript">
			window.location = 'admin.php?page=groupsformm&ndelete=1';
		</script>
<?php
	else:?>
		<script type="text/javascript">
			window.location = 'admin.php?page=groupsformm&ndelete=0';
		</script>
<?php	
	endif;
endif;	
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

$targetpage = 'admin.php?page=groupsformm';
if(!empty($show)):
	$targetpage .= '&show='.$show;
endif;
$sql 		= "SELECT * FROM ".$wpdb -> prefix."group_items WHERE 1 ORDER BY createdDate DESC LIMIT $start, $limit";
$results	= $wpdb -> get_results($sql);	

$group_id 	= get_option("mm_custom_field_group_id");
?>
<h2>Define Group Types</h2>
<div id="group_popup_msg">
<?php if(isset($_GET["delete"])):?>
	<?php if($_GET["delete"] == 1):?>
		<div class="group_success">Successfully deleted the Group.</div>
	<?php elseif($_GET["delete"] == 0):?>
		<div class="group_failure">Some error occured please try again later.</div>
	<?php endif;?>
<?php endif;?>

<?php if(isset($_GET["ndelete"])):?>
	<?php if($_GET["ndelete"] == 1):?>
		<div class="group_success">Successfully deleted the Notice.</div>
	<?php elseif($_GET["ndelete"] == 0):?>
		<div class="group_failure">Some error occured please try again later.</div>
	<?php endif;?>
<?php endif;?>
</div>

<div class="membermousegroupbuttoncontainer">
	<a class="group-button button-green button-small" title="Create Group Type" id="create_group">Create Group Type</a>
</div>

<?php if(count($results) == 0) { ?>
<p style="margin-top:90px;"><em>No group types defined.</em></p>
<?php } else { ?>
<?php echo MemberMouseGroupAddon::MemberMouseGroupPagination($limit, $count, $page, $start, $targetpage, 'group types');?>
<table class="widefat" id="mm-data-grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Group Leader Access</th>
			<!--th>Leader Associated Cost</th-->
			<th>Member Access</th>
			<!--th>Member Associated Cost</th-->
			<th>Group Size</th>
			<th>Purchase Link</th>
			<!--th>Description</th-->
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
<?php	foreach($results as $res):
			$leadermemSql 		= "SELECT name FROM mm_membership_levels WHERE id = '".$res -> leader_memlevel."'";
			$leadermemResult	= $wpdb -> get_row($leadermemSql);
			$leaderCost			= "";
			if(!empty($res -> group_leader_cost)):
				$leaderSql			= "SELECT name FROM mm_products WHERE id = '".$res -> group_leader_cost."'";	
				$leaderResult		= $wpdb -> get_row($leaderSql);
				$leaderCost			= $leaderResult -> name;	
			endif;
			
			$membermemSql 		= "SELECT name FROM mm_membership_levels WHERE id = '".$res -> member_memlevel."'";
			$membermemResult	= $wpdb -> get_row($membermemSql);
			$memberCost			= "";
			if(!empty($res -> group_member_cost)):
				$memberSql			= "SELECT name FROM mm_products WHERE id = '".$res -> group_member_cost."'";	
				$memberResult		= $wpdb -> get_row($memberSql);
				$memberCost			= $memberResult -> name;	
			endif;
			?> 		
			<tr>
				<td><?php echo $res -> name;?></td>
				<td><?php echo $leadermemResult -> name;?></td>
				<!--td><?php if(!empty($leaderCost)):echo $leaderCost;else: echo "N/A";endif;?></td-->
				<td><?php echo $membermemResult -> name;?></td>
				<!--td><?php if(!empty($memberCost)):echo $memberCost;else: echo "N/A";endif;?></td-->
				<td><?php echo $res -> group_size;?></td>
				<td>
					<a href="javascript:void(0)" title="Get Purchase Link" class="group-button button-small" onclick="javascript:MGROUP.showPurchaseLink('<?php echo $res -> id;?>','<?php echo $group_id;?>');">
						<?php echo MM_Utils::getIcon('money', '', '1.3em', '1px', '', 'margin-right:0px;'); ?>
					</a>
				</td>
				<!--td><?php echo $res -> description;?></td-->
				<?php 
				$editActionUrl = 'onclick="javascript:MGROUP.editGroup(\''.$res -> id.'\');"';
				$deleteActionUrl = 'onclick="javascript:MGROUP.deleteGroup(\''.$res -> id.'\');"';
				?>
				<td>
					<?php echo MM_Utils::getEditIcon("Edit Group Type", 'margin-left:5px;', $editActionUrl); ?>
					<?php echo MM_Utils::getDeleteIcon("Delete Group Type", 'margin-left:5px;', $deleteActionUrl); ?>
				</td>
			</tr>
<?php	endforeach;?>	
	</tbody>
</table>
<?php } ?>
<!--<div class="smarttag_message">
	<h3>Please add the following to your checkout page within the [MM_Form type='checkout'] and [/MM_Form] tags:</h3>
	<p>[MM_Form_Field type="custom-hidden" id="<?php // echo $group_id;?>"]</p>
</div>-->
<?php
$noticeSql		= "SELECT * FROM ".$wpdb -> prefix."group_notices WHERE msg_type = '0' ORDER BY createdDate DESC";
$noticeResults 	= $wpdb -> get_results($noticeSql);
$noticeCount	= count($noticeResults);
if($noticeCount > 0):?>
	<div class="group_notices">
		<h3>Notices</h3>
		<table class="widefat" id="mm-data-grid">
			<thead>
				<tr>
					<th>Name</th>
					<th width="60px">Action</th>
				</tr>
			</thead>
			<tbody>
<?php		foreach($noticeResults as $noticeResult):
				$groupSql		= "SELECT group_name FROM ".$wpdb -> prefix."group_sets WHERE id = '".$noticeResult -> group_id."'";
				$groupResult	= $wpdb -> get_row($groupSql);
				$groupName		= $groupResult -> group_name;
				
				$userSql		= "SELECT user_email FROM ".$wpdb -> prefix."users WHERE ID = '".$noticeResult -> user_id."'";
				$userResult		= $wpdb -> get_row($userSql);
				$userEmail		= $userResult -> user_email;
				
				$leaderSql		= "SELECT user_email FROM ".$wpdb -> prefix."users WHERE ID = '".$noticeResult -> leader_id."'";
				$leaderResult	= $wpdb -> get_row($leaderSql);
				$leaderEmail	= $leaderResult -> user_email;
?>
				<tr>
					<td>Member <font style="color:#FF0000;"><?php echo $userEmail;?></font> failed to join <?php echo $groupName;?> (<?php echo $leaderEmail;?>) because it was full. Please cancel that member account and inform the group leader.</td>
					<td>
						<a title="Delete Notice" href="admin.php?page=groupsformm&notice=<?php echo $noticeResult -> id;?>">Delete Notice</a>
					</td>
				</tr>	
<?php		endforeach;?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>	