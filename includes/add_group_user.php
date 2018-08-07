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