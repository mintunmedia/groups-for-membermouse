<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if (count($data) > 0) :
	foreach ($data as $key => $value) :
		$$key = $value;
	endforeach;

	$groupSql	= "INSERT INTO " . $wpdb->prefix . "group_sets_members (id,group_id,member_id,createdDate,modifiedDate)VALUES('','" . $group_id . "','" . $member_id . "',now(),now())";
	$groupQuery	= $wpdb->query($groupSql);
	if ($groupQuery) :
		$msg["success"]	= "yes";
	else :
		$msg["success"] = "no";
	endif;
	$return = json_encode($msg);
	echo $return;
endif;

/**
 * HOOK - Group User Added COMPLETE
 * @param $data array
 * @param $success bool
 */
do_action('groups_add_group_user_complete', $data, $msg['success']);
