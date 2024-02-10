<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Main Class
 * - Future: Move all functionality into this class for cleanup
 *
 * @author Mintun Media <hello@mintunmedia.com>
 * @since 2/10/24
 */
class MemberMouseGroups {

  private static $instance;

  /**
   * Returns an instance of this class
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new MemberMouseGroups();
    }
    return self::$instance;
  }

  /**
   * Constructor
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function __construct() {
    $this->load_classes();
    $this->load_filters();
    $this->load_actions();
  }

  /**
   * Load Classes
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function load_classes() {
    // Composer Autoload
    include_once(MMG_PLUGIN_PATH . 'includes/vendor/autoload.php');

    $classes = array(
      'logging' => 'MM_Logger',
    );

    foreach ($classes as $file => $class) {
      if (!class_exists($class)) {
        include_once(MMG_PLUGIN_PATH . 'includes/classes/class.' . $file . '.php');
      }
      $class::get_instance();
    }
  }

  /**
   * Load Filters
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function load_filters() {
    MM_Logger::log(__METHOD__);
  }

  /**
   * Load Actions
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function load_actions() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

    // Plugin testing page (if not using ACF)
    add_action('admin_menu', [$this, 'plugin_register_options_page']);
    add_action('wp_ajax_test_function', [$this, 'testing']);
  }

  /**
   * PLUGIN TESTING - Admin Enqueues -
   * Checks current page so we only enqueue on specific pages
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function admin_enqueue_scripts() {
    $screen = get_current_screen();

    if ($screen->id === 'tools_page_plugintesting') {
      wp_enqueue_script('plugintesting', ACR_PLUGIN_URL . 'inc/js/admin.js', ['jquery'], filemtime(ACR_PLUGIN_PATH . 'inc/js/admin.js'), true);
      wp_localize_script('plugintesting', 'pluginTesting', [
        'nonce' => wp_create_nonce('pluginTesting'),
        'ajaxurl' => admin_url('admin-ajax.php'),
      ]);
    }
  }

  /**
   * Plugin Testing- Register Options Page (under settings menu)
   * Used for plugin testing if ACF is not used
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function plugin_register_options_page() {
    $parent_slug = 'options-general.php'; // options-general.php, tools.php
    $page_title = 'Plugin Testing';
    $menu_title = 'Plugin Testing';
    $capability = 'manage_options';
    $menu_slug = 'plugintesting';
    $callback = [$this, 'plugin_options_page'];
    $position = null;
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback, $position);
  }

  /**
   * PLUGIN TESTING - Output for Plugin Settings Page
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function plugin_options_page() {
?>
    <div>
      <h2><?php echo get_admin_page_title(); ?></h2>
      <button class="button button-primary trigger-test-function">Test Me</button>
    </div>
<?php
  }

  /**
   * AJAX - PLUGIN TESTING - Test Function
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function testing() {
    MM_Logger::log(__METHOD__, null, true);

    wp_send_json_success();
  }

  /**
   * Enqueues and Registers
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function enqueue_scripts() {
    wp_register_style('mm-plugin-styles', ACR_PLUGIN_URL . 'inc/css/styles.css', array(), filemtime(ACR_PLUGIN_PATH . 'inc/css/styles.css'), 'all');
    wp_register_script('mm-plugin-scripts', ACR_PLUGIN_URL . 'inc/js/script.js', array('jquery'), filemtime(ACR_PLUGIN_PATH . 'inc/js/script.js'), true);
  }
}
