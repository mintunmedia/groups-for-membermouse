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
	$return		= array();
	$groupSql	= "DELETE FROM ".$wpdb -> prefix."group_sets WHERE id = '".$id."'";
	$groupQuery	= $wpdb -> query($groupSql);
	if($groupQuery):
		$memSql		= "DELETE FROM ".$wpdb -> prefix."group_sets_members WHERE group_id = '".$id."'";
		$memQuery	= $wpdb -> query($memSql);
		$return["success"]	= "yes";
	else:
		$return["success"]	= "no";
	endif;
	echo json_encode($return);	
endif;	
?>