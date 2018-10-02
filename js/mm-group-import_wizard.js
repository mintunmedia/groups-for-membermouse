/*!
 *
 * MemberMouse(TM) (http://www.membermouse.com)
 * (c) MemberMouse, LLC. All rights reserved.
 */
var MM_ImportGroupWizardViewJS = MM_Core.extend({

	validateForm: function()
	{
		var importFileFromComputer = jQuery('#mm-group-import-file-from-computer-radio').is(':checked');

		if(importFileFromComputer)
		{
			jQuery("#mm-group-import-file-from-url").val("");
			jQuery("#mm-group-import-file-source").val("computer");

			var fileFromComputer = jQuery("#fileToUpload").val();
			var length = fileFromComputer.length;

			if(fileFromComputer == undefined || length <= 0)
			{
				alert("Please upload a file before importing members");
				return false;
			}
			if(fileFromComputer.substring(length-3,length) != 'csv')
			{
				alert("Please upload s CSV file like the above template");
				return false;
			}
			else
			{
				jQuery("#mm-group-import-file-from-computer").val(fileFromComputer);
			}
		}
		else
		{
			jQuery("#mm-group-import-file-from-computer").val("");
			jQuery("#mm-group-import-file-source").val("url");

			var fileFromUrl = jQuery("#mm-group-import-file-from-url-source").val();

			if(fileFromUrl == undefined || fileFromUrl == "")
			{
				alert("Please specify an import file URL before importing members");
				return false;
			}
			else
			{
				jQuery("#mm-group-import-file-from-url").val(fileFromUrl);
			}
		}

		var msg = "When you click OK, the import process will start. The amount of time it takes varies ";
		msg += "\nbased on the number of members being imported.\n\nPlease be patient and let it run to completion.\n\n";
		msg += "Do you want to continue and import these members as '" + jQuery('#mm-membership-selector :selected').text() + "' members?";

		return confirm(msg);
	}
});

var mgjs = new MM_ImportGroupWizardViewJS("MM_ImportGroupWizardView", "Import Wizard");
