<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
	
	$sql	= "DELETE FROM ".$wpdb -> prefix."group_items WHERE id = '".$groupId."'";
	$query	= $wpdb -> query($sql);
	if($query):
		$return = json_encode(array("success" => "yes"));
	else:
		$return = json_encode(array("success" => "no"));
	endif;
	echo $return;	
endif;	