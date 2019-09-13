<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( isset( $_GET["type"] ) && !empty($_GET["type"] ) && ( $_GET["type"] == "manage" ) ):
  $manageClass 						= "selected";
  $configClass 						= "";
  $importClass 						= "";
  $manage_specific_group 	= "";
  $docs                   = "";
elseif ( isset( $_GET["type"]) && !empty( $_GET["type"] ) && ( $_GET["type"] == "import" )) :
  $manageClass 						= "";
  $configClass 						= "";
  $importClass 						= "selected";
  $manage_specific_group 	= "";
  $docs                   = "";
elseif ( isset( $_GET["type"]) && !empty( $_GET["type"] ) && ( $_GET["type"] == "manage_group" )) :
  $manageClass            = "";
  $configClass            = "";
  $importClass            = "";
  $manage_specific_group  = "selected";
  $docs                   = "";
elseif ( isset( $_GET["type"]) && !empty( $_GET["type"] ) && ( $_GET["type"] == "docs" )) :
  $manageClass            = "";
  $configClass            = "";
  $importClass            = "";
  $manage_specific_group  = "";
  $docs                   = "selected";
else:
  $manageClass            = '';
  $configClass            = "selected";
  $importClass            = "";
  $manage_specific_group  = "";
  $docs                   = "";
endif;

// Get Group Name from Group Leader ID
if( isset( $_GET["group_leader"] ) && !empty($_GET["group_leader"] ) ) {
  $sql = "SELECT id, group_name FROM ". $wpdb->prefix ."group_sets WHERE group_leader='". $_GET["group_leader"] ."'";
  $result	= $wpdb->get_row($sql);
	$group_name = $result->group_name;
  $specific_group_title = "Manage $group_name";
} else {
  $specific_group_title = "Manage Group";
}
?>
<ul>
  <li id="group-config" class="<?php echo $configClass; ?>">
    <a href="admin.php?page=groupsformm" title="Define Group Types">Define Group Types</a>
  </li>
  <li id="group-manage" class="<?php echo $manageClass; ?>">
    <a href="admin.php?page=groupsformm&type=manage" title="Manage Groups">Manage Groups</a>
  </li>
  <?php if( isset( $_GET["type"]) && !empty( $_GET["type"] ) && ( $_GET["type"] == "manage_group" ) ) : ?>
    <li id="group-manage-specific" class="<?php echo $manage_specific_group; ?>">
      <a href="admin.php?page=groupsformm&type=manage_group&group_leader=<?= $_GET['group_leader']; ?>" title="Manage Groups"><?= $specific_group_title; ?></a>
    </li>
  <?php endif; ?>
  <!-- NEW: Add tab to import members -->
  <!-- <li id="group-manage" class="<?php echo $importClass;?>">
    <a href="admin.php?page=groupsformm&type=import" title="Import Members">Import Members</a>
  </li> -->
  <li id="group-help" class="mm-group-help">
    <a href="javascript:MGROUP.showHelpWindow();">Need help?</a>
  </li>
  <li id="group-docs" class="<?= $docs; ?>">
    <a href="admin.php?page=groupsformm&type=docs" title="Manage Groups">Getting Started</a>
  </li>
</ul>