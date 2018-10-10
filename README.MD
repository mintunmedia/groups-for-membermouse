# Groups for MemberMouse

## Introduction

*Groups for MemberMouse* is one of the most sought after add-ons for MemberMouse, allowing you to offer memberships to businesses, schools, sports teams, and other users who need the ability to give access to your membership site to their team members. 

Groups allows you to extend MemberMouse's premium Member Management tool with the ability to sell "seats of membership" to your members. This gives your members the capability to add their own users to their own account. The beauty is that you can increase your profit by allowing your members to purchase membership with any varying amount of seats:

3 seats: $100/mo
5 seats: $120/mo
10 seats: $200/mo

This is the perfect solution for many different business models wanting to offer seats of membership:

- B2B
- Coaches
- Churches
- Sales Teams
- and many more!

Groups for MemberMouse REQUIRES MemberMouse in order to run.

**Some features include**

- Sell seats of membership to your products and memberships.
- Offers support for the same subscriptions/pricing plans that MemberMouse does.
- Integrates directly with MemberMouse
- Dedicated Leader Management Screen - Leaders are able to manage their groups easily from a dedicated mangement screen.
- Easy to Manage for Admins - Easily create or remove groups, add any kind of payment plan to your group products, and add and remove members from specific groups.

**Upcoming Changes**

- Improved leader management screen that takes the dashboard out of /wp-admin/
- Add group import feature

**Thanks to the Github Community**
You may recognize this plugin from Github. We thank the community for getting the plugin to where it was at over the past few years. Since it became stagnant, we decided to take it over and implement many security features and do things "the WordPress way". 

**Shortcodes and URLs**
You'll need to add a special shortcode to your checkout page in between the `[MM_Form type="checkout"]` and `[/MM_Form]` tags. This shortcode may have a different ID than the one displayed here: `[MM_Form_Field type="custom-hidden" id="1"]`

You'll want to place a shortcode on the Group Leader's payment confirmation page, this shows them their member signup link (which they'll use to send to the people they want to invite to your membership): `[MM_Group_SignUp_Link]`

Important: You can add the `[MM_Group_SignUp_Link]` shortcode anywhere. The Payment Confirmation page may not be the most ideal place to put this shortcode.

__Group Leader Management Page__
The URL you'll want to send your Group Leader to manage their Groups (adding and deleting people, etc) is located here:
`https://[domain]/wp-admin/admin.php?page=membermousemanagegroup`

*We understand that running this dashboard in your WP admin area is not ideal. We're working on building a new Group Leader Dashboard that is embedded on a regular WordPress page using a simple shortcode in the very near future (Q4 2018).*

## Installation - WordPress

1. Find the plugin in WordPress: https://wordpress.org/plugins/groups-for-membermouse/
2. Install this marvelous plugin
3. Activate it
4. Navigate to MemberMouse > Groups to set it up!
5. Be sure to add the shortcodes necessary to make it work properly (you'll see these show up as alerts in your WP admin area)
6. Start selling group memberships and increase your ROI ten-fold! (hopefully!)

## Installation - Github

1. Copy the `groups-for-membermouse/` directory to your `wp-content/plugins/` folder.
2. Go to the Plugins page in your WordPress Dashboard and click `Activate`.
3. The plugin options screen is located in MemberMouse > Groups.


## Changelog
1.0.2 Initial release on WordPress Plugin Repository. Fixed a lot of dependency issues within the plugin. Plugin taken over by Mintun Media from MemberMouse and maintaining it for good.

1.0.1 Update to release on Github

1.0.0 Initial Release on Github
