/**
 * Handles JS for Group Leader Dashboard
 */
jQuery(function($) {
  const nonce = groupsDashboard.nonce;
  const ajaxurl = groupsDashboard.ajaxurl;
  const groupName = groupsDashboard.groupName;
  const $editGroupNameTrigger = $('#edit-group-name');
  const $groupSignupLinkTrigger = $('#signup-link');
  const $addMemberTrigger = $('#add-member');
  const $clearSearchTrigger = $("#clear-search");
  let $deleteMemberTrigger = $('.delete-member');

  let $filterHeaderTrigger = $('.filter-header-wrapper');
  let $searchTrigger = $('#members-search');
  let $searchInput = $('#members-search-input');

  $groupSignupLinkTrigger.click(openSignUpLinkPop);
  $editGroupNameTrigger.click(openGroupNamePop);
  $addMemberTrigger.click(openAddMemberPop);
  $deleteMemberTrigger.click(openDeleteMemberPop);
  $filterHeaderTrigger.click(changeMemberFilter);
  $searchTrigger.on('click', searchMembers);
  $clearSearchTrigger.on('click', clearSearch);

  /**
   * Refreshes the page and changes the member filter.
   * When pressing a filter link, the order is as follows:
   *  No filter -> ASC filter -> DESC filter -> Reset to No Filter
   */
  function changeMemberFilter() {
    let selectedFilter = $(this).data('filter');
    let params = (new URL(document.location)).searchParams;
    let newOrder = null;
    let currentFilter = null;

    if (params.has('filter')) {
      let currentFilter = params.get('filter');
      if (currentFilter !== selectedFilter) {
        params.delete('order');
      }
    }

    if (params.has('order')) {
      let order = params.get('order');
      if (order === 'ASC') {
        newOrder = 'DESC';
      }

      if (order === 'DESC') {
        newOrder = null;
      }
    } else {
      newOrder = 'ASC';
    }

    params.set('filter', $(this).data('filter'));

    if (newOrder != null) {
      params.set('order', newOrder);
    } else {
      params.delete('order');
      params.delete('filter');
    }

    location.search = params.toString();
  }

  /**
   * Refreshes the page to run a search query.
   */
  function searchMembers() {
    let searchQuery = $searchInput.val();
    let params = (new URL(document.location)).searchParams;
    params.set('q', searchQuery);
    location.search = params.toString();
  }

  /**
   * Clears the search results.
   */
  function clearSearch() {
    let params = (new URL(document.location)).searchParams;
    if (params.has('q')) {
      params.delete('q');
      location.search = params.toString();
    }
  }


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
      customClass: {
        container: 'groups-pop'
      },
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
  function openGroupNamePop() {
    Swal.fire({
      title: 'Edit Group Name',
      input: 'text',
      inputValue: groupName,
      showConfirmButton: true,
      showCloseButton: true,
      showCancelButton: true,
      confirmButtonText: 'Save',
      showLoaderOnConfirm: true,
      padding: '30px',
      customClass: {
        container: 'groups-pop'
      },
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

  /**
 * Opens Add Member Popup
 */
  function openAddMemberPop() {

    let html = '<p>Enter details about the member and click Add Member below.</p>' +
      '<div class="error"></div>' +
      '<div class="form-group"><label for="addMemberFname">First Name</label><input type="text" id="addMemberFname" /></div>' +
      '<div class="form-group"><label for="addMemberLname">Last Name</label><input type="text" id="addMemberLname" /></div>' +
      '<div class="form-group"><label for="addMemberEmail">Email</label><input type="email" id="addMemberEmail" /></div>' +
      '<div class="form-group"><label for="addMemberPassword">Password</label><input type="password" id="addMemberPassword" /></div>';

    Swal.fire({
      title: 'Add a Member to your Group',
      html: html,
      showConfirmButton: true,
      confirmButtonText: 'Add Member',
      showCloseButton: true,
      showLoaderOnConfirm: true,
      showCancelButton: true,
      customClass: {
        container: 'groups-pop'
      },
      didOpen: () => {
        $('#addMemberFname').focus();
      },
      preConfirm: () => {
        // Validate that fields are filled in. undefined passes through value, false prevents popup.
        return new Promise(resolve => {

          $('.swal2-container .error').hide();

          let firstName = $('#addMemberFname');
          let lastName = $('#addMemberLname');
          let email = $('#addMemberEmail');
          let password = $('#addMemberPassword');

          if (firstName.val() === '' || lastName.val() === '' || email.val() === '' || password.val() === '') {
            // invalid
            $('.swal2-container .error').text('All fields are required.').show();
            resolve(false);
          } else {
            // Add member via Ajax

            const data = {
              action: 'groups_add_member',
              nonce: nonce,
              firstName: firstName.val(),
              lastName: lastName.val(),
              email: email.val(),
              password: password.val()
            };

            $.ajax({
              method: 'POST',
              url: ajaxurl,
              data: data
            })
              .done(function(response) {
                if (response.success === true) {
                  // successful response
                  Swal.fire({
                    title: 'Add a Member to your Group',
                    text: response.data,
                    showConfirmButton: true,
                    showCloseButton: true,
                    showCancelButton: false,
                    confirmButtonText: 'Refresh Page',
                    customClass: {
                      container: 'groups-pop'
                    },
                  }).then(() => {
                    // Refresh Page.
                    location = location;
                  });
                } else {
                  // unsuccessful response
                  Swal.fire({
                    title: 'Add a Member to your Group',
                    text: response.data,
                    showConfirmButton: false,
                    showCloseButton: true,
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    customClass: {
                      container: 'groups-pop'
                    },
                  });
                }
              });
            resolve();
          }
        })
      },
    });
  }

  /**
   * Delete Member Pop
   */
  function openDeleteMemberPop(e) {
    e.preventDefault();

    const memberId = $(this).data('member-id');
    const memberName = $(this).data('name');
    const modalTitle = 'Delete Member';

    console.log("Member ID: " + memberId);

    let text = 'Are you sure you want to delete ' + memberName + ' from your group?';

    Swal.fire({
      title: modalTitle,
      text: text,
      showConfirmButton: true,
      confirmButtonText: 'Delete ' + memberName,
      showCloseButton: true,
      showLoaderOnConfirm: true,
      showCancelButton: true,
      customClass: {
        container: 'groups-pop'
      },
      preConfirm: () => {
        // Validate that fields are filled in. undefined passes through value, false prevents popup.
        return new Promise(resolve => {
          const data = {
            action: 'groups_delete_member',
            nonce: nonce,
            memberId: memberId
          };

          $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: data
          })
            .done(function(response) {
              if (response.success === true) {
                // successful response
                Swal.fire({
                  title: modalTitle,
                  text: memberName + ' has been deleted from your group!',
                  showConfirmButton: true,
                  showCloseButton: true,
                  showCancelButton: false,
                  confirmButtonText: 'Refresh Page',
                  customClass: {
                    container: 'groups-pop'
                  },
                }).then(() => {
                  // Refresh Page.
                  location = location;
                });
              } else {
                // unsuccessful response
                Swal.fire({
                  title: modalTitle,
                  text: response.data,
                  showConfirmButton: false,
                  showCloseButton: true,
                  showCancelButton: true,
                  cancelButtonText: 'Close',
                  customClass: {
                    container: 'groups-pop'
                  },
                });
              }
            });
          resolve();
        });
      }
    });
  }
});
