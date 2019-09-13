<?php
/**
 * Contains how to docs to get Started with Groups for MemberMouse
 */
$group_id = get_option("mm_custom_field_group_id");
?>
<h1>Getting Started with Groups</h1>
<p>Welcome to Groups! This plugin allows you to extend your MemberMouse membership site to allow members to purchase Grouped Memberships!</p>

<h2>How to Use</h2>
<ol>
  <li>Add Hidden Field shortcode snippet to your checkout page. Place the following shortcode before the ending [/MM_Form] tag. This field will not appear on your checkout page because it's hidden. Don't worry, it's there and it's doing what it needs to do!<br />
  <input type="text" readonly="readonly" value="[MM_Form_Field type='custom-hidden' id='<?= $group_id; ?>']" style="width:400px; background:#fff;"></li>
  <li>Add Group Signup Link Shortcode to your Confirmation Page and anywhere else you want to share the Group Sign Up link with your Group Leaders<br />
  <input type="text" readonly="readonly" value="[MM_Group_SignUp_Link]" style="width:400px; background:#fff;"></li>
  <li>Create Group Types <a href="admin.php?page=groupsformm" target="_blank">here</a>. Group Types are the different types of Groups you may offer. For Example: Gold Group Membership, Silver Group Membership. Group Types are what Group Leaders will purchase.
    <ul style="list-style-type: circle; margin-left:30px;">
      <li><strong>Name:</strong> Set the name for the Group Type.</li>
      <li><strong>Group Leader Associated Access:</strong> Choose what Membership Level that the Group Leader will have applied to them. Keep in mind that if it's a Paid Membership Level, you'll need to choose the product that they'll purchase to gain access.</li>
      <li><strong>Group Member Associated Access:</strong> Choose what Membership Level the Group's Members will have applied to them. Usually you want this to be a Free Membership level, that way Group Member's don't need to purchase Membership to be a part of the group. However keep in mind, you can also offer Discounted access for Group Member's, if this is what your business calls for.</li>
      <li><strong>Group Size:</strong> Choose how many member's are associated with your group. Group Size <strong>does not</strong> include the Group Leader.</li>
    </ul>
  </li>
  <li>Add or Share your checkout page link for Groups by clicking "Purchase Link" next to your group type in Groups for MemberMouse. <strong>IMPORTANT: </strong>do not use product links in MemberMouse > Product Settings. They will not include the correct parameters to create a Group when purchased.</li>
</ol>