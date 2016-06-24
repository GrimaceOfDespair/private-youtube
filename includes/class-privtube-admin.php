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
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();
    $this->google = $module->get_google();
    
    add_action( 'admin_menu', [$this, 'menu'] );
    
    add_action( 'wp_ajax_listVideos', [$this, 'list_videos'] );
    add_action( 'wp_ajax_setVideoStatus', [$this, 'set_video_status'] );
    //add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles'] );
    //add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
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
    
    wp_localize_script( 'admin_js', 'configuration', array(
      'nonce' => wp_create_nonce( 'privtube' ),
      'locale' => get_locale(),
      'translations' => $this->get_translations(),
      'templateBaseUrl' => plugin_dir_url( dirname(__file__)) . $templatePath,
      'ajaxurl' => admin_url('admin-ajax.php'),
      'version' => strval(filemtime( $root_path )),
      'clientId' => $this->google->get_client_id(),
    ));
    
    wp_enqueue_script( 'admin_js');
    
    $culture = strtolower(str_replace('_', '-', get_locale()));
    wp_register_script( 'angular-locale', "https://code.angularjs.org/1.5.3/i18n/angular-locale_$culture.js" );
    wp_enqueue_script( 'angular-locale');
  }
  
  public function ajax_error($message_object) {
    status_header( 500 );
    wp_send_json_error($message_object);
    die();
  }
  
  public function ajax_success($message_object) {
    wp_send_json_success($message_object);
  }
  
  public function set_video_status() {
    
    $data = json_decode(file_get_contents('php://input'));

    $video_id = $data->id;
    if (!$video_id) {
      $this->ajax_error( array( message=> 'Video id required' ) );
    }
    
    $video_status = $data->status;
    if (!$video_status) {
      $this->ajax_error( array( message=> 'Video status required' ) );
    }
    
    switch ($video_status) {
      case 'unlisted':
      case 'public':
      case 'private':
        break;
        
      default:
        $this->ajax_error( array( message=> 'Video status ' . $video_status . ' not supported') );
    }
    
    try {

      $status = new Google_Service_YouTube_VideoStatus();
      $status->setPrivacyStatus($video_status);

      $video = new Google_Service_YouTube_Video();
      $video->setId($video_id);
      $video->setStatus($status);
    
      $youtube = $this->google->create_youtube_client();
      
      $updated_video = $youtube->videos->update('status', $video);
      
    } catch (Google_Service_Exception $e) {
      $this->ajax_error(array(
        type => __('Service error', 'privtube'),
        message => $e->getMessage()
      ));
    } catch (Google_Exception $e) {
      $this->ajax_error(array(
        type => __('Client error', 'privtube'),
        message => $e->getMessage()
      ));
    }
    
    delete_transient('privtube_list_videos');
    
    $this->ajax_success($this->create_video($updated_video));
  }
  
  public function list_videos() {
    
    if (!check_ajax_referer( 'privtube', 'nonce', false )) {
      $this->ajax_error(array(message => 'Security check failed'));
    }
    
    $videos = get_transient('privtube_list_videos');
    if (!$videos) {
      
      $videos = $this->prepare_videos();
      
      if ($this->google_api_error) {
        $this->ajax_error($this->google_api_error);
      }
      
      set_transient('privtube_list_videos', $videos, 60 * 60);
    }
    
    $this->ajax_success(array(
      videos => $videos,
    ));
  }
  
  public function menu() {
    global $submenu;
    
    $admin_pages = array(
      add_media_page( $this->__('All videos', 'privtube'), $this->__('All videos', 'privtube'), 'manage_videos', 'privtube-all-videos', [&$this, 'all_videos']),
      add_media_page( $this->__('New video', 'privtube'), $this->__('New video', 'privtube'), 'manage_videos', 'privtube-new-video', [&$this, 'new_video']),
    );
    
    foreach ($admin_pages as $admin_page) {
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_scripts' ) );
      add_action( 'load-' . $admin_page, array( $this, 'enqueue_styles' ) );
    }
  }
  
  public function all_videos() {
    
    $videos = $this->prepare_videos();
    ?>
    <h2><?php echo __('YouTube Videos', 'privtube') ?></h2>
    <div class="container">
      <?php if ($this->google_api_error): ?>
        <div class="alert alert-danger" role="alert">
          <strong><?= $this->google_api_error['type'] ?></strong><br />
          <code><?= $this->google_api_error['message'] ?></code><br />
          <br />
          <?=
            sprintf(__('Click <a href="%s">here</a> to reconfigure YouTube access', 'privtube'),
              admin_url('options-general.php?page=privtube-setting-admin'));
          ?> 
        </div>
      <?php else:
        include( dirname(dirname( __FILE__ )) . '/templates/all-videos.php' );
        endif; ?>
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
  
  public function prepare_videos() {
    
    // Check to ensure that the access token was successfully acquired.
    try {
      // Call the channels.list method to retrieve information about the
      // currently authenticated user's channel.
      $youtube = $this->google->create_youtube_client();
      
      $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
        'mine' => 'true',
      ));
      
      $videos = array();
      
      foreach ($channelsResponse['items'] as $channel) {
        // Extract the unique playlist ID that identifies the list of videos
        // uploaded to the channel, and then call the playlistItems.list method
        // to retrieve that list.
        $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

        $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet,status', array(
          'playlistId' => $uploadsListId,
          'maxResults' => 50
        ));

        foreach ($playlistItemsResponse['items'] as $playlistItem) {
          
          $videos []= $this->create_video($playlistItem);
        }
      }

      return $videos;
      
    } catch (Google_Service_Exception $e) {
      $this->google_api_error = array(
        type => __('Service error', 'privtube'),
        message => $e->getMessage()
      );
    } catch (Google_Exception $e) {
      $this->google_api_error = array(
        type => __('Client error', 'privtube'),
        message => $e->getMessage()
      );
    }
    
    return null;
  }
  
  private function create_video($playlistItem) {
    
    $id = $playlistItem->getId();
    $snippet = $playlistItem['snippet'];
    $video_id = $snippet['resourceId']['videoId'];
    
    return array(
      id => $video_id,
      title => $snippet['title'],
      publishedAt => mysql2date( get_option('date_format'), $snippet['publishedAt']),
      thumbnail => $snippet['thumbnails']['default']['url'],
      url => 'https://www.youtube.com/watch?v=' . $video_id . '?rel=0',
      status => $playlistItem['status']['privacyStatus']
    );
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
