<?php
include_once( WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php" );
include_once( WP_PLUGIN_DIR . "/membermouse/includes/init.php" );
global $wpdb, $current_user;?>
<script type="text/javascript" src="<?php echo get_bloginfo("url");?>/wp-content/plugins/membermouse/resources/js/admin/mm-details_access_rights.js"></script>
<div id="create_group_background" style="display:none;">
	<div id="create_group_loading" style="display:none;"></div>
	<div id="create_group_content" style="display:none;"></div>
</div>
<div id="group_popup_msg">
<?php if(isset($_GET["delete"])):?>
	<?php if($_GET["delete"] == 1):?>
		<div class="group_success">The operation completed successfully.</div>
	<?php elseif($_GET["delete"] == 0):?>
		<div class="group_failure">An error occured. Please try again later.</div>
	<?php endif;?>
<?php endif;?>

<?php if(isset($_GET["ndelete"])):?>
	<?php if($_GET["ndelete"] == 1):?>
		<div class="group_success">Successfully deleted the notice.</div>
	<?php elseif($_GET["ndelete"] == 0):?>
		<div class="group_failure">An error occured. Please try again later.</div>
	<?php endif;?>
<?php endif;?>
</div>
<?php
if(isset($_GET["notice"]) && !empty($_GET["notice"])):
	$notice 	= $_GET["notice"];
	$delSql		= "DELETE FROM ".$wpdb -> prefix."group_notices WHERE id = '".$notice."'";
	$delQuery	= $wpdb -> query($delSql);
	if($delQuery):?>
		<script type="text/javascript">
			window.location = 'admin.php?page=membermousemanagegroup&ndelete=1';
		</script>
<?php
	else:?>
		<script type="text/javascript">
			window.location = 'admin.php?page=membermousemanagegroup&ndelete=0';
		</script>
<?php	
	endif;
endif;
$sql	= "SELECT id, group_name FROM ".$wpdb -> prefix."group_sets WHERE group_leader = '".$current_user -> ID."'";
$result	= $wpdb -> get_row($sql);
if(count($result) > 0):
	$gid 	= $result -> id;
	$totalSql	= "SELECT COUNT(id) AS total FROM ".$wpdb -> prefix."group_sets_members WHERE group_id = '".$gid."'";
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

	$targetpage = 'admin.php?page=membermousemanagegroup';
	if(!empty($show)):
		$targetpage .= '&show='.$show;
	endif;
	$gMemSql		= "SELECT * FROM ".$wpdb -> prefix."group_sets_members WHERE group_id = '".$gid."' ORDER BY createdDate DESC LIMIT $start, $limit";
	$gMemResults	= $wpdb -> get_results($gMemSql);?>
	
	<h2><em><?php echo $result->group_name; ?></em> Management Dashboard</h2>

		<div class="membermousegroupbuttoncontainer">
			<a class="group-button button-green button-small" title="Edit Group Name" id="edit_group" onclick="javascript:MGROUP.editGroupNameForm('<?php echo $gid;?>','<?php echo $current_user -> ID;?>');">
				Edit Group Name
			</a>&nbsp;&nbsp;
			<a class="group-button button-green button-small" title="Signup Link" id="purchase_link" onclick="javascript:MGROUP.showMemberPurchaseLink('<?php echo $gid;?>', '<?php echo $current_user -> ID;?>');">
				Signup Link
			</a>
		</div>
	<div class="clear"></div>
<?php if(count($gMemResults) == 0 ) { ?>
<p><em>No members found.</em></p>
<?php } else { ?>
<?php
	echo MemberMouseGroupAddon::MemberMouseGroupPagination($limit, $count, $page, $start, $targetpage, 'members');?>
	<table class="widefat" id="mm-data-grid" style="width:96%">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Phone</th>
				<!--th>Membership Level</th-->
				<th>Registered</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
<?php		foreach($gMemResults as $gMemRes):
				$userSql		= "SELECT * FROM ".$wpdb -> prefix."users WHERE ID = '".$gMemRes -> member_id."'";
				$userResult		= $wpdb -> get_row($userSql);
				$registered		= $userResult -> user_registered;
				$memSql			= "SELECT * FROM mm_user_data WHERE wp_user_id = '".$gMemRes -> member_id."'";
				$memResult		= $wpdb -> get_row($memSql);
				$firstName 		= $memResult -> first_name;
				$lastName 		= $memResult -> last_name;
				$email 			= $userResult -> user_email;
				$phone 			= empty($memResult -> phone) ? "&mdash;" : $memResult -> phone;
				$statusId 		= $memResult -> status;
				$membershipId	= $memResult -> membership_level_id;
				$levelSql 		= "SELECT name FROM mm_membership_levels WHERE id = '".$membershipId."'";
				$levelResult	= $wpdb -> get_row($levelSql);
				$membershipName	= $levelResult -> name;
				$redirecturl  		= "";	
				$crntMemberId 		= $gMemRes -> member_id;
				$member		 		= new MM_User($crntMemberId);
				$url 				= "javascript:mmjs.changeMembershipStatus('".$crntMemberId."', ";
				$url 		   	   .= $membershipId.", ".MM_Status::$CANCELED.", '".$redirecturl."');";
				$cancellationHtml 	= "<a title=\"Cancel Member\" style=\"cursor: pointer;\" onclick=\"".$url."\"/>".MM_Utils::getIcon('stop', 'red', '1.2em', '1px')."</a>";?>		
				<tr>
					<td><?php echo $firstName.'&nbsp;'.$lastName;?></td>
					<td><?php echo $email;?></td>
					<td><?php echo $phone;?></td>
					<!--td><?php echo $membershipName;?></td-->
					<td><?php echo date('F d, Y h:m a',strtotime($registered));?></td>
					<td>
						<?php echo MM_Status::getImage($statusId); ?>		
					</td>
					<td>
						<?php if($statusId == MM_Status::$ACTIVE): ?>
							<?php echo $cancellationHtml;?>
							<?php echo MM_Utils::getDeleteIcon("This member has an active paid membership which must be canceled before they can be removed from the group", 'margin-left:5px;', '', true); ?>
						<?php else:?>	
							<?php 
							$deleteActionUrl = 'onclick="javascript:MGROUP.deleteGroupMember(\''.$gMemRes -> id.'\');"';
							echo MM_Utils::getDeleteIcon("Remove the member from this group", 'margin-left:5px;', $deleteActionUrl);
							?>
						<?php endif;?>	
					</td>
				</tr>
<?php		endforeach;?>		
	</tbody>
</table>
<?php } ?>
<?php
endif;
$noticeSql		= "SELECT * FROM ".$wpdb -> prefix."group_notices WHERE msg_type = '1' AND leader_id = '".$current_user -> ID."' ORDER BY createdDate DESC";
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
						<a title="Delete Notice" href="admin.php?page=membermousemanagegroup&notice=<?php echo $noticeResult -> id;?>">
							Delete Notice
						</a>
					</td>
				</tr>	
<?php		endforeach;?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>	