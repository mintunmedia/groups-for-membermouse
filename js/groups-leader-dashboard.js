/**
 * Handles JS for Group Leader Dashboard
 */
jQuery(function($) {
  const nonce = groupsDashboard.nonce;
  const ajaxurl = groupsDashboard.ajaxurl;
  const groupName = groupsDashboard.groupName;
  const $editGroupNameTrigger = $('#edit-group-name');
  const $groupSignupLinkTrigger = $('#signup-link');

  $groupSignupLinkTrigger.click(openSignUpLinkPop);
  $editGroupNameTrigger.click(openSignUpLinkPop);


  /**
   * Opens Group Sign Up Link Popup
   */
  function openSignUpLinkPop() {

    const data = {
      action: 'groups_get_signup_link',
      nonce: nonce
    }

    Swal.fire({
      title: 'Group Signup Link',
      html: '<p>Use the link below to allow customers to join this group:</p><input type="text" id="group-signup-link-field" class="disabled" readonly val="Loading..." />',
      showConfirmButton: false,
      showCloseButton: true,
      didRender: () => {
        $.ajax({
          type: 'post',
          url: ajaxurl,
          data: data,
          success: function(response) {
            if (response.success === true) {
              // successful
              let purchaseUrl = response.data;
              $('#group-signup-link-field').val(purchaseUrl);
            }
          }
        });
      }
    });
  }

  /**
   * Opens Edit Group Name Popup
   * Contains input field that prefills current group name. Save button and cancel button
   */
  function openSignUpLinkPop() {
    Swal.fire({
      title: 'Edit Group Name',
      input: 'text',
      inputValue: groupName,
      showConfirmButton: true,
      showCloseButton: true,
      showCancelButton: true,
      confirmButtonText: 'Save',
      showLoaderOnConfirm: true,
      preConfirm: (newGroupName) => {
        return new Promise(resolve => {
          const data = {
            action: 'groups_update_group_name',
            nonce: nonce,
            newGroupName: newGroupName
          }

          $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,
            success: function(response) {
              if (response.success === true) {
                resolve();
              } else {
                let error = response.data;
                Swal.fire({
                  title: 'Edit Group Name',
                  text: 'There was a problem updating your Group Name. Please close and try again! Error: ' + error
                });
              }
            }
          });
        });
      },
      allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
      if (result.isConfirmed) {
        // Confirmed
        Swal.fire({
          title: 'Edit Group Name',
          text: 'Your Group Name has been updated to ' + result.value + '!',
          willClose: () => {
            location = location;
          }
        });
      }
    });
  }
});
