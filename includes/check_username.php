<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;
	$userId		= 0;
	$userSql	= "SELECT * FROM ".$wpdb -> prefix."users WHERE user_login = '".$username."' OR user_email = '".$username."'";
	$userResult	= $wpdb -> get_row($userSql);
	if(count($userResult) > 0):
		$userId	= $userResult -> ID;
		$checkMemSql	= "SELECT gm.group_id,g.group_name FROM ".$wpdb -> prefix."group_sets_members AS gm LEFT JOIN ".$wpdb->prefix."group_sets AS g ON gm.group_id = g.id WHERE gm.member_id = '".$userId."' AND gm.member_status = 1";
		$checkMemResult	= $wpdb -> get_row($checkMemSql);
		if(count($checkMemResult) > 0):
			if(!empty($checkMemResult -> group_name)):
				$gName = $checkMemResult -> group_name;
			else:
				$gName = "Group";
			endif;
			$msg["error"] = "<font class=\"red-text\">This member is already registered to Group '".$gName."'.</font>";
		else:
			$leaderSql		= "SELECT group_name FROM ".$wpdb -> prefix."group_sets WHERE group_leader = '".$userId."'";
			$leaderResult	= $wpdb -> get_row($leaderSql);
			if(count($leaderResult) > 0):
				if(!empty($leaderResult -> group_name)):
					$groupName = $leaderResult -> group_name;
				else:
					$groupName = "Group";
				endif;
				$msg["error"] = "<font class=\"red-text\">This member is already a Group leader of Group '".$groupName."'.</font>";
			else:
				$sizeSql	= "SELECT group_size FROM ".$wpdb -> prefix."group_sets WHERE id = '".$group_id."'";
				$sizeResult	= $wpdb -> get_row($sizeSql);
				$groupSize	= $sizeResult -> group_size;
				$activeSql	= "SELECT count(id) AS active FROM ".$wpdb -> prefix."group_sets_members WHERE group_id = '".$group_id."' AND member_status = 1";
				$activeResult	= $wpdb -> get_row($activeSql);
				$activeUsers	= $activeResult -> active;
				if($activeUsers < $groupSize):
					$msg[$userId] = '<font class="green-text">This member is available.</font>';
				else:
					$msg["error"] = '<font class="red-text">There is already '.$groupSize.' members in this group.</font>';
				endif;
			endif;
		endif;
	else:
		$msg["error"] = '<font class="red-text">No member found with this username or email.</font>';
	endif;
	$return = json_encode($msg);
	echo $return;
endif;
