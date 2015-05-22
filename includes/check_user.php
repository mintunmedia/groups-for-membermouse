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
	
	$userSql	= "SELECT ID FROM ".$wpdb -> prefix."users WHERE user_email = '".$user."' OR user_login = '".$user."'";
	$userResult	= $wpdb -> get_row($userSql);
	if(count($userResult) > 0):
		$user_id 		= $userResult -> ID;
		$user_data		= get_userdata($user_id);
		$user_roles		= $user_data-> roles;
		if(in_array("administrator", $user_roles)):
			$msg["error"] = 'This user already has an administrator role.';
		else:
			$groupSql		= "SELECT group_name FROM ".$wpdb -> prefix."group_sets WHERE group_leader = '".$user_id."'";
			$groupResult	= $wpdb -> get_row($groupSql);
			if(count($groupResult) > 0):
				if(!empty($groupResult -> group_name)):
					$group_name = $groupResult -> group_name;
				else:
					$group_name = 'Group';
				endif;	
				$msg["error"] = 'This member is already a Group Leader of '.$group_name.'.';
			else:
				$checkMemSql	= "SELECT gm.group_id,g.group_name FROM ".$wpdb -> prefix."group_sets_members AS gm LEFT JOIN ".$wpdb -> prefix."group_sets AS g ON gm.group_id = g.id WHERE gm.member_id = '".$user_id."'";
				$checkMemResult	= $wpdb -> get_row($checkMemSql);
				if(count($checkMemResult) > 0):
					if(!empty($checkMemResult -> group_name)):
						$gName = $checkMemResult -> group_name;
					else:
						$gName = "Group";
					endif;
					$msg["error"] = "<font class=\"red-text\">This member is already registered to Group '".$gName."'.</font>";	
				else:
					$msg["success"] = $user_id;
				endif;	
			endif;
		endif;
	else:
		$msg["error"] = 'This member doesn\'t exist';
	endif;
	$return = json_encode($msg);
	echo $return;	
endif;
?>