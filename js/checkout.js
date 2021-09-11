/**
 * Checkout page protection by Groups for MemberMouse
 * - If member is attempting to join a cancelled group - shows error when value is in query string
 */
jQuery(function($) {
  const urlParams = new URLSearchParams(window.location.search);
  if (!urlParams.has('groups-error')) {
    return;
  }

  Swal.fire({
    'text': 'The Group you are attempting to join is no longer active. By signing up, you will not be added to a group.'
  });
});
