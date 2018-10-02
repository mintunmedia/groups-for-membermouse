<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	$sql	= "DELETE FROM ".$wpdb -> prefix."group_sets_members WHERE id = '".$gmId."'";
	$query	= $wpdb -> query($sql);
	if($query):
		$return = json_encode(array("success" => "yes"));
	else:
		$return = json_encode(array("success" => "no"));
	endif;
	echo $return;
endif;