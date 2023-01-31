<?php

/**
 * Plugin Name: Groups for MemberMouse
 * Description: Adds group support to MemberMouse. You can define different types of groups allowing a single customer to pay for multiple seats and members to join existing groups for free or for a price based on how you configure the group type. <strong>Requires MemberMouse to activate and use.</strong>
 * Version: 2.3.0
 * Author: Mintun Media
 * Plugin URI:  https://www.mintunmedia.com
 * Author URI:  https://www.mintunmedia.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!(DEFINED('MGROUP_DIR'))) DEFINE('MGROUP_DIR', plugins_url('groups-for-membermouse'));
if (!(DEFINED('MGROUP_PATH'))) DEFINE('MGROUP_PATH', plugin_dir_path(__FILE__));
if (!(DEFINED('MGROUP_IMG'))) DEFINE('MGROUP_IMG', plugins_url('images/', __FILE__));

define('MGROUP_TESTING', false);

/**
 * Local Logging for Plugin.
 *
 * @param string|array $data Data sent to logs. Can be string or Array or Object.
 * @param string $pretext Text that can be added ABOVE $data. Useful if $data is an array and you want to include a string above it.
 * @param bool $first_log True if you want to add a PHP_EOL before text is printed to give visual space and adds Date and time
 * @return void
 */
function write_groups_log($data, $pretext = null, $first_log = false) {

	if (!MGROUP_TESTING) {
		return;
	}

	$loc = plugin_dir_path(__FILE__) . 'debug.log';

	// First Log Handler
	if ($first_log) {
		error_log(PHP_EOL . '*** ' . date('m/d/Y H:i:s') . ' ***' . PHP_EOL, 3, $loc);
	}

	// Pretext Handler
	if ($pretext && is_string($pretext)) {
		error_log($pretext . PHP_EOL, 3, $loc);
	}
	if (is_array($data) || is_object($data)) {
		error_log(print_r($data, true) . PHP_EOL, 3, $loc);
	} else {
		error_log($data . PHP_EOL, 3, $loc);
	}
}

