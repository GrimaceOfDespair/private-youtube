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

  public function get_google() {
    
    return $this->google;
    
  }
  
  private function load_dependencies() {

    set_include_path(dirname(__FILE__)."/../");
    require_once 'vendor/autoload.php';
    
    $script_base = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-privtube-';
    foreach (array(
    
      'loader',
      'assets',
      'admin',
      'options',
      'google',
      
    ) as $script) {
      
      require_once $script_base . $script . '.php';
      
    }

    $this->loader = new PrivTube_Loader();
  }
  
  private function define_common_hooks() {
    
    $this->google = new PrivTube_Google();
    
    $this->loader->add_action( 'plugins_loaded', $this, 'load_text_domain' );
    $this->loader->add_action( 'activated_plugin', $this, 'fix_plugin_dependencies' );
    add_shortcode( 'privtube', [$this, 'privtube_shortcode'] );
  }

  private function define_admin_hooks() {
    
    $plugin_admin = new PrivTube_Admin( $this );
    
    $plugin_options = new PrivTube_Options( $this );
    
    //$this->loader->add_action( 'rest_api_init', $plugin_public, 'enable_privtube' );
    
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
  
  public function privtube_shortcode($atts) {
    
    extract( shortcode_atts( array( 'roles' => null ), $atts ) );
    
    if ( is_null($roles) ) {
      $roles = wp_get_current_user()->roles;
    } else if ($roles == '') {
      $roles = array();
    } else {
      $roles = explode(',', $roles);
    }
  
    $videos = $this->google->list_videos( $roles );
    
    ob_start();
    ?>
    <div class="container">
    
      <?php
        include( dirname(dirname( __FILE__ )) . '/templates/videos.php' );
      ?>
    </div>
    <?php
    
    $result = ob_get_contents();
    
    ob_end_clean();
    
    return $result;
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

    load_plugin_textdomain( $this->plugin_name, false, 'private-youtube/languages' );
    
  }
}
