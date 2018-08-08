# Groups for MemberMouse

## Introduction

The MemberMouse Group Addon plugin extends MemberMouse with the ability to have group memberships. Each group can have an administrator who can add/remove group members.

## Installation

1. Copy the `MemberMouseGroupAddon/` directory to your `wp-content/plugins/` folder.
2. Go to the Plugins page in your WordPress Dashboard and click `Activate`.
3. The plugin options screen is located in MemberMouse > Groups.

## Instructions on Usage
1. You'll need to add a special shortcode to your checkout page in between the `[MM_Form type="checkout"]` and `[/MM_Form]` tags. This shortcode may have a different ID than the one displayed here: `[MM_Form_Field type="custom-hidden" id="1"]`
2. You'll want to place a shortcode on the Group Leader's payment confirmation page, this shows them their member signup link (which they'll use to send to the people they want to invite to your membership): `[MM_Group_SignUp_Link]`

## Group Leader Management Page
The URL you'll want to send your Group Leader to manage their Groups (adding and deleting people, etc) is located here:
`https://[domain]/wp-admin/admin.php?page=membermousemanagegroup`

** We understand that running this dashboard in your WP admin area is not ideal. We're working on building a new Group Leader Dashboard that is embedded on a regular WordPress page using a simple shortcode in the very near future (Q4 2018).