if (!class_exists('MemberMouseGroupAddon')) {
	class MemberMouseGroupAddon {

		const ACTIONS = array(
			'create_group',
			'add_group',
			'delete_group',
			'purchase_link',
			'edit_group',
			'update_group',
			'edit_group_name',
			'update_group_name',
			'show_purchase_link',
			'check_username',
			'add_group_user',
			'delete_group_member',
			'group_leader_form',
			'check_user',
			'create_group_leader',
			'change_group_cost',
			'show_help_window',
			'cancel_group',
			'activate_group',
			'delete_group_data'
		);

		const MM_PLUGIN_PATH = 'membermouse/index.php';

		function __construct() {
			$this->plugin_name = basename(dirname(__FILE__)) . '/' . basename(__FILE__);

			if ($this->is_plugin_active(self::MM_PLUGIN_PATH)) {
				register_activation_hook($this->plugin_name, array(&$this, 'MemberMouseGroupAddonActivate'));
				register_deactivation_hook($this->plugin_name, array(&$this, 'MemberMouseGroupAddonDeactivate'));
				add_action('admin_menu', array(&$this, 'MemberMouseGroupAddonAdminMenu'), 11);
				add_action('admin_head', array(&$this, 'MemberMouseGroupAddonAdminResources'));
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
				add_action('admin_init', array($this, 'check_db_version'));
				add_action('mm_member_add', array(&$this, 'MemberMouseGroupMemberAdded'));
				add_action('mm_member_status_change', array(&$this, 'MemberMouseGroupLeaderStatus'));
				add_action('mm_member_membership_change', array($this, 'membership_changed_handler'));
				add_action('admin_head', array(&$this, 'MemberMouseGroupOptionUpdate'));
				add_shortcode('MM_Group_SignUp_Link', array(&$this, 'MemberMouseGroupPurchaseLinkShortcode'));
				add_action('plugins_loaded', array($this, 'plugins_loaded'));
				add_action('template_redirect', array($this, 'checkout_groups_protection'));
				add_action('wp_enqueue_scripts', array($this, 'frontend_enqueues'));

				// Admin Notices
				if (empty(get_option('gfmm_checkoutpage_notice'))) {
					add_action('admin_notices', array($this, 'gfm_adminnotice_checkoutpage'));
				}
				if (empty(get_option('gfmm_confirmationpage_notice'))) {
					add_action('admin_notices', array($this, 'gfm_adminnotice_confirmationpage'));
				}
				add_action('wp_ajax_dismiss_checkoutpage_notice', array($this, 'gfm_dismiss_checkoutpage_notice'));
				add_action('wp_ajax_dismiss_confirmationpage_notice', array($this, 'gfm_dismiss_confirmationpage_notice'));

				$this->load_classes();

				//add_action('admin_notices', array(&$this, 'MemberMouseGroupAdminNotice'));
				//add_action('admin_init', array(&$this, 'MemberMouseGroupAdminNoticeIgnore'));
			} else {

				// Show notice that plugin can't be activated
				add_action('admin_notices', 'groupsformm_notice_mmrequired');
			}
		}

		/**
		 * Check Database Version
		 * - Used to update tables/columns
		 *
		 * @return void
		 */
		public function check_db_version() {
			global $wpdb;

			$plugin_data = get_plugin_data(MGROUP_PATH . 'groups-for-membermouse.php');
			$plugin_version = explode('.', $plugin_data['Version']);
			$plugin_version_major = (int) reset($plugin_version);
			$plugin_version_middle = (int) $plugin_version[1];
			$plugin_version_minor = (int) end($plugin_version);

			/**
			 * 2.0.8 DB Update
			 * - Add member_status to wp_group_sets_members
			 * @date 9.28.2021
			 */
			if ($plugin_version_major <= 2 && $plugin_version_middle === 0 && $plugin_version_minor <= 9) {
				$dbname = $wpdb->dbname;
				$table_name = $wpdb->prefix . "group_sets_members";
				$is_status_col = $wpdb->get_results("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `table_name` = '{$table_name}' AND `TABLE_SCHEMA` = '{$dbname}' AND `COLUMN_NAME` = 'member_status'");
				if (empty($is_status_col)) :
					$add_status_column = "ALTER TABLE `{$table_name}` ADD `member_status` INT(11) DEFAULT 1 AFTER `member_id`; ";
					$wpdb->query($add_status_column);
				endif;
			}
		}
		/**
		 * Load Extra Groups Classes
		 *
		 * @return void
		 */
		public function load_classes() {
			include_once(MGROUP_PATH . 'includes/class.shortcodes.php');

			MemberMouseGroup_Shortcodes::get_instance();
		}

		/**
		 * Enqueue Scripts into Groups for MemberMouse head
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie<roypmckenzie@icloud.com>
		 */
		public function admin_enqueue_scripts($hook_suffix) {
			$pages_to_enqueue_in = array('membermouse_page_membermousemanagegroup');

			if (in_array($hook_suffix, $pages_to_enqueue_in)) {
				wp_enqueue_script('mm-detail-access-rights', plugins_url('/membermouse/resources/js/admin/mm-details_access_rights.js'), array('jquery', 'membermouse-global'));
			}
		}

		/**
		 * Plugins loaded action.
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie<roypmckenzie@icloud.com>
		 */
		public function plugins_loaded() {
			add_action('rest_api_init', array($this, 'rest_api_init'));
		}

		/**
		 * Register REST routes.
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie <roypmckenzie@icloud.com>
		 */
		public function rest_api_init() {
			foreach (self::ACTIONS as $action) {
				register_rest_route('mm-groups/v1/', $action, array(
					'methods' 	=> WP_REST_Server::EDITABLE,
					'callback' 	=> function () use ($action) {
						$this->rest_callback($action);
					},
					'permission_callback' => function () use ($action) {
						return $this->permission_callback($action);
					}
				));
			}
		}

		/**
		 * Permission callback for REST routes
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie<roypmckenzie@icloud.com>
		 */
		public function permission_callback($action) {
			$user = wp_get_current_user();

			$group_leader_actions = array(
				'delete_group_member',
				'show_purchase_link',
				'edit_group_name',
				'update_group_name'
			);

			if (in_array(self::get_group_leader_role(), $user->roles) && in_array($action, $group_leader_actions)) {
				return check_ajax_referer('wp_rest', FALSE, FALSE);
			}

			if (in_array('administrator', $user->roles)) {
				return check_ajax_referer('wp_rest', FALSE, FALSE);
			}

			return FALSE;
		}

		/**
		 * REST callback handler
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie<roypmckenzie@icloud.com>
		 */
		public function rest_callback($action) {
			header('Content-Type: text/html');

			require(plugin_dir_path(__FILE__) . "/includes/$action.php");

			exit();
		}

		/**
		 * Checks if a plugin is loaded.
		 *
		 * @since 1.0.2
		 *
		 * @author Roy McKenzie <roypmckenzie@icloud.com>
		 */
		private function is_plugin_active($plugin) {
			return in_array($plugin, (array) get_option('active_plugins', array()));
		}

		/**
		 * All Original Plugin code before Mintun Media took it over (with modifications)
		 */
		public function groupsformm_notice_mmrequired() {
?>
			<div class="notice error is-dismissible">
				<p>Sorry! MemberMouse is required to activate Groups for MemberMouse.</p>
			</div>
		<?php
		}

		public function MemberMouseGroupAddonActivate() {
			$this->MemberMouseGroupAddGroup();
			$this->MemberMouseGroupAddonCreateTables();
			$this->MemberMouseGroupAddCap();
			$this->MemberMouseGroupAddRoll();
		}

		public function MemberMouseGroupAddonDeactivate() {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");
			global $wpdb, $current_user;
			$user_id = $current_user->ID;
		}

		public function MemberMouseGroupAddonAdminResources() {
			/** Scripts */
			wp_enqueue_script('MemberMouseGroupAddOnAdminJs', plugins_url('js/admin.js', __FILE__), array('jquery', 'membermouse-global'), filemtime(plugin_dir_path(__FILE__) . '/js/admin.js'));
			wp_localize_script('MemberMouseGroupAddOnAdminJs', 'dismiss_notices', array('ajax_url' => admin_url('admin-ajax.php')));

			wp_enqueue_script('import-group', plugins_url('js/mm-group-import_wizard.js', __FILE__), array('jquery', 'MemberMouseGroupAddOnAdminJs'), '1.0.0', true);

			/** Styles */
			wp_enqueue_style('MemberMouseGroupAddOnAdminCss', plugins_url('css/admin.css', __FILE__));

			/** REST Actions */
			foreach (self::ACTIONS as $action) {
				wp_localize_script(
					'MemberMouseGroupAddOnAdminJs',
					$action,
					array('ajax_url' => get_rest_url(NULL, "/mm-groups/v1/$action"))
				);
			}

			/** REST nonce */
			wp_localize_script('MemberMouseGroupAddOnAdminJs', 'rest_nonce', array('_wpnonce' => wp_create_nonce('wp_rest')));
		}

		public function MemberMouseGroupAddonAdminMenu() {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");
			add_submenu_page('mmdashboard', 'MemberMouse Groups', 'Groups', 'manage_options', 'groupsformm', array(&$this, 'MemberMouseGroupAddonAdminManagement'));
			add_submenu_page('mmdashboard', 'Group Management Dashboard', 'Group Management Dashboard', 'Group Leader', 'membermousemanagegroup', array(&$this, "MemberMouseManageGroup"));
		}

		/**
		 * Shortcode output for Purchase Link
		 * [MM_Group_SignUp_Link]
		 */
		public function MemberMouseGroupPurchaseLinkShortcode() {
			global $wpdb, $current_user;

			if (is_user_logged_in() && in_array(self::get_group_leader_role(), $current_user->roles)) {
				$leaderSql = "SELECT id,group_template_id,group_name FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $current_user->ID . "'";
				$leaderResult = $wpdb->get_row($leaderSql);

				if (is_array($leaderResult) || is_object($leaderResult)) {
					$group_id 		= $leaderResult->id;
					$template_id 	= $leaderResult->group_template_id;
					$groupName 		= $leaderResult->group_name;
					$itemSql 			= "SELECT member_memlevel,group_member_cost FROM " . $wpdb->prefix . "group_items WHERE id = '" . $template_id . "'";
					$itemResult 	= $wpdb->get_row($itemSql);

					if (!empty($itemResult->group_member_cost)) {
						$itemCost 		= $itemResult->group_member_cost;
						$purchaseUrl 	= MM_CorePageEngine::getCheckoutPageStaticLink($itemCost);
					} else {
						$itemCost 		= $itemResult->member_memlevel;
						$purchaseUrl 	= MM_CorePageEngine::getCheckoutPageStaticLink($itemCost, true);
					}
					$custom_field = get_option("mm_custom_field_group_id");
					$purchaseUrl .= '&cf_' . $custom_field . '=g' . $group_id;
					return $purchaseUrl;
				}
			}
			return '';
		}

		/**
		 * Checkout Page Add Shortcode Notice
		 */
		public function gfm_adminnotice_checkoutpage() {
			global $current_user;
			$userRole = $current_user->roles;

			if (in_array('administrator', $userRole)) {
				$group_id = get_option("mm_custom_field_group_id");

				echo "<div class='checkoutpage_notice notice updated is-dismissible'><p><strong>Please add the following to your checkout page within the [MM_Form type='checkout'] and [/MM_Form] tags:</strong></p><pre>[MM_Form_Field type='custom-hidden' id='$group_id']</pre></div>";
			}
		}

		/**
		 * Confirmation Page Add Shortcode Notice
		 */
		public function gfm_adminnotice_confirmationpage() {
			global $current_user;
			$userRole = $current_user->roles;

			if (in_array('administrator', $userRole)) {
				echo "<div class='confirmationpage_notice notice updated is-dismissible'><p><strong>Place this shortcode on your Group Leader's confirmation page to show their member signup link.</strong></p><pre>[MM_Group_SignUp_Link]</pre></div>";
			}
		}


		/**
		 * Update plugin options for notice dismissal - Checkout Page
		 */
		public function gfm_dismiss_checkoutpage_notice() {
			update_option('gfmm_checkoutpage_notice', 1);
		}

		/**
		 * Update plugin options for notice dismissal - Confirmation Page
		 */
		public function gfm_dismiss_confirmationpage_notice() {
			update_option('gfmm_confirmationpage_notice', 1);
		}

		public function MemberMouseGroupAdminNoticeIgnore() {
			global $current_user;
			$user_id = $current_user->ID;
			if (isset($_GET['mmgroups-ignore']) && '1' == $_GET['mmgroups-ignore']) :
				add_user_meta($user_id, 'mmgroups-ignore', 'true', true);
			endif;

			if (isset($_GET['mmgroups-sc-ignore']) && '1' == $_GET['mmgroups-sc-ignore']) :
				add_user_meta($user_id, 'mmgroups-sc-ignore', 'true', true);
			endif;
		}

		public function MemberMouseGroupAddCap() {
			$custom_cap = "membermouse_group_capability";
			$grant = true;
			foreach ($GLOBALS['wp_roles']->role_objects as $role => $name) :
				if (!$name->has_cap($custom_cap)) :
					$name->add_cap($custom_cap, $grant);
				endif;
			endforeach;
		}

		public function MemberMouseGroupRemoveCap() {
			$custom_cap = "membermouse_group_capability";
			foreach ($GLOBALS['wp_roles']->role_objects as $role => $name) :
				if (!$name->has_cap($custom_cap)) :
					$name->remove_cap($custom_cap);
				endif;
			endforeach;
		}

		public function MemberMouseGroupAddonCreateTables() {
			global $wpdb;

			$table_name         = $wpdb->prefix . 'group_sets';
			$table_name1        = $wpdb->prefix . 'group_sets_members';
			$table_group_item   = $wpdb->prefix . 'group_items';
			$table_group_notice = $wpdb->prefix . 'group_notices';

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) :
				$sql = "CREATE TABLE IF NOT EXISTS $table_name (
					id INT(11) NOT NULL AUTO_INCREMENT,
					group_template_id INT(11) NOT NULL DEFAULT '0',
					group_name VARCHAR(255) NOT NULL,
					group_size INT(11) NOT NULL DEFAULT '0',
					group_leader INT(11) NOT NULL DEFAULT '0',
					group_status INT(11) NOT NULL DEFAULT '0',
					createdDate DATETIME NOT NULL,
					modifiedDate DATETIME NOT NULL,
					PRIMARY KEY (id)
				);";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

			endif;

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name1'") != $table_name1) :
				$sql = "CREATE TABLE IF NOT EXISTS $table_name1 (
					id INT(11) NOT NULL AUTO_INCREMENT,
					group_id INT(11) NOT NULL DEFAULT '0',
					member_id VARCHAR(255) NOT NULL,
					member_status INT(11) DEFAULT 1,
					createdDate DATETIME NOT NULL,
					modifiedDate DATETIME NOT NULL,
					PRIMARY KEY (id)
				);";
				dbDelta($sql);
			endif;

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_group_item'") != $table_group_item) :
				$sql = "CREATE TABLE IF NOT EXISTS $table_group_item (
					id INT(11) NOT NULL AUTO_INCREMENT,
					name VARCHAR(255) NOT NULL,
					leader_memlevel INT(11) NOT NULL DEFAULT '0',
					member_memlevel INT(11) NOT NULL DEFAULT '0',
					group_leader_cost INT(11) NOT NULL DEFAULT '0',
					group_member_cost INT(11) NOT NULL DEFAULT '0',
					group_size INT(11) NOT NULL DEFAULT '0',
					createdDate DATETIME NOT NULL,
					modifiedDate DATETIME NOT NULL,
					PRIMARY KEY (id)
					);";
				dbDelta($sql);
			endif;

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_group_notice'") != $table_group_notice) :
				$sql = "CREATE TABLE IF NOT EXISTS $table_group_notice(
					id INT(11) NOT NULL AUTO_INCREMENT,
					group_id INT(11) NOT NULL DEFAULT '0',
					user_id INT(11) NOT NULL DEFAULT '0',
					leader_id INT(11) NOT NULL DEFAULT '0',
					msg_type INT(11) NOT NULL DEFAULT '0',
					createdDate DATETIME NOT NULL,
					modifiedDate DATETIME NOT NULL,
					PRIMARY KEY (id)
					);";
				dbDelta($sql);
			endif;
		}

		public function MemberMouseGroupAddGroup() {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");
			$customFieldList = MM_CustomField::getCustomFieldsList();

			if (count($customFieldList) > 0) :
				$customFieldId = 0;
				foreach ($customFieldList as $id => $displayName) :
					if ($displayName == "group_id") :
						$customFieldId = $id;
						break;
					endif;
				endforeach;
				if (empty($customFieldId)) :
					$customField = new MM_CustomField();
					$displayName = "group_id";
					$customField->setDisplayName($displayName);
					$customField->setShowOnMyAccount("0");
					$customField->setHiddenFlag("1");
					$customField->commitData();
				else :
					update_option("mm_custom_field_group_id", $customFieldId);
				endif;
			else :
				$customField = new MM_CustomField();
				$displayName = "group_id";
				$customField->setDisplayName($displayName);
				$customField->setShowOnMyAccount("0");
				$customField->setHiddenFlag("1");
				$customField->commitData();
			endif;
		}

		public function MemberMouseGroupOptionUpdate() {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");
			$customFieldList = MM_CustomField::getCustomFieldsList();
			foreach ($customFieldList as $id => $displayName) :
				if ($displayName == "group_id") :
					update_option("mm_custom_field_group_id", $id);
					break;
				endif;
			endforeach;
		}

		/**
		 * Add MemberMouse Groups Role on Install.
		 *
		 * @return void
		 */
		public function MemberMouseGroupAddRoll() {
			self::get_group_leader_role();
		}

		public function MemberMouseGroupAddonAdminManagement() {
			global $wpdb;
			if (isset($_GET["type"]) && !empty($_GET["type"])) {
				$type = $_GET["type"];
			} else {
				$type = '';
			}
		?>
			<div class="wrap" style="margin-top:20px;">
				<div id="create_group_background" style="display:none;">
					<div id="create_group_loading" style="display:none;"></div>
					<div id="create_group_content" style="display:none;"></div>
				</div>
				<div class="membermousegroupaddon">
					<h2>Groups for MemberMouse</h2>
					<div class="membermousegroups-row">
						<div class="membermousegrouptabs">
							<?php include_once(dirname(__FILE__) . "/includes/tabs.php"); ?>
						</div>
						<div class="membermousegroupcontent">
							<?php if ($type == "manage") : ?>
								<div class="membermousegroupmanage">
									<?php include_once(dirname(__FILE__) . "/includes/manage.php"); ?>
								</div>
							<?php elseif ($type == "import") : ?>
								<div class="membermousegroupmanage">
									<?php include_once(dirname(__FILE__) . "/includes/import.php"); ?>
								</div>
							<?php elseif ($type == "manage_group") : ?>
								<div class="membermousegroupmanage-specific">
									<?php include_once(dirname(__FILE__) . "/includes/manage_groups_admin.php"); ?>
								</div>
							<?php elseif ($type == "docs") : ?>
								<div class="membermousegroupmanage-specific">
									<?php include_once(dirname(__FILE__) . "/includes/docs.php"); ?>
								</div>
							<?php else : ?>
								<div class="membermousegroupconfig">
									<?php include_once(dirname(__FILE__) . "/includes/config.php"); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="membermousegroups-cta">
							<div class="theCta purple">
								<h2>Need MemberMouse or WordPress Development Support?</h2>
								<p>The development team behind the MemberMouse Groups plugin is here to help you get started or take your membership site to the next level! We offer development services and customization services.</p>
								<a href="https://www.mintunmedia.com/" target="_blank">Yes! Help Me!</a>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
		}

		public function MemberMouseManageGroup() {
			include_once(dirname(__FILE__) . "/includes/manage_groups.php");
		}

		public static function MemberMouseGroupPagination($limit = 10, $count, $page, $start, $targetpage, $type = "groups") {
			$prev = $page - 1;
			$next = $page + 1;
			$lastpage = ceil($count / $limit);
			$pagination = "";
			$pagination .= "<div class=\"group_pagination\">";
			$pagination .= '<span class="group_prev_next">';
			$pagination .= 'Page';
			if ($page > 1) :
				$pagination .= '<a href="' . $targetpage . '&p=' . $prev . '" class="prev" title="Previous" style="margin-left:4px; margin-right:4px;">';
				$pagination .= MM_Utils::getIcon('chevron-circle-left', 'light-blue', '1.4em', '1px');
				$pagination .= '</a>';
			else :
				$pagination .= '<a href="javascript:void(0);" class="prev" title="Previous" style="margin-left:4px; margin-right:4px;">';
				$pagination .= MM_Utils::getIcon('chevron-circle-left', 'light-blue', '1.4em', '1px');
				$pagination .= '</a>';
			endif;
			$pagination .= $page;
			if ($page < $lastpage) :
				$pagination .= '<a href="' . $targetpage . '&p=' . $next . '" class="next" title="Next" style="margin-left:4px; margin-right:4px;">';
				$pagination .= MM_Utils::getIcon('chevron-circle-right', 'light-blue', '1.4em', '1px');
				$pagination .= '</a>';
			else :
				$pagination .= '<a href="javascript:void(0);" title="Next" style="margin-left:4px; margin-right:4px;">';
				$pagination .= MM_Utils::getIcon('chevron-circle-right', 'light-blue', '1.4em', '1px');
				$pagination .= '</a>';
			endif;
			$pagination .= 'of	' . $lastpage . ' pages';
			$pagination .= '</span>';
			$pagination .= '<span class="group_show">';
			$pagination .= 'Show ';
			$pagination .= "<select name=\"show_record\" id=\"show_record\" onchange=\"javascript:MGROUP.changeRecordVal(this.value,'" . $targetpage . "');\">";
			$pagination .= '<option value="10"';
			if ($limit == 10) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>10</option>';
			$pagination .= '<option value="20"';
			if ($limit == 20) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>20</option>';
			$pagination .= '<option value="50"';
			if ($limit == 50) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>50</option>';
			$pagination .= '<option value="100"';
			if ($limit == 100) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>100</option>';
			$pagination .= '<option value="500"';
			if ($limit == 500) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>500</option>';
			$pagination .= '<option value="1000"';
			if ($limit == 1000) :
				$pagination .= ' selected="selected"';
			endif;
			$pagination .= '>1000</option>';
			$pagination .= '</select> ';
			$pagination .= 'per page';
			$pagination .= '</span>';
			$pagination .= '<span class="group_found">' . $count . ' ' . $type . ' found</span>';
			$pagination .= "</div>";

			return $pagination;
		}

		/**
		 * Frontend Enqueues
		 */
		public function frontend_enqueues() {
			wp_register_script('groups-checkout', MGROUP_DIR . "/js/checkout.js", array('jquery'), filemtime(MGROUP_PATH . "js/checkout.js"), true);
			wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@11');

			$cfe = new MM_CorePageEngine();
			$checkout_page_id = $cfe->getCorePageIdByRefType(MM_CorePageType::$CHECKOUT, null, null);
			if (is_page($checkout_page_id)) {
				wp_enqueue_script('groups-checkout');
				wp_enqueue_script('sweetalert');
			}
		}

		/**
		 * Member Added to MemberMouse - Check if it's a group purchase and add to Groups table
		 */
		public function MemberMouseGroupMemberAdded($data) {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");

			global $wpdb;

			$groupId = get_option("mm_custom_field_group_id");

			// Check if Purchase is for a group (custom field will be filled in)
			if (isset($data["cf_" . $groupId]) && !empty($data["cf_" . $groupId])) {
				$cf 			 = $data["cf_" . $groupId];
				$memberId  = $data["member_id"];

				if (is_numeric($cf)) {
					// Group Leader. Custom Field contains Group Type ID

					$type = 'group-leader';

					$templateSql 		= "SELECT id,group_size,name FROM " . $wpdb->prefix . "group_items WHERE id = '" . $cf . "'";
					$templateResult = $wpdb->get_row($templateSql);

					if ($templateResult) {
						$template_id 	= $templateResult->id;
						$groupSize 		= $templateResult->group_size;
						$groupName 		= $templateResult->name;
						$sql 					= "INSERT INTO " . $wpdb->prefix . "group_sets (id,group_template_id,group_name,group_size,group_leader,group_status,createdDate,modifiedDate)VALUES('','" . $template_id . "','" . $groupName . "','" . $groupSize . "','" . $memberId . "','1',now(),now())";
						$wpdb->query($sql);
						wp_update_user(array('ID' => $memberId, 'role' => self::get_group_leader_role()));
					}
				} else {
					// Group Member. Custom Field contains group ID (g##)

					$type = 'group-member';

					/**
					 * Check if Group is Active
					 */
					$gID 		= substr($cf, 1);
					$is_group_active = $this->is_group_active($gID);

					if ($is_group_active) {
						/**
						 * Active Group
						 */
						$sql 		= "SELECT * FROM " . $wpdb->prefix . "group_sets WHERE id = '" . $gID . "'";
						$result = $wpdb->get_row($sql);

						if ($result) {
							$groupSize 	= $result->group_size;
							$groupId 		= $result->id;
							$sSql 			= "SELECT COUNT(id) AS count FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $gID . "' AND member_status = 1";
							$sRes 			= $wpdb->get_row($sSql);
							$gCount 		= $sRes->count;

							if ($gCount < $groupSize) {
								// There's space in this group. Add them.
								$sql 		= "INSERT INTO " . $wpdb->prefix . "group_sets_members (id,group_id,member_id,createdDate,modifiedDate)VALUES('','" . $groupId . "','" . $memberId . "',now(),now())";
								$query 	= $wpdb->query($sql);
							} else {
								// Reached Group Capacity. Do not add member
								$groupSql 		= "SELECT group_leader FROM " . $wpdb->prefix . "group_sets WHERE id = '" . $groupId . "'";
								$groupResult 	= $wpdb->get_row($groupSql);
								$group_leader = $groupResult->group_leader;

								// Add notices to DB
								$adminSql 		= "INSERT INTO " . $wpdb->prefix . "group_notices (id,group_id,user_id,leader_id,msg_type,createdDate,modifiedDate)VALUES('','" . $groupId . "','" . $memberId . "','" . $group_leader . "','0',now(),now())";
								$adminQuery 	= $wpdb->query($adminSql);

								$leaderSql 		= "INSERT INTO " . $wpdb->prefix . "group_notices (id,group_id,user_id,leader_id,msg_type,createdDate,modifiedDate)VALUES('','" . $groupId . "','" . $memberId . "','" . $group_leader . "','1',now(),now())";
								$leaderQuery 	= $wpdb->query($leaderSql);

								// Cancel member's access.
								$user = new MM_User($memberId);
								$userStatus = MM_AccessControlEngine::changeMembershipStatus($user, MM_Status::$CANCELED);
							}
						}
					} else {
						/**
						 * Group is Not Active
						 * - Do not give access
						 * - Change status of user to groups status
						 */
						$user = new MM_User($memberId);
						$user->setStatus(MM_Status::$CANCELED);
						$user->commitStatusOnly();
					}
				}

				/**
				 * ACTION - Group Member Added to Group
				 * @param $data Member Data sent by MemberMouse
				 * @param $type group-leader or group-member
				 */
				do_action('MemberMouseGroupMemberAdded', $data, $type);
			}
		}

		/**
		 * Check if Group ID is Active
		 *
		 * @param int $group_id
		 *
		 * @return bool
		 */
		public function is_group_active($group_id) {
			global $wpdb;
			$sql = "SELECT group_status FROM {$wpdb->prefix}group_sets WHERE id={$group_id}";
			$result = $wpdb->get_row($sql);

			if ($result->group_status == 0) {
				return false;
			} else {
				return true;
			}
		}
		/**
		 * Group Leader Member Status Changed.
		 * If status = cancelled, cancel all group member access as well
		 * If status = expired, expire all group member access as well
		 * If status = active, activate all group member access as well
		 *
		 * Also handles active/inactive status for the leaders group
		 *
		 * @param array $data
		 * @return void
		 *
		 */
		public function MemberMouseGroupLeaderStatus($data) {
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/mm-constants.php");
			include_once(WP_PLUGIN_DIR . "/membermouse/includes/init.php");
			global $wpdb;

			$memberId 		= $data["member_id"];
			$status 			= $data["status"];
			$leaderSql 		= "SELECT id FROM " . $wpdb->prefix . "group_sets WHERE group_leader = '" . $memberId . "'";
			$leaderResult = $wpdb->get_row($leaderSql);

			// Check if current member is a Group Leader
			if ($leaderResult) {
				$groupId = $leaderResult->id;
			} else {
				// Exit
				return;
			}

			// Check if User Status is changed and has a group. Update all members in group accordingly.
			if (!empty($groupId)) {
				if ($status == MM_Status::$CANCELED || $status == MM_Status::$EXPIRED || $status == MM_Status::$ACTIVE) {
					// Handles Status Changes: Cancelled, Expired, Active

					$sql = "SELECT member_id FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $groupId . "'";
					$results 	= $wpdb->get_results($sql);

					if (count($results) > 0) {
						foreach ($results as $result) {
							$user = new MM_User($result->member_id);
							$userStatus = MM_AccessControlEngine::changeMembershipStatus($user, $status);
						}
					}

					if ($status == MM_Status::$CANCELED || $status == MM_Status::$EXPIRED) {
						// Change group Status to Cancelled
						$groupSql	= "UPDATE {$wpdb->prefix}group_sets SET group_status='0', modifiedDate = now() WHERE group_leader={$memberId}";
						$wpdb->query($groupSql);
					} else if ($status == MM_Status::$ACTIVE) {
						// Change group Status back to Active
						$groupSql	= "UPDATE {$wpdb->prefix}group_sets SET group_status='1', modifiedDate = now() WHERE group_leader={$memberId}";
						$wpdb->query($groupSql);
					}
				}
			}
		}

		/**
		 * Membership Level Changed Handler
		 * Handles group leader upgrade and downgrade.
		 *
		 * If Group Leader Upgrades or Downgrades > Update group template and number allowed in group in database.
		 *
		 * TODO - if user has more accounts than are allowed in group changed to - they'll still have that number of members in database.
		 */
		public function membership_changed_handler($data) {
			global $wpdb;

			$groupId = get_option("mm_custom_field_group_id");

			// Check if Purchase is for a group leader (custom field will be filled in).
			// If group is numeric - it means they are a group leader
			if (isset($data["cf_" . $groupId]) && !empty($data["cf_" . $groupId]) && is_numeric($data["cf_" . $groupId])) {
				$cf 			 = $data["cf_" . $groupId];
				$memberId  = $data["member_id"];

				// Get Group Type data based on Group Type ID. Gives us group name and size
				$templateSql 		= "SELECT id, group_size, name FROM {$wpdb->prefix}group_items WHERE id={$cf}";
				$templateResult = $wpdb->get_row($templateSql);

				if (is_object($templateResult)) {
					// Capture Group Type Information for later
					$template_id 	= $templateResult->id;
					$groupSize 		= $templateResult->group_size;
					$groupName		= $templateResult->name;

					// Check if Leader already has a group
					$group = $this->get_group_from_leader_id($memberId);

					if ($group) {
						// Update Group Template ID and group size

						$original_group_template = $this->get_group_template_by_id($group->group_template_id);

						// Don't change group name if it's already been changed.
						if ($group->group_name !== $original_group_template->name) {
							$groupName = $group->group_name;
						}

						$wpdb->update($wpdb->prefix . "group_sets", array('group_template_id' => $template_id, 'group_size' => $groupSize, 'group_name' => $groupName), array('id' => $group->id));
					} else {
						// Create Group
						$sql 				= "INSERT INTO {$wpdb->prefix}group_sets (id,group_template_id,group_name,group_size,group_leader,group_status,createdDate,modifiedDate)VALUES('','" . $template_id . "','" . $groupName . "','" . $groupSize . "','" . $memberId . "','1',now(),now())";
						$query 			= $wpdb->query($sql);
						$updateUser = wp_update_user(array('ID' => $memberId, 'role' => self::get_group_leader_role()));
					}
				}
			}
		}

		/**
		 * Get Group ID with Member ID
		 * @return object | bool - If Group is found, returns row from database. If not, returns false
		 */
		public function get_group_from_leader_id($memberId) {
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "group_sets WHERE group_leader='" . $memberId . "'";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		/**
		 * Get Group ID with Member ID
		 * @return object | bool - If Group is found, returns row from database. If not, returns false
		 */
		public function get_group_from_group_id($group_id) {
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "group_sets WHERE id='" . $group_id . "'";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		/**
		 * Get Group ID with a Members User ID
		 * @return object | bool - If Group is found, returns row from database. If not, returns false
		 */
		public function get_group_from_member_id($memberId) {
			write_groups_log(__METHOD__);
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "group_sets_members WHERE member_id='" . $memberId . "' ORDER BY member_status DESC, createdDate DESC";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		/**
		 * Get List of Members in a Group
		 * @param string $group_id is the group ID you want to get the list of members from
		 * @return object | bool - If Group is found, returns row of members from database. If not, returns false
		 */
		public function get_members_in_group($group_id) {
			write_groups_log(__METHOD__);
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "group_sets_members WHERE group_id = '" . $group_id . "' AND member_status=1 ORDER BY member_status DESC, createdDate DESC";
			$result = $wpdb->get_results($sql);
			return $result;
		}

		/**
		 * Get Group Template from Group Template ID
		 * @param int $group_template_id
		 * @return object
		 */
		public function get_group_template_by_id($group_template_id) {
			global $wpdb;
			$sql = "SELECT * FROM {$wpdb->prefix}group_items WHERE id={$group_template_id}";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		/**
		 * Get Group Sign Up link with Leader User ID
		 *
		 * @param int $leader_user_id
		 *
		 * @return string | false
		 */
		public function get_group_signup_link($leader_user_id) {
			$group = $this->get_group_from_leader_id($leader_user_id);

			if (!$group) {
				return false;
			}

			$group_id = $group->id;
			$template_id = $group->group_template_id;

			$group_level_cost = $this->get_group_level_and_cost($template_id);

			if (!empty($group_level_cost->group_member_cost)) {
				$itemCost    = $group_level_cost->group_member_cost;
				$purchaseUrl   = MM_CorePageEngine::getCheckoutPageStaticLink($itemCost);
			} else {
				$itemCost    = $group_level_cost->member_memlevel;
				$purchaseUrl   = MM_CorePageEngine::getCheckoutPageStaticLink($itemCost, true);
			}

			$custom_field  = get_option("mm_custom_field_group_id");
			$purchaseUrl   .= '&cf_' . $custom_field . '=g' . $group_id;
			return $purchaseUrl;
		}

		/**
		 * Get Group Membership Level and Cost from Group Template ID
		 *
		 * @param int $template_id
		 *
		 * @return object
		 */
		public function get_group_level_and_cost($template_id) {
			global $wpdb;
			$itemSql = "SELECT member_memlevel, group_member_cost FROM {$wpdb->prefix}group_items WHERE id = '" . $template_id . "'";
			return $wpdb->get_row($itemSql);
		}

		/**
		 * Checkout Page Protection
		 * - if user is attempting to join a group that is not active, redirect them
		 * to the generic product page with query string added to URL to show jquery popup
		 *
		 * @return void
		 */
		public function checkout_groups_protection() {
			$cfe = new MM_CorePageEngine();
			$checkout_page_id = $cfe->getCorePageIdByRefType(MM_CorePageType::$CHECKOUT, null, null);
			if (!is_page($checkout_page_id)) {
				return;
			}

			$custom_field = get_option('mm_custom_field_group_id');
			$group_query = "cf_{$custom_field}";
			if (isset($_GET[$group_query]) && $_GET[$group_query] !== '') {
				$group_id = $_GET[$group_query];

				// Exit. This is for creating a group.
				if (strpos($group_id, 'g') === false) {
					return;
				}
				$group_id = substr($group_id, 1);

				if (!$this->is_group_active($group_id)) {
					// Group is not active. Redirect to default product checkout
					$checkout_url = MM_CorePageEngine::getUrl(MM_CorePageType::$CHECKOUT, '');
					$error_message = urlencode('Groups is not active');
					$query = "groups-error={$error_message}";

					/**
					 * Filter the redirect URL.
					 * @param $group_id
					 */
					$redirect_url = apply_filters('groups-mm-group-expired-redirect', "{$checkout_url}?{$query}", $group_id);

					header("Location: {$redirect_url}");
					exit();
				}
			}
		}

		/**
		 * Get Group Leader Role
		 * - Default = Group Leader
		 * - Ability to set group leader role via filter
		 *
		 * @return void
		 */
		public static function get_group_leader_role() {
			$role_slug =  apply_filters('groupsmm_group_leader_role_slug', 'Group Leader');

			if (!wp_roles()->is_role($role_slug)) {
				$display_name = apply_filters('groupsmm_group_leader_role_name', 'Group Leader');
				$capabilities = array("read" => true, "membermouse_group_capability" => true);
				add_role($role_slug, $display_name, $capabilities);
			}
			return $role_slug;
		}
	} // End Class
} // End if class exists

if (class_exists('MemberMouseGroupAddon')) :
	global $MemberMouseGroupAddon;
	$MemberMouseGroupAddon = new MemberMouseGroupAddon();
endif;
