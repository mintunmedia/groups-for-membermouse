<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if (count($data) > 0) :
	foreach ($data as $key => $value) :
		$$key = $value;
	endforeach;

	$errs = false;
	$msg  = array();
	if (empty($group)) :
		$msg["group"] = "Please select the Group type.";
		$errs = true;
	endif;

	if (empty($user)) :
		$msg["user"] = "Please enter the Group Leader.";
		$errs = true;
	else :
		$userSql	= "SELECT ID FROM " . $wpdb->prefix . "users WHERE user_email = '" . $user . "' OR user_login = '" . $user . "'";
		$userResult	= $wpdb->get_row($userSql);
		if (count($userResult) > 0) :
			$user_id 		= $userResult->ID;
			$user_data		= get_userdata($user_id);
			$user_roles		= $user_data->roles;
			if (in_array("administrator", $user_roles)) :
				$msg["user"] = 'This user already has an administrator role.';
				$errs		 = true;
			else :
				$groupSql		= "SELECT group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $user_id . "'";
				$groupResult	= $wpdb->get_row($groupSql);
				if (count($groupResult) > 0) :
					if (!empty($groupResult->group_name)) :
						$group_name = $groupResult->group_name;
					else :
						$group_name = 'Group';
					endif;
					$msg["user"] = 'This member is already the Group Leader of ' . $group_name . '.';
					$errs = true;
				else :
					$checkMemSql	= "SELECT gm.group_id,g.group_name FROM " . $wpdb->prefix . "group_sets_members AS gm LEFT JOIN " . $wpdb->prefix . "group_sets AS g ON gm.group_id = g.id WHERE gm.member_id = '" . $user_id . "' AND member_status = 1";
					$checkMemResult	= $wpdb->get_row($checkMemSql);
					if (count($checkMemResult) > 0) :
						if (!empty($checkMemResult->group_name)) :
							$gName = $checkMemResult->group_name;
						else :
							$gName = "Group";
						endif;
						$msg["user"] = "<font class=\"red-text\">This member is already registered to Group '" . $gName . "'.</font>";
						$errs = true;
					endif;
				endif;
			endif;
		else :
			$msg["user"] = 'This member doesn\'t exist.';
			$errs = true;
		endif;
	endif;

	if ($errs == false) :
		$gNameSql		= "SELECT group_size FROM " . $wpdb->prefix . "group_items WHERE id = '" . $group . "'";
		$gNameResult	= $wpdb->get_row($gNameSql);
		$group_size		= $gNameResult->group_size;
		$sql	= "INSERT INTO " . $wpdb->prefix . "group_sets(id,group_template_id,group_name,group_size,group_leader,group_status,createdDate,modifiedDate)VALUES('','" . $group . "','" . $group_name . "','" . $group_size . "','" . $user_id . "','1',now(),now())";
		$query  = $wpdb->query($sql);
		if ($query) :
			$updateUser 	= wp_update_user(array('ID' => $user_id, 'role' => 'Group Leader'));
			$msg["success"] = 'yes';
		else :
			$msg["success"] = 'no';
		endif;
	endif;

	$return = json_encode($msg);
	echo $return;
endif;

/**
 * HOOK - Create Group COMPLETE
 * @param $data array
 * @param $success bool
 */
do_action('groups_create_group_complete', $data, $msg['success']);
