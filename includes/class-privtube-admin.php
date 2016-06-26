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
  
  protected $google;
  
  protected $translations = array(
    'privtube' => array(
    ),
  );
  
  protected $notices = array();
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();
    $this->google = $module->get_google();
    
    add_action( 'admin_menu', [$this, 'menu'] );
    
    add_action( 'wp_ajax_listVideos', [$this, 'list_videos'] );
    add_action( 'wp_ajax_updateVideo', [$this, 'updateVideo'] );
    add_action( 'admin_notices', [$this, 'admin_notices'] );
  }
  
  public function __($text) {
    
    return __($text, $this->plugin_name);
    
  }
  
  public function admin_notices() {

    foreach ( $this->notices as $notice ) {
      ?>
      <div class="<?= $notice['type'] ?>">
        <p><?= $notice['message'] ?></p>
      </div>
      <?php
    }
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
    
    wp_localize_script( 'admin_js', 'configuration', array(
      'nonce' => wp_create_nonce( 'privtube' ),
      'locale' => get_locale(),
      'translations' => $this->get_translations(),
      'templateBaseUrl' => plugin_dir_url( dirname(__file__)) . $templatePath,
      'manageVideosUrl' => admin_url('upload.php?page=privtube-all-videos'),
      'ajaxurl' => admin_url('admin-ajax.php'),
      'version' => strval(filemtime( $root_path )),
      'clientId' => $this->google->get_client_id()
    ));
    
    wp_enqueue_script( 'admin_js');
    
    $culture = strtolower(str_replace('_', '-', get_locale()));
    wp_register_script( 'angular-locale', "https://code.angularjs.org/1.5.3/i18n/angular-locale_$culture.js" );
    wp_enqueue_script( 'angular-locale');
  }
  
  public function google_youtube_api() {

    wp_register_script( 'youtube-api', 'https://apis.google.com/js/client:plusone.js', null, null, false );
    wp_enqueue_script( 'youtube-api');
    
  }
  
  public function ajax_error($message_object) {
    status_header( 500 );
    wp_send_json_error($message_object);
  }
  
  public function ajax_success($message_object) {
    wp_send_json_success($message_object);
  }
  
  public function updateVideo() {
    
    try {
      
      $data = json_decode(file_get_contents('php://input'));

      $video_id = $data->id;
      if (!$video_id) {
        throw new Exception( 'Video id required' );
      }
      
      $video_status = $data->status;
      if (!$video_status) {
        throw new Exception( 'Video status required' );
      }
      
      $video_title = $data->title;
      if (!$video_title) {
        throw new Exception( 'Video title required' );
      }
      
      $video_description = $data->description;
      
      $video_tags = $data->tags;

      $video = $this->google->set_video_properties(
        $video_id,
        $video_title,
        $video_description,
        $video_status,
        $video_tags
      );
      
      $this->ajax_success($video);
      
    } catch (Exception $e) {
      
      $this->ajax_error($e->getMessage());
    }
  }
  
  public function list_videos() {
    
    try {
      
      if (!check_ajax_referer( 'privtube', 'nonce', false )) {
        throw new Exception('Security check failed');
      }
      
      $this->ajax_success(array(
        videos => $this->google->list_videos( false ),
      ));
      
    } catch (Exception $e) {
      
      $this->ajax_error($e->getMessage());
    }
  }
  
  public function menu() {
    global $submenu;
    
    $admin_pages = array(
      add_media_page( $this->__('All videos', 'privtube'), $this->__('All videos', 'privtube'), 'manage_videos', 'privtube-all-videos', [&$this, 'manage_videos']),
      add_media_page( $this->__('New video', 'privtube'), $this->__('New video', 'privtube'), 'manage_videos', 'privtube-new-video', [&$this, 'new_video']),
    );
    
    foreach ($admin_pages as $admin_page) {
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_scripts' ) );
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_styles' ) );
      add_action( 'load-' . $admin_page, array( $this, 'handle_actions' ) );
    }
    
    add_action( 'load-media_page_privtube-new-video', [$this, 'google_youtube_api'] );
  }
  
  public function handle_actions() {
    
    if ($_POST['submit_clear']) {

      $this->google->clear_videocache();
      
      $this->notices []= array(
        type => 'updated',
        message => __('Cache was cleared', 'privtube')
      );
    }
  }
  
  public function manage_videos() {
    ?>
    <h2><?php echo __('YouTube Videos', 'privtube') ?></h2>
    <div class="container">
      <?php
        include( dirname(dirname( __FILE__ )) . '/templates/manage-videos.php' );
      ?>
    </div>
    <?php
  }

  public function new_video() {
    ?>
    <h2><?php echo __('Upload Video', 'privtube') ?></h2>
    <div class="container">
      <?php
        load_template( dirname(dirname( __FILE__ )) . '/templates/new-video.php' );
      ?>
    </div>
    <?php
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
