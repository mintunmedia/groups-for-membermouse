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
	$itemSql		= "SELECT name,leader_memlevel,group_leader_cost FROM ".$wpdb -> prefix."group_items WHERE id = '".$prodId."'";
	$itemResult		= $wpdb -> get_row($itemSql);
	if(!empty($itemResult -> group_leader_cost)):
		$purchaseUrl 	= MM_CorePageEngine::getCheckoutPageStaticLink($itemResult -> group_leader_cost);
	else:
		$purchaseUrl	= MM_CorePageEngine::getCheckoutPageStaticLink($itemResult -> leader_memlevel, true);
	endif;
	$purchaseUrl 	.= '&cf_'.$groupId.'='.$prodId;?>
	<div id="group_popup_container">
		<h2>
			<span class="group_title">Purchase Link</span>
			<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>
		</h2>
		<div id="group_popup_main">
			<p><span class="group_section_header">Purchase link for '<?php echo $itemResult -> name;?>'</span></p>
			<p>Use the link below to allow customers to create a new '<?php echo $itemResult -> name;?>' group:</p>
			<input type="text" onclick="jQuery('#mm-static-link').focus(); jQuery('#mm-static-link').select();" style="width:440px; font-family:courier; font-size:11px;" value="<?php echo $purchaseUrl;?>" readonly="" id="mm-static-link">
		</div>
	</div>	
<?php	
endif;