<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;


$sql = "SELECT * FROM ".$wpdb -> prefix."group_sets AS gs, ".$wpdb -> prefix."users AS wu WHERE wu.id = gs.group_leader ORDER BY gs.createdDate DESC";
$results	= $wpdb -> get_results($sql);
?>
<?php if(count($results) == 0) { ?>
<p><em>No groups created yet.</em></p>
<?php } else { 

	$select_list = "";
	
	foreach($results as $res){
		$select_list .= "<option value=".$res->group_leader.">".$res->user_email."</option>";
	}
}
?>

	<style>
	.mm-import-wizard-step {
		font: 21px/1.3 'PT Sans','Myriad Pro',Myriad,Arial,Helvetica,sans-serif;
		margin-bottom: 20px;
		color: #004D66;
		margin-top: 20px;
	}
	.mm-import-wizard-notice {
		color: #F90;
		font: 16px/0.4em 'PT Sans','Myriad Pro',Myriad,Arial,Helvetica,sans-serif;
		margin-bottom: 20px;
		margin-top: 20px;
	}
	#mm-form-container td {
		font-size: 14px;
		vertical-align: middle;
	}
	.ui-progressbar-value { 
		background-image: url('<?php echo MM_IMAGES_URL."pbar-animated.gif" ?>'); 
	}
	</style>

	<div class="mm-wrap" style="font-size:14px;">
	<?php
	if(isset($_POST["mm-membership-selector"]))
	{	
		//get the leader id       
		$leader_id = $_POST["mm-membership-selector"];

		//get the group id of the leader user selected
		$sql = "SELECT id FROM ".$wpdb -> prefix."group_sets WHERE group_leader = ".$leader_id;
		$group_id = $wpdb -> get_var($sql);
	?>
	<div id="import-running">
	<p id="mm-import-progress-message" class="mm-import-wizard-notice">IMPORT RUNNING... PLEASE DO NOT REFRESH THIS PAGE</p>

	<div id="mm-results-container"></div>
	<div id="mm-progressbar" style="width:400px; height:22px;"></div>
		<script>
		jQuery(function() {
			jQuery("#mm-progressbar").progressbar({value: 100});
		});
		</script>
	</div>
	</div>
	<?php 
		if ($_FILES) {
			$uploadedfile = $_FILES['fileToUpload'];
		    // Get the type of the uploaded file. This is returned as "type/extension"
		    $uploaded_file_type = $_FILES['fileToUpload']['type'];

			    $upload_overrides = array('test_form' => false);

			    $movefile  = wp_handle_upload($uploadedfile, $upload_overrides);

			    if (!$movefile) {
			        echo "<pre>ERROR, file was NOT uploaded!\n</pre>";
			    } 

			    if (!file_exists($movefile['file']) || !is_readable($movefile['file'])) {
			        return false;
			    }

			    //get the group info
			    $group_leader = $_POST['mm-membership-selector'];

			    //get the number original of seats of the group
			    $sql		= "SELECT group_size FROM ".$wpdb -> prefix."group_sets where group_leader = ".$group_leader;
				$groupt_seats	= intval ($wpdb -> get_var($sql) );

				//get the number of seats already on use
				$sql		= "SELECT count(*) FROM ".$wpdb -> prefix."group_sets_members as m, ".$wpdb -> prefix."group_sets as g  where m.group_id = g.id and g.group_leader = ".$group_leader;
				$seats_used	= intval ($wpdb -> get_var($sql) );

				$seats_available = $groupt_seats - $seats_used;

			    $header = null;
			    $data   = array();
			    # Open the File.
			    ini_set( 'auto_detect_line_endings', TRUE ); // fix for OSX systems not set to insert Unix \n
			    if (($handle = fopen($movefile['file'], "r")) !== false) {
			    	 $fp = file_get_contents( $movefile['file'] );
			    	 $rows = explode("\n", $fp);
   					 $seats_required = count($rows) - 1; // -1 to not include the header of the file

   					 if($seats_required <= $seats_available){
	   					 	while (($row = fgetcsv($handle, 1000, ",")) !== false) {
				            if (!$header) {
				                $header = $row;
				            } else {
				                $username 	= $row[0];
				                $email 		= $row[1];
				                $pass 		= substr( "abcdefghijklmnopqrstuvwxyz" ,mt_rand( 0 ,25 ) ,1 ) .substr( md5( time( ) ) ,1 ) ;
				                // $phone 		= $row[2];
				                $first_name = $row[2];
				                $last_name 	= $row[3];
												if(!email_exists( $email )){// the user is already registered
				                	$userdata = array('user_login' => $username , 'user_email' => $email  , 'user_pass' => $pass, 'first_name' => $first_name  , 'last_name' => $last_name);
													$userId = wp_insert_user( $userdata ) ;
				                	if( !is_wp_error( $userId ) ){
				                		// wp_new_user_notification( $userId );
				                	}
				                }
				                else{
				                	$user = get_user_by( 'email', $email );
				                	$userId = $user->ID;
				                }

				                if( !is_wp_error( $userId ) ){	
					                //verify if the user is already registered in the group
					                $sql = "SELECT id FROM ".$wpdb -> prefix."group_sets_members WHERE group_id=".$group_id." AND member_id=".$userId;
													$registered = $wpdb -> get_var($sql);
													
					                if(is_null($registered)){
					                	//register user in the group
						                $sql	= "INSERT INTO ".$wpdb -> prefix."group_sets_members (id,group_id,member_id,createdDate,modifiedDate)VALUES('','".$group_id."','".$userId."',now(),now())";
										$query	= $wpdb -> query($sql);
					                }
					            }
				            }
			        	}

			        	//redirect to the group management page
			        	echo '<script> window.location.replace("/wp-admin/admin.php?page=groupsformm&type=manage"); </script>';
   					 }
   					 else{
   					 	echo "<pre>ERROR, the group does not have enough seats!\n</pre>";
   					 }
			        fclose($handle);


			        //redirect to the group management page
			        $url = admin_url('admin.php?page=groupsformm&type=import');
			    }
			    ini_set( 'auto_detect_line_endings', FALSE );
		}
	}
	else 
	{
	?>
		<p class="mm-import-wizard-step">Step 1: Download Import Template</p>

		<p style="margin-left:12px;">
			<a class="mm-ui-button" href="<?php echo plugin_dir_url( __FILE__ )."templates/mm_group_template.csv" ?>" download="mm_group_template.csv"><?php echo MM_Utils::getIcon('download', '', '1.3em', '2px'); ?> Download Import Template</a>
		</p>
		
		<p class="mm-import-wizard-step" style="margin-bottom:10px;">Step 2: Upload Import File</p>

		<div id="mm-upload-import-file-form">

		<form action="" name='file-upload' method="post" enctype="multipart/form-data" onSubmit="return mgjs.validateForm();">

		<table cellspacing="12">
		<tr>
			<td width="120px;">
				<input id="mm-group-import-file-from-computer-radio" type="radio" checked value="computer" name="import-file-location">
				From Computer
			</td>
			<td>
				<div id="mm-uploaded-file-details" style='display:none;'>
					<div id="mm-uploaded-file" style='float:left; font-family:courier;'></div>
					<div id="mm-uploaded-file-hidden" style='display:none;float:left;'></div>
					<a onclick="mgjs.clearUploadedFile()" class="mm-ui-button" style='margin-left: 10px; float:left;'>Clear</a>
					<div style='clear:both;'></div>
				</div>
				<div id="mm-file-upload-container">
						<input id="fileToUpload" name="fileToUpload" type="file" size="30"  />
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<input id="mm-group-import-file-from-url-radio" type="radio" value="url" name="import-file-location">
				From URL
			</td>
			<td>
				<span style="font-family: courier; font-size: 12px;">
					<input type="text" id="mm-import-file-from-url-source" style="width:430px;" />
				</span>
			</td>
		</tr>
		</table>
		</div>
		<p class="mm-import-wizard-step" style="margin-bottom:10px;">Step 3: Configure Import Settings</p>
		
		<table cellspacing="12">
			<tr>
				<td width="140">Import members to the group of</td>
				<td>
					<select id="mm-membership-selector" name="mm-membership-selector">
						<?php echo $select_list; ?>
					</select>
				</td>
			</tr>
		</table>
		
		<p class="mm-import-wizard-step" style="margin-top:10px;">Step 4: Import Members</p>
		
		<p style="margin-left:12px;">
			<input type="hidden" id="mm-group-import-file-source" name="mm-import-file-source" />
			<input type="hidden" id="mm-group-import-file-from-computer" name="mm-import-file-from-computer" />
			<input type="hidden" id="mm-group-import-file-from-url" name="mm-import-file-from-url" />
			<input type='submit' class="mm-ui-button green" value='Import Members' />
		</p>
		</form>
	<?php } ?>
</div>