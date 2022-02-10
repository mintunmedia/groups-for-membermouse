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
    add_action('wp_ajax_groups_add_member', array($this, 'ajax_add_member_to_group'));
    add_action('wp_ajax_groups_delete_member', array($this, 'ajax_delete_member_from_group'));
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
  public function generate_group_leader_dashboard($atts) {
    global $wpdb, $current_user;

    $controls = array(
      'signup-link' => 'show',
      'add-member' => 'show',
      'action-column' => 'show',
    );

    $atts = shortcode_atts($controls, $atts, 'MM_Group_Leader_Dashboard');

    $signup_link_content = '<button class="btn primary-btn" title="Signup Link" id="signup-link">Signup Link</button>';
    $add_member_content = '<button class="btn primary-btn" title="Add a Member" id="add-member">Add Member</button>';
    $action_header_content = '<th>Actions</th>';

    $signup_link_control = $atts['signup-link'];
    $add_member_control = $atts['add-member'];
    $action_control = $atts['action-column'];

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

    wp_enqueue_style('groups-leader-dashboard');
    wp_enqueue_script('groups-leader-dashboard');
    wp_enqueue_script('sweetalert');

    ob_start();

    /**
     * Get Group ID from DB
     */
    $sql = "SELECT id, group_name, group_size FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $current_user->ID . "'";
    $result = $wpdb->get_row($sql);
    $gid = $result->id;
    $group_name = $result->group_name;
    $group_size = $result->group_size;

    $totalSql  = "SELECT COUNT(id) AS total FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $gid . "' AND member_status = 1";
    $totalRes  = $wpdb->get_row($totalSql);
    $member_count    = $totalRes->total;

    $gMemSql    = "SELECT * FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $gid . "' ORDER BY member_status DESC, createdDate DESC";
    $gMemResults  = $wpdb->get_results($gMemSql); ?>

    <h2><em><?php echo $group_name; ?></em> Management Dashboard</h2>

    <div class="groups-button-container">
      <button class="btn primary-btn" title="Edit Group Name" id="edit-group-name">Edit Group Name</button>
      <?php if ($signup_link_control != 'hide') {
        echo $signup_link_content;
      }
      if ($add_member_control != 'hide') {
        echo $add_member_content;
      } ?>
    </div>

    <div class="member-count">
      <p>Members: <?= $member_count ?>/<?= $group_size ?></p>
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
            <?php if ($action_control != 'hide') {
              echo $action_header_content;
            } ?>
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
              <?php if ($action_control != 'hide') { ?>
                <td>
                  <?php
                  if ($has_subscriptions) {
                    // Member has active subscriptions. Show error
                    echo $cancellationHtml;
                    echo MM_Utils::getDeleteIcon("This member has an active paid membership which must be canceled before they can be removed from the group. Please contact support.", 'margin-left:5px;', '', true);
                  } else if ($statusId === 1) {
                    $deleteActionUrl = 'href="#" class="delete-member" data-member-id="' .  $gMemRes->member_id . '" data-name="' . $firstName . ' ' . $lastName . '"';
                    echo MM_Utils::getDeleteIcon("Remove the member from this group", 'margin-left:5px;', $deleteActionUrl);
                  }
                  ?>
                </td>
              <?php } ?>
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

    if ($result) {
      return $result->group_name;
    } else {
      return false;
    }
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
    global $wpdb;

    $sql = "UPDATE {$wpdb->prefix}group_sets SET group_name = '{$group_name}', modifiedDate = now() WHERE id = '{$group_id}'";
    $query = $wpdb->query($sql);
  }

  /**
   * AJAX - Add member to group
   *
   * @return json
   */
  public function ajax_add_member_to_group() {
    if (!wp_verify_nonce($_POST['nonce'], 'groupDashboardNonce')) {
      wp_send_json_error('Nonce Mismatch. Please try again.');
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
      wp_send_json_error('No User ID found.');
    }

    $member_data = array(
      'first_name' => $_POST['firstName'],
      'last_name' => $_POST['lastName'],
      'email' => $_POST['email'],
      'password' => $_POST['password']
    );

    $group = (new MemberMouseGroupAddon())->get_group_from_leader_id($user_id);
    if (!$group) {
      wp_send_json_error("No Group associated with this user");
    }

    $group_id = $group->id;

    // Update group Name
    $add_member = $this->maybe_add_member_to_group($group_id, $member_data);

    if ($add_member['success']) {
      wp_send_json_success($add_member['message']);
    } else {
      wp_send_json_error($add_member['message']);
    }
  }

  /**
   * Add Member to Group
   *
   * @param int $group_id
   * @param array $member_data. Must include first_name, last_name, email, password
   *
   * @return array success (bool), message (string)
   */
  public function maybe_add_member_to_group($group_id, $member_data) {
    $mmGroups = new MemberMouseGroupAddon();
    $group_max_size = $this->get_group_max_size($group_id);
    $group_current_size = $this->get_group_current_size($group_id);

    // Group Size Check
    if ($group_max_size < $group_current_size + 1) {
      return array(
        'success' => false,
        'message' => 'Your group has reached capacity. Member not added.'
      );
    }

    $email = $member_data['email'];

    // Does user exist. Create if not
    $user = MM_User::findByEmail($email);
    $error = false;
    $active_free_member = false;

    if (!$user->isValid()) {
      // Not valid, create
      $group = $mmGroups->get_group_from_group_id($group_id);
      $group_template_id = $group->group_template_id;
      $group_template = $mmGroups->get_group_template_by_id($group_template_id);
      $group_member_membership_level_id = $group_template->member_memlevel;

      $user = new MM_User();
      $user->setStatus(MM_Status::$ACTIVE);
      $user->setEmail($email);
      $user->setFirstName($member_data['first_name']);
      $user->setLastName($member_data['last_name']);
      $user->setPassword($member_data['password']);
      $user->setMembershipId($group_member_membership_level_id);
      $user->setNotes('User created by Group Leader');
      $commit = $user->commitData();

      if ($commit->type === 'error') {
        return array(
          'success' => false,
          'message' => $commit->message
        );
      }

      // Get ID
      $wp_user = get_user_by('email', $email);
      $member_id = $wp_user->id;
    } else {
      // User Exists. Check if they are admin, in a group already, or paid member

      $wp_user = get_user_by('email', $email);
      $member_id = $wp_user->id;

      if ($user->isAdmin()) {
        // Admin User
        $error = true;
        $error_msg = "Can't add $email. This user is associated with an admin account";
      } else if ($user->isActive()) {
        // Active User
        if ($user->hasActiveSubscriptions()) {
          // User has Active Subscriptions
          $error = true;
          $error_msg = "Can't add $email. This user is already a paid active member. In order to add them, have them cancel their paid account first.";
        } elseif ($this->isGroupLeader($member_id)) {
          // User is a group leader. hasActiveSubscriptions will likely catch them before this. But just in case :)
          $error = true;
          $error_msg = "Can't add $email. This user is already a Group Leader in another group. In order to add them, have them cancel their paid account first.";
        } elseif ($this->isInGroup($member_id)) {
          // User is in a group already
          $error = true;
          $error_msg = "Can't add $email. This user is already in a Group. In order to add them, have them removed from their current group first.";
        }
      }

      // By now, they are likely a free subscriber that isn't in a group
      $active_free_member = true;
    }

    // Exit if Validation hit errors
    if ($error) {
      return array(
        'success' => false,
        'message' => $error_msg
      );
    }

    // Add to group
    $add_to_group = $this->add_member_to_group($member_id, $group_id);

    // Update Custom Field
    $custom_field  = get_option("mm_custom_field_group_id");
    $user->setCustomData($custom_field, "g$group_id");

    if ($add_to_group) {
      if ($active_free_member) {
        $success_msg = "You have successfully added $email to your group! Their account already existed, so we didn't change their password or any of their data.";
      } else {
        $success_msg = "You have successfully added $email to your group!";
      }
      return array(
        'success' => true,
        'message' => $success_msg
      );
    } else {
      return array(
        'success' => false,
        'message' => 'Member Not Added. Error with SQL.'
      );
    }
  }

  /**
   * Add Member to Group
   * @param int $member_id  - ID of member to add to Group
   * @param int $group_id   - ID of Group to add member to
   * @return bool
   */
  public function add_member_to_group($member_id, $group_id) {
    if (!$member_id || !$group_id) {
      return false;
    }
    global $wpdb;
    $add_to_group  = "INSERT INTO " . $wpdb->prefix . "group_sets_members (id,group_id,member_id,createdDate,modifiedDate)VALUES('','" . $group_id . "','" . $member_id . "',now(),now())";
    $add_to_group_query  = $wpdb->query($add_to_group);

    if ($add_to_group_query) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Get Group Max Allowed Size
   */
  public function get_group_max_size($group_id) {
    global $wpdb;
    $group_sql = "SELECT * FROM " . $wpdb->prefix . "group_sets WHERE id = '" . $group_id . "'";
    $group     = $wpdb->get_row($group_sql);
    return $group->group_size;
  }

  /**
   * Get Current Group Size
   */
  public function get_group_current_size($group_id) {
    global $wpdb;
    $group_sql = "SELECT member_id FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $group_id . "' AND member_status = 1";
    $members = $wpdb->get_results($group_sql);
    return $wpdb->num_rows;
  }

  /**
   * Checks if User ID is a group leader
   * @return bool
   */
  public function isGroupLeader($user_id) {
    global $wpdb;
    $groupSql      = "SELECT group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $user_id . "'";
    $groupResult  = $wpdb->get_row($groupSql);
    if (count($groupResult) > 0) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Checks if User ID is in a group
   * @return bool
   */
  public function isInGroup($user_id) {
    global $wpdb;
    $checkMemSql = "SELECT gm.group_id,g.group_name FROM " . $wpdb->prefix . "group_sets_members AS gm LEFT JOIN " . $wpdb->prefix . "group_sets AS g ON gm.group_id = g.id WHERE gm.member_id = '" . $user_id . "' AND member_status = 1";
    $checkMemResult  = $wpdb->get_row($checkMemSql);
    if (count($checkMemResult) > 0) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Delete Member from Group and Cancel Membership
   */
  public function ajax_delete_member_from_group() {
    if (!wp_verify_nonce($_POST['nonce'], 'groupDashboardNonce')) {
      wp_send_json_error('Nonce Mismatch. Please try again.');
    }

    $member_id = $_POST['memberId'];
    $delete = $this->delete_member($member_id);

    if ($delete === true) {
      wp_send_json_success("Member Deleted!");
    } else {
      wp_send_json_error($delete);
    }
  }

  /**
   * Delete Member from Group and Cancel Membership
   * - Set group status to deactivated
   * - Cancel Membership and change membership level
   *
   * @param int $user_id
   *
   * @return bool | string
   */
  public function delete_member($user_id) {
    global $wpdb;
    $cf_id = get_option("mm_custom_field_group_id");

    $sql = "UPDATE {$wpdb->prefix}group_sets_members SET member_status = 0, modifiedDate = now() WHERE member_id = '{$user_id}'";
    $query = $wpdb->query($sql);

    // Clear Custom field for Group ID
    $member = new MM_User($user_id);
    $member->setCustomData($cf_id, '');

    // Cancel their subscription status (same as if their access was removed)
    $member->setStatus(MM_Status::$CANCELED);
    $member->commitStatusOnly();

    $new_status = $member->getStatus();

    if ($query && $new_status === MM_Status::$CANCELED) {
      return true;
    } else {
      $return = '';
      if (!$query) {
        $return .= 'Error removing user from Group. ';
      }
      if ($new_status !== MM_Status::$CANCELED) {
        $return .= 'Error cancelling user\'s membership. ';
      }

      $return .= "Please reach out to support. Sorry for the inconvenience.";
      return $return;
    }
  }
} // End Class
