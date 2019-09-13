<?php
/**
 * Removes Group Member from Database table - group_sets_members
 * Deletes custom field value for Group ID.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	$cf_id = get_option("mm_custom_field_group_id");
	$sql	 = "DELETE FROM ". $wpdb->prefix ."group_sets_members WHERE id = '". $gmId ."'";
	$query = $wpdb->query($sql);

	// Clear Custom field for Group ID
	$member = new MM_User($member_id);
	$member->setCustomData($cf_id, '');

	// Cancel their subscription status (same as if their access was removed)
	$member->setStatus(MM_Status::$CANCELED);
	$member->commitStatusOnly();

	// Send Response Back
	if($query):
		$return = json_encode(array("success" => "yes"));
	else:
		$return = json_encode(array("success" => "no"));
	endif;
	echo $return;
endif;