<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
	
	$errs 	= false;
	$error	= array();
	if(empty($name)):
		$error["name"]	= 'Please enter the Name.';
		$errs			= true;
	endif;
	
	if($errs == true):
		$return = json_encode($error);
	else:
		$sql	= "UPDATE ".$wpdb -> prefix."group_sets SET group_name = '".$name."', modifiedDate = now() WHERE id = '".$group_id."' AND group_leader = '".$member_id."'";
		$query	= $wpdb -> query($sql);
		if($query):
			$return = json_encode(array("success"=> "yes")); 
		else:
			$return = json_encode(array("success"=> "no"));
		endif;	
	endif;
	echo $return;
endif;	
?>