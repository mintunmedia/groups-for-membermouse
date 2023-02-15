<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if (count($data) > 0) :
	foreach ($data as $key => $value) :
		$$key = $value;
	endforeach;

	$userSql	= "SELECT ID FROM " . $wpdb->prefix . "users WHERE user_email = '" . $user . "' OR user_login = '" . $user . "'";
	$userResult	= $wpdb->get_row($userSql);

	write_groups_log($userResult, 'users results: ');

	if ($userResult) {
		write_groups_log('has results:');
		$user_id 		= $userResult->ID;
		$user_data		= get_userdata($user_id);
		$user_roles		= $user_data->roles;
		if (in_array("administrator", $user_roles)) {
			$msg["error"] = 'This user already has an administrator role.';
		} else {
			$groupSql		= "SELECT group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $user_id . "'";
			$groupResult	= $wpdb->get_row($groupSql);
			if ($groupResult) {
				if (!empty($groupResult->group_name)) {
					$group_name = $groupResult->group_name;
				} else {
					$group_name = 'Group';
				}
				$msg["error"] = 'This member is already a Group Leader of ' . $group_name . '.';
			} else {
				$checkMemSql	= "SELECT gm.group_id,g.group_name FROM " . $wpdb->prefix . "group_sets_members AS gm LEFT JOIN " . $wpdb->prefix . "group_sets AS g ON gm.group_id = g.id WHERE gm.member_id = '" . $user_id . "' AND gm.member_status = 1";
				$checkMemResult	= $wpdb->get_row($checkMemSql);
				if ($checkMemResult) {
					if (!empty($checkMemResult->group_name)) {
						$gName = $checkMemResult->group_name;
					} else {
						$gName = "Group";
					}
					$msg["error"] = "<font class=\"red-text\">This member is already registered to Group '" . $gName . "'.</font>";
				} else {
					$msg["success"] = $user_id;
				}
			}
		}
	} else {
		$msg["error"] = 'This member doesn\'t exist';
		write_groups_log('no results');
	}
	$return = json_encode($msg);
	echo $return;
endif;
