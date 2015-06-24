/*!
 * 
 * MemberMouse(TM) (http://www.membermouse.com)
 * (c) MemberMouse, LLC. All rights reserved.
 */
var MM_ImportGroupWizardViewJS = MM_Core.extend({
  
	downloadTemplate: function(moduleUrl, filePath)
	{
		document.location.href = moduleUrl + "/export_file.php?file_path=" + filePath + "&file_type=text/csv";
	},
	
	validateForm: function()
	{
		var importFileFromComputer = jQuery('#mm-import-file-from-computer-radio').is(':checked');

		if(importFileFromComputer)
		{
			jQuery("#mm-import-file-from-url").val("");
			jQuery("#mm-import-file-source").val("computer");
			
			var fileFromComputer = jQuery("#mm-uploaded-file-hidden").html();
			
			if(fileFromComputer == undefined || fileFromComputer.length <= 0)
			{
				alert("Please upload a file before importing members");
				return false;
			}
			else
			{
				jQuery("#mm-import-file-from-computer").val(fileFromComputer);
			}
		}
		else
		{
			jQuery("#mm-import-file-from-computer").val("");
			jQuery("#mm-import-file-source").val("url");

			var fileFromUrl = jQuery("#mm-import-file-from-url-source").val();
			
			if(fileFromUrl == undefined || fileFromUrl == "")
			{
				alert("Please specify an import file URL before importing members");
				return false;
			}
			else
			{
				jQuery("#mm-import-file-from-url").val(fileFromUrl);
			}
		}
		
		var msg = "When you click OK, the import process will start. The amount of time it takes varies ";
		msg += "\nbased on the number of members being imported.\n\nPlease be patient and let it run to completion.\n\n";
		msg += "Do you want to continue and import these members as '" + jQuery('#mm-membership-selector :selected').text() + "' members?";
		
		return confirm(msg);
	}
});

var mmjs = new MM_ImportGroupWizardViewJS("MM_ImportGroupWizardView", "Import Wizard");