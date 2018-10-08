<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	$groupSql	= "INSERT INTO ".$wpdb -> prefix."group_sets_members (id,group_id,member_id,createdDate,modifiedDate)VALUES('','".$group_id."','".$member_id."',now(),now())";
	$groupQuery	= $wpdb -> query($groupSql);
	if($groupQuery):
		$msg["success"]	= "yes";
	else:
		$msg["success"] = "no";
	endif;
	$return = json_encode($msg);
	echo $return;
endif;
?>