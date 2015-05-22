<?php
global $wpdb;
if(!isset($wpdb)):
	require_once('../../../../wp-config.php');
    require_once('../../../../wp-includes/wp-db.php');
endif;

include_once("../../membermouse/includes/mm-constants.php");
include_once("../../membermouse/includes/init.php");

if(count($_POST) > 0):
	foreach($_POST as $key => $value):
		$$key = $value;
	endforeach;
	$templateSql	= "SELECT group_template_id,group_name FROM ".$wpdb -> prefix."group_sets WHERE id = '".$group_id."' AND group_leader = '".$member_id."'";
	$templateResult	= $wpdb -> get_row($templateSql);
	$template_id	= $templateResult -> group_template_id;
	$groupName		= $templateResult -> group_name;
	$itemSql		= "SELECT member_memlevel,group_member_cost FROM ".$wpdb -> prefix."group_items WHERE id = '".$template_id."'";
	$itemResult		= $wpdb -> get_row($itemSql);
	
	if(!empty($itemResult -> group_member_cost)):
		$itemCost		= $itemResult -> group_member_cost;
		$purchaseUrl 	= MM_CorePageEngine::getCheckoutPageStaticLink($itemCost);
	else:
		$itemCost		= $itemResult -> member_memlevel;
		$purchaseUrl 	= MM_CorePageEngine::getCheckoutPageStaticLink($itemCost, true);
	endif;	
	$custom_field	= get_option("mm_custom_field_group_id");	
	$purchaseUrl   .= '&cf_'.$custom_field.'=g'.$group_id;
	if(!empty($groupName)):
		$name = $groupName;
	else:
		$name = "Group";
	endif;	
?>
	<div id="group_popup_container">
		<h2>
			<span class="group_title">Signup Link</span>
			<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
		</h2>
		<div id="group_popup_main">
			<p>Use the link below to allow customers to join this group:</p>
			<input type="text" onclick="jQuery('#mm-static-link').focus(); jQuery('#mm-static-link').select();" style="width:440px; font-family:courier; font-size:11px;" value="<?php echo $purchaseUrl;?>" readonly="" id="mm-static-link">
		</div>
	</div>	
<?php	
endif;