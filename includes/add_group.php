<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	$errs = false;

	if(empty($name)):
		$error["name"]	= 'Please enter the Name.';
		$errs			= true;
	endif;

	if(empty($leader_memlevel)):
		$error["leader_memlevel"]	= 'Please select the Group Leader Associated Access.';
		$errs						= true;
	endif;

	if(($lCost == 1) && empty($leader_cost)):
		$error["leader_cost"]	= 'Please select the Group Leader Associated Cost.';
		$errs					= true;
	endif;

	if(empty($member_memlevel)):
		$error["member_memlevel"]	= 'Please select the Group Member Associated Access.';
		$errs						= true;
	endif;

	if(($mCost == 1) && empty($member_cost)):
		$error["member_cost"]	= 'Please select the Group Member Associated Cost.';
		$errs					= true;
	endif;

	if(empty($group_size)):
		$error["group_size"]	= 'Please enter the Group Size.';
		$errs					= true;
	endif;

	if($errs == true):
		$return = json_encode($error);
	else:
		if(!empty($groupId)):
			$sql	= "UPDATE ".$wpdb -> prefix."group_items SET name = '".$name."', leader_memlevel = '".$leader_memlevel."',  member_memlevel = '".$member_memlevel."', group_leader_cost = '".$leader_cost."', group_member_cost = '".$member_cost."', group_size = '".$group_size."', modifiedDate = now() WHERE id = '".$groupId."'";
		else:
			$sql	= "INSERT INTO ".$wpdb -> prefix."group_items (id,name,leader_memlevel,member_memlevel,group_leader_cost,group_member_cost,group_size,createdDate,modifiedDate)VALUES('','".$name."','".$leader_memlevel."','".$member_memlevel."','".$leader_cost."','".$member_cost."','".$group_size."',now(),now())";
		endif;
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