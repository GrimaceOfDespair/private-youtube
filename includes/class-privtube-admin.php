<?php

/**
 * PrivTube admin
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube_Admin {

  protected $version;

  protected $plugin_name;

  protected $assets;
  
  protected $translations = array(
    'privtube' => array(
    ),
  );
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();

  }

  public function __($text) {
    
    return __($text, $this->plugin_name);
    
  }
  
  public function enqueue_styles() {

    wp_enqueue_style( 'admin_css', $this->assets->get_path('styles/admin.css'), array(), $this->version, 'all' );

  }

  public function enqueue_scripts() {
  
    $paths = $this->assets->get_paths('scripts/admin.js');
    foreach ($paths->dependencies as $path) {
      $handle = $path->path;
      
      if (preg_match('/[\\\\\/]jquery\.js$/', $handle)) {
        $handle = 'jquery';
        wp_deregister_script($handle);
      }
      
      wp_enqueue_script( $handle, $path->url, null, '' . $path->version );
    }
    
    $root = $paths->root;
    wp_register_script( 'admin_js', $root->url, null, $root->version, false );
      
    $templatePath = WP_ENV === 'development' ? 'assets/' : 'dist/';
    $root_path = plugin_dir_path( dirname(__FILE__) ) . $root->path;
    
    $client_id = '';
    $channel_id = '';
    $options = get_option('privtube_options');
    if ($options) {
      $client_id = $options['client_id'];
      $channel_id = $options['channel_id'];
    }

    wp_localize_script( 'admin_js', 'configuration', array(
      'nonce' => wp_create_nonce( 'wp_rest' ),
      'locale' => get_locale(),
      'translations' => $this->get_translations(),
      'templateBaseUrl' => plugin_dir_url( dirname(__file__)) . $templatePath,
      'version' => strval(filemtime( $root_path )),
      'clientId' => $client_id,
      'channelId' => $channel_id
    ));
    
    wp_enqueue_script( 'admin_js');
    
    $culture = strtolower(str_replace('_', '-', get_locale()));
    wp_register_script( 'angular-locale', "https://code.angularjs.org/1.5.3/i18n/angular-locale_$culture.js" );
    wp_enqueue_script( 'angular-locale');
    
    wp_register_script( 'youtube-api', 'https://apis.google.com/js/client:plusone.js?onload=onLoadPlus', null, null, false );
    wp_enqueue_script( 'youtube-api');
    
    //wp_register_script( 'youtube-api', 'https://apis.google.com/js/client.js' );
    //wp_enqueue_script( 'youtube-api');
  }
  
  public function menu() {
    global $submenu;
    
    $admin_pages = array(
      add_menu_page( $this->__('Private videos'), $this->__('Videos'), 'manage_videos', 'privtube-videos', [&$this, 'manage_videos'], 'dashicons-media-video', 57 ),
    );
    
    foreach ($admin_pages as $admin_page) {
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_scripts' ) );
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_styles' ) );
    }
  }
  
  public function manage_videos() {
    load_template( dirname(dirname( __FILE__ )) . '/templates/manage-videos.php' );
  }

  private function get_translations() {
    
    $merged_translations = array();
    
    foreach ($this->translations as $text_domain => $translations) {
      foreach ($this->translations[$text_domain] as $translation_key) {
        if (is_string($translation_key)) {
          $merged_translations[$translation_key] = __($translation_key, $text_domain);
        } else {
          foreach ($translation_key as $context => $context_translation_keys) {
            $x_translations = array();
            if (is_string($context_translation_keys)) {
              $x_translations[$context_translation_keys] =
                _x($context_translation_keys, $context, $text_domain);
            } else {
              foreach ($context_translation_keys as $context_translation_key) {
                $x_translations[$context_translation_key] =
                  _x($context_translation_key, $context, $text_domain);
              }
            }
            $merged_translations[$context] = $x_translations;
          }
        }
      }
    }
    
    return $merged_translations;
  }

  private function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
  }
}
