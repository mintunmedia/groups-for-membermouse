jQuery(document).ready(function(){
	jQuery("a#create_group").click(function(){
		var height = MGROUP.contentheight();
		var width  = MGROUP.contentwidth();
		var top	   = MGROUP.contentLoadingTop();
		var left   = MGROUP.contentLoadingLeft();
		jQuery("#create_group_background").height(height);
		jQuery("#create_group_loading").css({"top":top, "left":left});
		jQuery("#create_group_background").show();
		jQuery("#create_group_loading").show();
		jQuery("#create_group_content").show();
		jQuery.ajax({
			type: 'post',
			url : createGroup.ajaxurl,
			data: '',
			success: function(data){
				jQuery("#create_group_content").html(data);
				var contentLeft = MGROUP.contentDataLeft();
				var contentTop = MGROUP.contentDataTop();
				jQuery("#create_group_content").css({"top": contentTop, "left":contentLeft});
				jQuery("#create_group_loading").hide();
				jQuery("#create_group_content").show();
			}
		});
	});
});

(function($){
	MGROUP = {
		init: function() {
		
		},
		changeAssociatedAccessValue:function(gVal){
			$("#associated_access_value").val(gVal);
			if(gVal == 'none'){
				$("#group_membership_access_container").hide();
				$("#group_bundle_access_container").hide();
			}else if(gVal == 'membership'){
				$("#group_membership_access_container").show();
				$("#group_bundle_access_container").hide();
			}else if(gVal == 'bundle'){
				$("#group_membership_access_container").hide();
				$("#group_bundle_access_container").show();
			}
		},
		closeGroupPopup:function(){
			$("#create_group_loading").hide();
			$("#create_group_content").html('');
			$("#create_group_content").hide();
			$("#create_group_background").hide();
		},
		saveGroupForm:function(groupId){
			$(".group-loading-container").show();
			var name 				= $("#name").val();
			var leader_memlevel		= $("#leader_memlevel").val();
			var member_memlevel		= $("#member_memlevel").val();
			var lCost				= $("#leaderCost").val();
			var leader_cost			= 0;
			if(lCost == 1){	
				leader_cost			= $("#group_leader_cost").val();
			}
			var mCost				= $("#memberCost").val();
			var member_cost			= 0;
			if(mCost == 1){
				member_cost			= $("#group_member_cost").val();
			}
			var group_size			= $("#group_size").val();
			var description			= $("#description").val();
			var retVar				= false;
			
			// Error Handling
			if(name == ''){
				$("#name").css({"border-color":"#FF0000"});
				$("#nameErr").html("Please enter the Name.");
				retVar = true;
			}else{
				$("#name").css({"border-color":"#DFDFDF"});
				$("#nameErr").html('');
				retVar = false;
			}
			
			if(leader_memlevel == ''){
				$("#leader_memlevel").css({"border-color":"#FF0000"});
				$("#leadermemlevelErr").html("Please select the Group Leader Associated Access.");
				retVar = true;
			}else{
				$("#leader_memlevel").css({"border-color":"#DFDFDF"});
				$("#leadermemlevelErr").html('');
				retVar = false;
			}
			
			if(lCost == 1 && leader_cost == ''){
				$("#group_leader_cost").css({"border-color":"#FF0000"});
				$("#groupLeaderCostErr").html("Please select the Group Leader Associated Cost.");
				retVar = true;
			}else{
				$("#group_leader_cost").css({"border-color":"#DFDFDF"});
				$("#groupLeaderCostErr").html('');
				retVar = false;
			}
			
			if(member_memlevel == ''){
				$("#member_memlevel").css({"border-color":"#FF0000"});
				$("#membermemlevelErr").html("Please select the Group Member Associated Access.");
				retVar = true;
			}else{
				$("#member_memlevel").css({"border-color":"#DFDFDF"});
				$("#membermemlevelErr").html('');
				retVar = false;
			}
			
			if(mCost == 1 && member_cost == ''){
				$("#group_member_cost").css({"border-color":"#FF0000"});
				$("#groupMemberCostErr").html("Please select the Group Member Associated Cost.");
				retVar = true;
			}else{
				$("#group_member_cost").css({"border-color":"#DFDFDF"});
				$("#groupMemberCostErr").html('');
				retVar = false;
			}
			
			if(group_size == ''){
				$("#group_size").css({"border-color":"#FF0000"});
				$("#groupSizeErr").html("Please enter the Group Size.");
				retVar = true;
			}else{
				$("#group_size").css({"border-color":"#DFDFDF"});
				$("#groupSizeErr").html('');
				retVar = false;
			}
			
			if(description == ''){
				$("#description").css({"border-color":"#FF0000"});
				$("#descriptionErr").html("Please enter the Description.");
				retVar = true;
			}else{
				$("#description").css({"border-color":"#DFDFDF"});
				$("#descriptionErr").html('');
				retVar = false;
			}
			
			if(retVar == true){
				$(".group-loading-container").hide();
			}else{
				$(".group-loading-container").show();
				$.ajax({
					type: 'post',
					url : addGroup.ajaxurl,
					dataType : 'json',
					data: 'name='+name+'&leader_memlevel='+leader_memlevel+'&lCost='+lCost+'&leader_cost='+leader_cost+'&member_memlevel='+member_memlevel+'&mCost='+mCost+'&member_cost='+member_cost+'&group_size='+group_size+'&description='+description+'&groupId='+groupId,
					success: function(data){
						$.each(data, function(i){
							if(i == "name"){
								$("#name").css({"border-color":"#FF0000"});
								$("#nameErr").html(data[i]);
							}else if(i == "leader_memlevel"){
								$("#leader_memlevel").css({"border-color":"#FF0000"});
								$("#leadermemlevelErr").html(data[i]);
							}else if(i == "leader_cost"){
								$("#group_leader_cost").css({"border-color":"#FF0000"});
								$("#groupLeaderCostErr").html(data[i]);
							}else if(i == "member_memlevel"){
								$("#member_memlevel").css({"border-color":"#FF0000"});
								$("#membermemlevelErr").html(data[i]);
							}else if(i == "member_cost"){
								$("#group_member_cost").css({"border-color":"#FF0000"});
								$("#groupMemberCostErr").html(data[i]);
							}else if(i == "group_size"){
								$("#group_size").css({"border-color":"#FF0000"});
								$("#groupSizeErr").html(data[i]);
							}else if(i == "description"){
								$("#description").css({"border-color":"#FF0000"});
								$("#descriptionErr").html(data[i]);
							}else if(i == "success"){
								if(data[i] == "yes"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_success">Successfully save the Group.</div>');
									$("#group_popup_msg").show();
									window.location = 'admin.php?page=groupsformm';
								}else if(data[i] == "no"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_failure">Some error occure please try again later.</div>');
									$("#group_popup_msg").show();
								}
							}
						});
					}
				});
			}	
		},
		showHelpWindow:function(){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : showHelpWindow.ajaxurl,
				data: '',
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		editGroup:function(groupId){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : createGroup.ajaxurl,
				data: 'groupId='+groupId,
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		deleteGroup:function(groupId){
			var c = confirm("Are you sure you want to delete this group.");
			if(c == true){
				var height = MGROUP.contentheight();
				var width  = MGROUP.contentwidth();
				var top	   = MGROUP.contentLoadingTop();
				var left   = MGROUP.contentLoadingLeft();
				$("#create_group_background").height(height);
				$("#create_group_loading").css({"top":top, "left":left});
				$("#create_group_background").show();
				$("#create_group_loading").show();
				$.ajax({
					type: 'post',
					url : deleteGroup.ajaxurl,
					dataType : 'json',
					data: 'groupId='+groupId,
					success: function(data){
						$.each(data, function(i){
							if(i == "success"){
								if(data[i] == "yes"){
									$("#create_group_loading").hide();
									$("#create_group_background").hide();
									window.location = 'admin.php?page=groupsformm&delete=1';
								}else if(data[i] == "no"){
									$("#create_group_loading").hide();
									$("#create_group_background").hide();
									window.location = 'admin.php?page=groupsformm&delete=0';
								}
							}
						});
						
					}
				});
			}
		},
		
		deleteGroupData: function(id){
			if(confirm("Are you sure you want to delete this Group?")){
				var height = MGROUP.contentheight();
				var width  = MGROUP.contentwidth();
				var top	   = MGROUP.contentLoadingTop();
				var left   = MGROUP.contentLoadingLeft();
				$("#create_group_background").height(height);
				$("#create_group_loading").css({"top":top, "left":left});
				$("#create_group_background").show();
				$("#create_group_loading").show();
				$.ajax({
					type		: 'POST',
					url			: deletegroupData.ajaxurl,
					data		: 'id='+id,
					dataType	: 'json',
					success		: function(data){
						$.each(data, function(i){
							if(data[i] == "yes"){
								$("#create_group_loading").hide();
								$("#create_group_background").hide();
								window.location = 'admin.php?page=groupsformm&type=manage&msg=1';
							}else if(data[i] == "no"){
								$("#create_group_loading").hide();
								$("#create_group_background").hide();
								window.location = 'admin.php?page=groupsformm&type=manage&msg=2';
							}
						});
					}	
				});
			}
		},
		
		cancelGroup: function(id){
			if(confirm('Are you sure you want to cancel this group?')){
				var height = MGROUP.contentheight();
				var width  = MGROUP.contentwidth();
				var top	   = MGROUP.contentLoadingTop();
				var left   = MGROUP.contentLoadingLeft();
				$("#create_group_background").height(height);
				$("#create_group_loading").css({"top":top, "left":left});
				$("#create_group_background").show();
				$("#create_group_loading").show();
				$.ajax({
					type		: 'POST',
					url			: cancelGroup.ajaxurl,
					data		: 'id='+id,
					dataType	: 'json',
					success		: function(data){
						$.each(data, function(i){
							if(data[i] == "yes"){
								$("#create_group_loading").hide();
								$("#create_group_background").hide();
								window.location = 'admin.php?page=groupsformm&type=manage&msg=1';
							}else if(data[i] == "no"){
								$("#create_group_loading").hide();
								$("#create_group_background").hide();
								window.location = 'admin.php?page=groupsformm&type=manage&msg=2';
							}
						});
					}	
				});
			}
		},
		
		activateGroup: function(id){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$.ajax({
				type		: 'POST',
				url			: activateGroup.ajaxurl,
				data		: 'id='+id,
				dataType	: 'json',
				success		: function(data){
					$.each(data, function(i){
						if(data[i] == "yes"){
							$("#create_group_loading").hide();
							$("#create_group_background").hide();
							window.location = 'admin.php?page=groupsformm&type=manage&msg=1';
						}else if(data[i] == "no"){
							$("#create_group_loading").hide();
							$("#create_group_background").hide();
							window.location = 'admin.php?page=groupsformm&type=manage&msg=2';
						}
					});
				}	
			});
		},
		
		showPurchaseLink:function(prodId, groupId){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : purchaseLink.ajaxurl,
				data: 'prodId='+prodId+'&groupId='+groupId,
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		changeRecordVal:function(recordVal,targetPage){
			window.location = targetPage+'&show='+recordVal;
		},
		editGroupForm:function(gId){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : editGroup.ajaxurl,
				data: 'gId='+gId,
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		updateGroup:function(gId){
			$(".group-loading-container").show();
			var group_name	= $("#group_name").val();
			var group_size	= $("#group_size").val();
			var retVar		= false;

			if(group_name == ''){
				$("#group_namee").css({"border-color":"#FF0000"});
				$("#groupNameErr").html("Please enter the Group  Name.");
				retVar = true;
			}else{
				$("#group_name").css({"border-color":"#DFDFDF"});
				$("#groupNaeErr").html('');
				retVar = false;
			}

			if(group_size == ''){
				$("#group_size").css({"border-color":"#FF0000"});
				$("#groupSizeErr").html("Please enter the Group Size.");
				retVar = true;
			}else{
				$("#group_size").css({"border-color":"#DFDFDF"});
				$("#groupSizeErr").html('');
				retVar = false;
			}
			
			if(retVar == true){
				$(".group-loading-container").hide();
			}else{
				$(".group-loading-container").show();
				$.ajax({
					type: 'post',
					url : updateGroup.ajaxurl,
					dataType : 'json',
					data: 'gId='+gId+'&group_size='+group_size+'&group_name='+group_name,
					success: function(data){
						$.each(data, function(i){
							if(i == "group_size"){
								$("#group_size").css({"border-color":"#FF0000"});
								$("#groupSizeErr").html(data[i]);
							}else if(i == "success"){
								if(data[i] == "yes"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_success">Update successful.</div>');
									$("#group_popup_msg").show();
									window.location = 'admin.php?page=groupsformm&type=manage';
								}else if(data[i] == "no"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_failure">An error occured. Please try again later.</div>');
									$("#group_popup_msg").show();
								}
							}
						});
					}
				});
			}
		},
		editGroupNameForm:function(group_id,member_id){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : editGroupName.ajaxurl,
				data: 'group_id='+group_id+'&member_id='+member_id,
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		updateGroupName:function(group_id, member_id){
			$(".group-loading-container").show();
			var name	= $("#name").val();
			var retVar		= false;
			if(name == ''){
				$("#name").css({"border-color":"#FF0000"});
				$("#nameErr").html("Please enter the Name.");
				retVar = true;
			}else{
				$("#name").css({"border-color":"#DFDFDF"});
				$("#nameErr").html('');
				retVar = false;
			}
			
			if(retVar == true){
				$(".group-loading-container").hide();
			}else{
				$(".group-loading-container").show();
				$.ajax({
					type: 'post',
					url : updateGroupName.ajaxurl,
					dataType : 'json',
					data: 'group_id='+group_id+'&name='+name+'&member_id='+member_id,
					success: function(data){
						$.each(data, function(i){
							if(i == "name"){
								$("#name").css({"border-color":"#FF0000"});
								$("#nameErr").html(data[i]);
							}else if(i == "success"){
								if(data[i] == "yes"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_success">Update successful.</div>');
									$("#group_popup_msg").show();
									window.location = 'admin.php?page=membermousemanagegroup';
								}else if(data[i] == "no"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_failure">An error occured. Please try again later.</div>');
									$("#group_popup_msg").show();
								}
							}
						});
					}
				});
			}
		},
		showMemberPurchaseLink:function(group_id, member_id){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : showPurchaseLink.ajaxurl,
				data: 'group_id='+group_id+'&member_id='+member_id,
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		checkUsername:function(group_id){
			var username = $("#username").val();
			$("#add_user_loading").show();
			$.ajax({
				type 		: 'post',
				dataType 	: 'json',
				data		: 'username='+encodeURIComponent(username)+'&group_id='+group_id,
				url			: checkUsername.ajaxurl,
				success		: function(data){
					$("#add_user_loading").hide();
					$.each(data, function(i){
						if(i == "error"){
							$("#add_user_msg").html(data[i]);
							$("#add_user_msg").show();
							$("#add_user_container").html("<a class=\"group-button\" title=\"Check Availability\" onclick=\"javascript:MGROUP.checkUsername('"+group_id+"');\">Check Availability</a>");
							$("#user_id").val(0);
						}else{
							$("#add_user_msg").html(data[i]);
							$("#add_user_msg").show();
							$("#add_user_container").html("<a class=\"group-button button-green\" title=\"Add Member\" id=\"add_user_button\" onclick=\"javascript:MGROUP.addGroupUsers('"+group_id+"','"+i+"');\">Add Member</a>");
							$("#user_id").val(i);
						}
					});
				}
				
			});
		},
		addGroupUsers:function(group_id, member_id){
			$("#add_user_loading").show();
			$.ajax({
				type		: 'POST',
				dataType	: 'json',
				data		: 'group_id='+group_id+'&member_id='+member_id,
				url			: addGroupUser.ajaxurl,
				success		: function(data){
					$("#add_user_loading").hide();
					$.each(data, function(i){
						if(data[i] == "yes"){
							$("#add_user_msg").html('<font class="green-text">Successfully added the member.</font>');
							$("#add_user_msg").show();
							$("#username").val('');
							$("#user_id").val('0');
						}else{
							$("#add_user_msg").html('<font class="red-text">An error occured. Please try again later.</font>');
							$("#add_user_msg").show();
							$("#username").val('');
							$("#user_id").val('0');
						}
					});
				}	
			});
		},
		deleteGroupMember:function(gmId){
			var c = confirm('Are you sure you want to remove this member from the group?');
			if(c == true){
				var height = MGROUP.contentheight();
				var width  = MGROUP.contentwidth();
				var top	   = MGROUP.contentLoadingTop();
				var left   = MGROUP.contentLoadingLeft();
				$("#create_group_background").height(height);
				$("#create_group_loading").css({"top":top, "left":left});
				$("#create_group_background").show();
				$("#create_group_loading").show();
				$.ajax({
					type: 'post',
					url : deleteGroupMember.ajaxurl,
					dataType : 'json',
					data: 'gmId='+gmId,
					success: function(data){
						$.each(data, function(i){
							if(i == "success"){
								if(data[i] == "yes"){
									$("#create_group_loading").hide();
									$("#create_group_background").hide();
									window.location = 'admin.php?page=membermousemanagegroup&delete=1';
								}else if(data[i] == "no"){
									$("#create_group_loading").hide();
									$("#create_group_background").hide();
									window.location = 'admin.php?page=membermousemanagegroup&delete=0';
								}
							}
						});
						
					}
				});
			}
		},
		contentheight:function(){
			var height = $(document).height();
			return height;
		},
		contentwidth:function(){
			var width  = $(window).width();
			return width;
		},
		contentLoadingTop:function(){
			var top = ($(window).height() - 22) / 2;
			return top;
		},
		contentLoadingLeft:function(){
			var left = ($(window).width() - 425) / 2;
			return left;
		},
		contentDataLeft:function(){
			var width		= $("#group_popup_container").width() + 165;
			var contentLeft = ($(window).width() - width) / 2;
			return contentLeft;
		},
		contentDataTop:function(){
			var height = $("#group_popup_container").height();
			var contentTop = ($(window).height() - height) / 2;
			return contentTop;
		},
		GroupLeaderForm:function(){
			var height = MGROUP.contentheight();
			var width  = MGROUP.contentwidth();
			var top	   = MGROUP.contentLoadingTop();
			var left   = MGROUP.contentLoadingLeft();		
			$("#create_group_background").height(height);
			$("#create_group_loading").css({"top":top, "left":left});
			$("#create_group_background").show();
			$("#create_group_loading").show();
			$("#create_group_content").show();
			$.ajax({
				type: 'post',
				url : GroupLeaderForm.ajaxurl,
				data: '',
				success: function(data){
					$("#create_group_content").html(data);
					var contentLeft = MGROUP.contentDataLeft();
					var contentTop = MGROUP.contentDataTop();
					$("#create_group_content").css({"top": contentTop, "left":contentLeft});
					$("#create_group_loading").hide();
					$("#create_group_content").show();
				}
			});
		},
		checkGroupUser:function(user){
			if(user != ""){
				$("#userLoading").show();
				$.ajax({
					type		: 'POST',
					url			: checkGroupUser.ajaxurl,
					dataType	: 'json',
					data		: 'user='+encodeURIComponent(user),
					success		: function(data){
						$("#userLoading").hide();
						$.each(data, function(i){
							if(i == "error"){
								$("#userErr").html(data[i]);
								$("#user").css({"border-color":"#FF0000"});
								$("#user_id").val(0);
							}else{
								$("#user_id").val(data[i]);
								$("#userErr").html('');
								$("#user").css({"border-color":"#DFDFDF"});
							}							
						});
						
					}
				});
			}else{
				$("#user").css({"border-color":"#FF0000"});
				$("#user_id").val(0);
				$("#userErr").html('');
			}			
		},
		createGroupLeader:function(){
			var group_name 	= $("#group_name").val();
			var group 		= $("#group").val();
			var user 		= $("#user").val();
			var user_id 	= $("#user_id").val();
			var retVar		= false;

			if(group_name == ''){
				$("#groupNameErr").html('Please type the Group Name.');
				$("#group_name").css({"border-color":"#FF0000"});
				retVar = true;
			}else{
				$("#groupNameErr").html('');
				$("#group_name").css({"border-color":"#DFDFDF"});
				retVar = false;
			}

			if(group == ''){
				$("#groupErr").html('Please select the Group Type.');
				$("#group").css({"border-color":"#FF0000"});
				retVar = true;
			}else{
				$("#groupErr").html('');
				$("#group").css({"border-color":"#DFDFDF"});
				retVar = false;
			}	
			if(user == ''){
				$("#userErr").html('Please enter the Group Leader.');
				$("#user").css({"border-color":"#FF0000"});
				retVar = true;
			}else{
				$("#userErr").html('');
				$("#user").css({"border-color":"#DFDFDF"});
				retVar = false;
			}	
				
			if(retVar == false){
				$(".group-loading-container").show();
				$("#group").css({"border-color":"#DFDFDF"});
				$("#user").css({"border-color":"#DFDFDF"});
				$("#groupErr").html('');
				$("#userErr").html('');
				$.ajax({
					type: 'post',
					url : createGroupLeader.ajaxurl,
					dataType : 'json',
					data: 'group='+group+'&user='+encodeURIComponent(user)+'&user_id='+user_id+'&group_name='+group_name,
					success: function(data){
						$(".group-loading-container").hide();
						$.each(data, function(i){
							if(i == "group"){
								$("#group").css({"border-color":"#FF0000"});
								$("#groupErr").html(data[i]);
							}else if(i == "user"){
								$("#user").css({"border-color":"#FF0000"});
								$("#userErr").html(data[i]);
							}else if(i == "success"){
								if(data[i] == "yes"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_success">Successfully created the Group Leader.</div>');
									$("#group_popup_msg").show();
									window.location = 'admin.php?page=groupsformm&type=manage';
								}else if(data[i] == "no"){
									$(".group-loading-container").hide();
									$("#group_popup_msg").html('<div class="group_failure">Some error occure please try again later.</div>');
									$("#group_popup_msg").show();
								}
							}
						});
					}
				});
			}else{
				$(".group-loading-container").hide();
			}
		},
		changeGroupMemberCost:function(levelId){
			$("#memberLoading").show();
			$.ajax({
				type: 'post',
				url : changeGroupCost.ajaxurl,
				data: 'levelId='+levelId+'&type=member',
				success: function(data){
					$("#member_associated_cost").html(data);
					$("#memberLoading").hide();
				}
			});
		},
		changeGroupLeaderCost:function(levelId){
			$("#leadermemLoading").show();
			$.ajax({
				type: 'post',
				url : changeGroupCost.ajaxurl,
				data: 'levelId='+levelId+'&type=leader',
				success: function(data){
					$("#leader_associated_cost").html(data);
					$("#leadermemLoading").hide();
				}
			});
		},
	}
	$(document).ready(function(){ 
		MGROUP.init(); 
	});		
})(jQuery);