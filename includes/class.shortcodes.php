<?php

/**
 * Groups Shortcodes
 */
if (!defined('ABSPATH')) {
  exit;
}

class MemberMouseGroup_Shortcodes {

  /**
   * A reference to an instance of this class.
   */
  private static $instance;

  /**
   * Returns an instance of this class.
   */
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new MemberMouseGroup_Shortcodes();
    }
    return self::$instance;
  }

  /**
   * Initializes the plugin by setting filters and administration functions.
   */
  public function __construct() {
    $this->load_actions();
    $this->load_shortcodes();
  }

  /**
   * Load Actions
   *
   * @return void
   */
  public function load_actions() {
  }

  /**
   * Load Shortcodes
   *
   * @return void
   */
  public function load_shortcodes() {
    add_shortcode('MM_Group_Leader_Dashboard', array($this, 'generate_group_leader_dashboard'));
  }

  /**
   * SHORTCODE - Group Leader Dashboard [MM_Group_Leader_Dashboard]
   * Outputs the Group Leader Dashboard on front end
   *
   * @return void
   */
  public function generate_group_leader_dashboard() {
    write_groups_log(__METHOD__);

    global $wpdb, $current_user;

    $groups = new MemberMouseGroupAddon();
    $group = $groups->get_group_from_leader_id($current_user->ID);

    // Check if current user is a group leader
    if (!$group) {
      return 'You must be a group leader to view this.';
    }

    // Check if current group is active
    if ($group && !$groups->is_group_active($group->id)) {
      return 'Your group is no longer active.';
    }

    ob_start();

?>
    <div id="create_group_background" style="display:none;">
      <div id="create_group_loading" style="display:none;"></div>
      <div id="create_group_content" style="display:none;"></div>
    </div>
    <div id="group_popup_msg">
      <?php if (isset($_GET["delete"])) { ?>
        <?php if ($_GET["delete"] == 1) { ?>
          <div class="group_success">The operation completed successfully.</div>
        <?php } elseif ($_GET["delete"] == 0) { ?>
          <div class="group_failure">An error occured. Please try again later.</div>
        <?php } ?>
      <?php } ?>

      <?php if (isset($_GET["ndelete"])) { ?>
        <?php if ($_GET["ndelete"] == 1) { ?>
          <div class="group_success">Successfully deleted the notice.</div>
        <?php } elseif ($_GET["ndelete"] == 0) { ?>
          <div class="group_failure">An error occured. Please try again later.</div>
        <?php } ?>
      <?php } ?>
    </div>
    <?php
    if (isset($_GET['notice']) && !empty($_GET['notice']) && is_numeric($_GET['notice'])) {
      $notice   = $_GET['notice'];
      $delSql    = "DELETE FROM " . $wpdb->prefix . "group_notices WHERE id = '" . $notice . "'";
      $delQuery  = $wpdb->query($delSql);
      if ($delQuery) { ?>
        <script type="text/javascript">
          window.location = 'admin.php?page=membermousemanagegroup&ndelete=1';
        </script>
      <?php } else { ?>
        <script type="text/javascript">
          window.location = 'admin.php?page=membermousemanagegroup&ndelete=0';
        </script>
    <?php
      }
    }

    /**
     * Get Group ID from DB
     */
    $sql  = "SELECT id, group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $current_user->ID . "'";
    $result  = $wpdb->get_row($sql);
    $gid   = $result->id;
    $group_name = $result->group_name;

    $totalSql  = "SELECT COUNT(id) AS total FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $gid . "'";
    $totalRes  = $wpdb->get_row($totalSql);
    $count    = $totalRes->total;

    $gMemSql    = "SELECT * FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $gid . "' ORDER BY member_status DESC, createdDate DESC";
    $gMemResults  = $wpdb->get_results($gMemSql); ?>

    <h2><em><?php echo $group_name; ?></em> Management Dashboard</h2>

    <div class="membermousegroupbuttoncontainer">
      <a class="group-button button-green button-small" title="Edit Group Name" id="edit_group" onclick="javascript:MGROUP.editGroupNameForm('<?php echo $gid; ?>','<?php echo $current_user->ID; ?>');">
        Edit Group Name
      </a>&nbsp;&nbsp;
      <a class="group-button button-green button-small" title="Signup Link" id="purchase_link" onclick="javascript:MGROUP.showMemberPurchaseLink('<?php echo $gid; ?>', '<?php echo $current_user->ID; ?>');">
        Signup Link
      </a>
    </div>
    <div class="clear"></div>
    <?php if (count($gMemResults) == 0) { ?>
      <p><em>No members found.</em></p>
    <?php } else { ?>
      <table class="widefat" id="mm-data-grid" style="width:96%">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gMemResults as $gMemRes) :
            $userSql      = "SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '" . $gMemRes->member_id . "'";
            $userResult    = $wpdb->get_row($userSql);
            $registered    = $userResult->user_registered;
            $memSql        = "SELECT * FROM mm_user_data WHERE wp_user_id = '" . $gMemRes->member_id . "'";
            $memResult    = $wpdb->get_row($memSql);
            $firstName     = $memResult->first_name;
            $lastName     = $memResult->last_name;
            $email         = $userResult->user_email;
            $phone         = empty($memResult->phone) ? "&mdash;" : $memResult->phone;
            $membershipId  = $memResult->membership_level_id;
            $levelSql     = "SELECT name FROM mm_membership_levels WHERE id = '" . $membershipId . "'";
            $levelResult  = $wpdb->get_row($levelSql);
            $redirecturl      = "";
            $crntMemberId     = $gMemRes->member_id;
            $member         = new MM_User($crntMemberId);
            $url           = "javascript:mmjs.changeMembershipStatus('" . $crntMemberId . "', ";
            $url             .= $membershipId . ", " . MM_Status::$CANCELED . ", '" . $redirecturl . "');";
            $cancellationHtml   = "<a title=\"Cancel Member\" style=\"cursor: pointer;display: none;\" onclick=\"" . $url . "\"/>" . MM_Utils::getIcon('stop', 'red', '1.2em', '1px') . "</a>";
            $statusId = (int) $gMemRes->member_status;

            // Get Member's Active Subscriptions - includes overdue subscriptions
            $activeSubscriptions = $member->getActiveMembershipSubscriptions(true);

            if (empty($activeSubscriptions)) {
              // No Subscriptions
              $has_subscriptions = false;
            } else {
              $has_subscriptions = true;
            }

            switch ($statusId) {
              case 1:
                $status = "Active";
                break;
              case 0:
                $status = "Deactivated";
                break;
            }

          ?>
            <tr class="<?= strtolower($status) ?>">
              <td><?php echo $firstName . '&nbsp;' . $lastName; ?></td>
              <td><?php echo $email; ?></td>
              <td><?php echo $phone; ?></td>
              <td><?php echo date('F d, Y h:m a', strtotime($registered)); ?></td>
              <td><?= $status; ?></td>
              <td>
                <?php
                if ($has_subscriptions) {
                  // Member has active subscriptions. Show error
                  echo $cancellationHtml;
                  echo MM_Utils::getDeleteIcon("This member has an active paid membership which must be canceled before they can be removed from the group. Please contact support.", 'margin-left:5px;', '', true);
                } else {
                  $deleteActionUrl = 'onclick="javascript:MGROUP.deleteGroupMember(' . $gMemRes->id . ',' . $gMemRes->member_id . ');"';
                  echo MM_Utils::getDeleteIcon("Remove the member from this group", 'margin-left:5px;', $deleteActionUrl);
                }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php } ?>
    <?php

    $noticeSql    = "SELECT * FROM " . $wpdb->prefix . "group_notices WHERE msg_type = '1' AND leader_id = '" . $current_user->ID . "' ORDER BY createdDate DESC";
    $noticeResults   = $wpdb->get_results($noticeSql);
    $noticeCount  = count($noticeResults);
    if ($noticeCount > 0) : ?>
      <div class="group_notices">
        <h3>Notices</h3>
        <table class="widefat" id="mm-data-grid">
          <thead>
            <tr>
              <th>Name</th>
              <th width="60px">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($noticeResults as $noticeResult) :
              $groupSql    = "SELECT group_name FROM " . $wpdb->prefix . "group_sets WHERE id = '" . $noticeResult->group_id . "'";
              $groupResult  = $wpdb->get_row($groupSql);
              $groupName    = $groupResult->group_name;

              $userSql    = "SELECT user_email FROM " . $wpdb->prefix . "users WHERE ID = '" . $noticeResult->user_id . "'";
              $userResult    = $wpdb->get_row($userSql);
              $userEmail    = $userResult->user_email;

              $leaderSql    = "SELECT user_email FROM " . $wpdb->prefix . "users WHERE ID = '" . $noticeResult->leader_id . "'";
              $leaderResult  = $wpdb->get_row($leaderSql);
              $leaderEmail  = $leaderResult->user_email;
            ?>
              <tr>
                <td>Member <span style="color:#FF0000;"><?php echo $userEmail; ?></span> failed to join <?php echo $groupName; ?> (<?php echo $leaderEmail; ?>) because it was full. Please cancel that member account and inform the group leader.</td>
                <td>
                  <a title="Delete Notice" href="admin.php?page=membermousemanagegroup&notice=<?php echo $noticeResult->id; ?>">
                    Delete Notice
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php
    endif;

    return ob_get_clean();
  }
} // End Class
