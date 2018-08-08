<?php 
global $wpdb;
if(!isset($wpdb)):
	require_once('../../../../wp-config.php');
    require_once('../../../../wp-includes/wp-db.php');
endif;
$group_id = get_option("mm_custom_field_group_id");?>
<div id="group_popup_container">
	<h2>
		<span class="group_title">Groups for MemberMouse Help</span>
		<span class="group_close"><a href="javascript:MGROUP.closeGroupPopup();" title="Close">Close</a></span>	
	</h2>
	<div id="group_popup_main">
		<p><a href="https://membermouseplus.com/support" target="_blank" class="mm-ui-button blue">Contact Support</a></p>
		<p><a href="http://support.membermouse.com/knowledgebase/articles/319238-groups-extension-overview-beta" target="_blank" class="mm-ui-button blue">Documentation</a></p>
		<p style="margin-top:25px;">Add the following SmartTag to your checkout page within the <code>[MM_Form type="checkout"]</code> and <code>[/MM_Form]</code> tags: </p>
		<p><input id="mm-group-id-custom-field" type="text" readonly value="<?php echo "[MM_Form_Field type='custom-hidden' id='{$group_id}']"; ?>" style="width:320px; font-family:courier; font-size:11px;" onclick="jQuery('#mm-group-id-custom-field').focus(); jQuery('#mm-group-id-custom-field').select();" /></p>
		<p style="margin-top:25px;">Use the following SmartTag to display the group signup link to Group leaders: </p>
		<p><input id="mm-group-signup-link" type="text" readonly value="[MM_Group_SignUp_Link]" style="width:200px; font-family:courier; font-size:11px;" onclick="jQuery('#mm-group-signup-link').focus(); jQuery('#mm-group-signup-link').select();" /></p>
	</div>
</div>