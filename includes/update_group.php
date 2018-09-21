<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
	
		$sql	= "UPDATE ".$wpdb -> prefix."group_sets SET group_size = '".$group_size."', group_name = '".$group_name."', modifiedDate = now() WHERE id = '".$gId."'";
		$query	= $wpdb -> query($sql);
		if($query):
			$return = json_encode(array("success"=> "yes")); 
		else:
			$return = json_encode(array("success"=> "no"));
		endif;	
	echo $return;
endif;	
?>