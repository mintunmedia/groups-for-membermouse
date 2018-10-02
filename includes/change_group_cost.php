<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$data = sanitize_post($_POST);
if(count($data) > 0):
	foreach($data as $key => $value):
		$$key = $value;
	endforeach;

	if($type == "leader"):
		$selectName	= "group_leader_cost";
		$hiddenName = "leaderCost";
		$divId		= "leader_associated_cost";
	else:
		$selectName = "group_member_cost";
		$hiddenName	= "memberCost";
		$divId		= "member_associated_cost";
	endif;

	$costVal = 0;
	$productSql		= "SELECT lp.product_id AS product_id,p.id AS id,p.name AS name FROM mm_membership_level_products AS lp LEFT JOIN mm_products AS p ON lp.product_id = p.id WHERE lp.membership_id ='".$levelId."' ORDER BY p.name ASC";
	$productResults	= $wpdb -> get_results($productSql);
	echo '<td>';
		if(count($productResults) > 0): echo 'Associated Cost*';endif;
	echo '</td>';
	echo '<td>';
		if(count($productResults) > 0):
			echo '<select name="'.$selectName.'" id="'.$selectName.'">';
				echo '<option value="">&mdash; select option &mdash;</option>';
				foreach($productResults as $productResult):
					echo '<option value="'.$productResult -> id.'">'.$productResult -> name.'</option>';
				endforeach;
			echo '</select>';
			$costVal = 1;
		endif;
		echo '<input type="hidden" name="'.$hiddenName.'" id="'.$hiddenName.'" value="'.$costVal.'"/>';
	echo '</td>';
endif;
