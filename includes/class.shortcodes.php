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
    add_action('wp_enqueue_scripts', array($this, 'enqueues'));

    // Ajax
    add_action('wp_ajax_groups_get_signup_link', array($this, 'ajax_get_signup_link'));
    add_action('wp_ajax_groups_update_group_name', array($this, 'ajax_update_group_name'));
  }

  /**
   * Enqueues for Shortcodes
   *
   * @return void
   */
  public function enqueues() {
    wp_register_style('groups-leader-dashboard', MGROUP_DIR . "/css/groups-leader-dashboard.css", null, filemtime(MGROUP_PATH . "css/groups-leader-dashboard.css"), 'all');
    wp_register_script('groups-leader-dashboard', MGROUP_DIR . "/js/groups-leader-dashboard.js", array('jquery'), filemtime(MGROUP_PATH . "js/groups-leader-dashboard.js"), true);
    wp_localize_script('groups-leader-dashboard', 'groupsDashboard', array(
      'nonce' => wp_create_nonce('groupDashboardNonce'),
      'ajaxurl' => admin_url('admin-ajax.php'),
      'groupName' => $this->get_group_name()
    ));
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

    wp_enqueue_style('groups-leader-dashboard');
    wp_enqueue_script('groups-leader-dashboard');
    wp_enqueue_script('sweetalert');

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

    <div class="groups-button-container">
      <button class="btn primary-btn" title="Edit Group Name" id="edit-group-name">Edit Group Name</button>
      <button class="btn primary-btn" title="Signup Link" id="signup-link">Signup Link</button>
    </div>

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

  /**
   * AJAX - Get Group Sign up Link
   *
   * @return string Sign up Link
   */
  public function ajax_get_signup_link() {
    if (!wp_verify_nonce($_POST['nonce'], 'groupDashboardNonce')) {
      wp_send_json_error('Nonce Mismatch. Please try again.');
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
      wp_send_json_error('No User ID found.');
    }
    $purchase_url = (new MemberMouseGroupAddon())->get_group_signup_link($user_id);
    wp_send_json_success($purchase_url);
  }

  /**
   * AJAX - Update Group Name
   *
   * @return bool
   */
  public function ajax_update_group_name() {
    write_groups_log(__METHOD__);
    if (!wp_verify_nonce($_POST['nonce'], 'groupDashboardNonce')) {
      wp_send_json_error('Nonce Mismatch. Please try again.');
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
      wp_send_json_error('No User ID found.');
    }

    $group_name = $_POST['newGroupName'];
    $group = (new MemberMouseGroupAddon())->get_group_from_leader_id($user_id);
    if (!$group) {
      wp_send_json_error("No Group associated with this user");
    }

    $group_id = $group->id;

    // Update group Name
    $this->update_group_name($group_id, $group_name);

    wp_send_json_success();
  }

  /**
   * Get Group Name
   *
   * @return string
   */
  public function get_group_name() {
    global $wpdb;

    $user_id = get_current_user_id();
    if (!$user_id) {
      return false;
    }

    $sql = "SELECT id, group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $user_id . "'";
    $result = $wpdb->get_row($sql);
    return $result->group_name;
  }

  /**
   * Update group Name
   *
   * @param int    $group_id
   * @param string $group_name
   *
   * @return void
   */
  public function update_group_name($group_id, $group_name) {
    write_groups_log(__METHOD__);
    global $wpdb;

    $sql = "UPDATE {$wpdb->prefix}group_sets SET group_name = '{$group_name}', modifiedDate = now() WHERE id = '{$group_id}'";
    $query = $wpdb->query($sql);
    write_groups_log($query, "Query");
  }
} // End Class
