<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

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