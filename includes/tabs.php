<?php
	if(isset($_GET["type"]) && !empty($_GET["type"]) && ($_GET["type"] == "manage")):
		$manageClass = "selected";
		$configClass = "";
		$importClass = ""; // NEW
	// NEW block - Begin
	elseif(isset($_GET["type"]) && !empty($_GET["type"]) && ($_GET["type"] == "import")):
		$manageClass = "";
		$configClass = "";
		$importClass = "selected";	
	// NEW block - End
	else:
		$manageClass = '';
		$configClass = "selected";
		$importClass = ""; // NEW
	endif;
?>	
<ul>
	<li class="<?php echo $configClass;?>" id="group-config">
		<a href="admin.php?page=membermousegroupaddon" title="Define Group Types">Define Group Types</a>
	</li>
	<li id="group-manage" class="<?php echo $manageClass;?>">
		<a href="admin.php?page=membermousegroupaddon&type=manage" title="Manage Groups">Manage Groups</a>
	</li>
	<!-- NEW: Add tab to import members -->
	<li id="group-manage" class="<?php echo $importClass;?>">
		<a href="admin.php?page=membermousegroupaddon&type=import" title="Import Members">Import Members</a>
	</li>
	<li id="group-help" class="mm-group-help">
		<a href="javascript:MGROUP.showHelpWindow();">Need help?</a>
	</li>
</ul>