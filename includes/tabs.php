<?php
	if(isset($_GET["type"]) && !empty($_GET["type"]) && ($_GET["type"] == "manage")):
		$manageClass = "selected";
		$configClass = "";
	else:
		$manageClass = '';
		$configClass = "selected";
	endif;
?>	
<ul>
	<li class="<?php echo $configClass;?>" id="group-config">
		<a href="admin.php?page=membermousegroupaddon" title="Define Group Types">Define Group Types</a>
	</li>
	<li id="group-manage" class="<?php echo $manageClass;?>">
		<a href="admin.php?page=membermousegroupaddon&type=manage" title="Manage Groups">Manage Groups</a>
	</li>
	<li id="group-help" class="mm-group-help">
		<a href="javascript:MGROUP.showHelpWindow();">Need help?</a>
	</li>
</ul>