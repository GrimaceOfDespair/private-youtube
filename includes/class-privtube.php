<?php

/**
 * PrivTube core
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube {

  protected $version;

  protected $plugin_name;

  protected $assets;
  
  protected $loader;

  public function __construct() {

    $this->version = '1.0.0';

    $this->plugin_name = 'privtube';

    $this->load_dependencies();
    
    $this->define_common_hooks();
    if (is_admin()) {
      $this->define_admin_hooks();
    }
  }
  
  public function get_version() {
    
    return $this->version;
    
  }

  public function get_plugin_name() {
    
    return $this->plugin_name;
    
  }

  private function load_dependencies() {

    $script_base = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-privtube-';
    foreach (array('loader', 'assets', 'admin', 'options') as $script) {
      require_once $script_base . $script . '.php';
    }

    $this->loader = new PrivTube_Loader();
  }

  private function define_common_hooks() {
    $this->loader->add_action( 'init', $this, 'load_text_domain' );
    $this->loader->add_action( 'activated_plugin', $this, 'fix_plugin_dependencies' );
  }

  private function define_admin_hooks() {
    
    $plugin_admin = new PrivTube_Admin( $this );
    $this->loader->add_action( 'admin_menu', $plugin_admin, 'menu' );
    $this->loader->add_action( 'wp_print_scripts', $plugin_admin, 'google_signin' );
    
    $plugin_options = new PrivTube_Options( $this );
    $this->loader->add_action( 'admin_menu', $plugin_options, 'menu' );
    $this->loader->add_action( 'admin_init', $plugin_options, 'menu_init' );
    
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    $this->loader->add_action( 'rest_api_init', $plugin_public, 'enable_privtube' );
    
  }
  
  public function run() {
    $this->loader->run();
  }

  public function get_assets() {
    static $assets;

    if (!$assets) {
      $assets = new PrivTube_Assets(WP_ENV === 'development');
    }

    return $assets;
  }

  public function fix_plugin_dependencies() {
    
    // ensure path to this file is via main wp plugin path
    $wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
    $this_plugin = plugin_basename(trim($wp_path_to_this_file));
    $this_plugin = 'private-youtube/privtube.php';
    
    $active_plugins = get_option('active_plugins');
    $this_plugin_key = array_search($this_plugin, $active_plugins);
    
    array_splice($active_plugins, $this_plugin_key, 1);
    array_push($active_plugins, $this_plugin);
    
    update_option('active_plugins', $active_plugins);
  }
  
  public function load_text_domain() {
    
    load_plugin_textdomain( $this->plugin_name, false, $this->domain . '/languages' );
    
  }
}
