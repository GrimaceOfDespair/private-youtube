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
  
  protected $yt_client_id = '';
  
  protected $yt_client_secret = '';
  
  protected $yt_channel_id = '';
  
  protected $google_client = null;
  
  protected $google_api_error = null;
  
  protected $translations = array(
    'privtube' => array(
    ),
  );
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();
    
    $options = get_option('privtube_options');
    if ($options) {
      $this->yt_client_id = $options['client_id'];
      $this->yt_client_secret = $options['client_secret'];
      $this->yt_channel_id = $options['channel_id'];

      $this->google_client = $this->createGoogleClient();
    }
  }
  
  public function createGoogleClient() {
    
    set_include_path(dirname(__FILE__)."/../");
    require_once 'vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId($this->yt_client_id);
    $client->setClientSecret($this->yt_client_secret);
    $client->setScopes('https://www.googleapis.com/auth/youtube');
    $redirect = admin_url('admin.php?page=privtube-all-videos');
    $client->setRedirectUri($redirect);
    $client->setAccessType('offline');
    
    return $client;
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
      'nonce' => wp_create_nonce( 'wp_rest' ),
      'locale' => get_locale(),
      'translations' => $this->get_translations(),
      'templateBaseUrl' => plugin_dir_url( dirname(__file__)) . $templatePath,
      'version' => strval(filemtime( $root_path )),
      'clientId' => $this->yt_client_id,
      'channelId' => $this->yt_channel_id
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
  
  public function google_signin() {
    if ($this->yt_client_id) {
      echo '<meta name="google-signin-client_id" content="' . $this->yt_client_id . '">';
    }
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
          <strong><?= $this->google_api_error['type'] ?></strong>
          <?= $this->google_api_error['message'] ?>
          <br />
          <br />
          <?= sprintf(__('<a href="%s">Retry</a> authentication?', 'privtube'), $this->create_auth_url()) ?> 
        </div>
      <?php else:
        include( dirname(dirname( __FILE__ )) . '/templates/all-videos.php' );
        endif; ?>
    </div>
    <?php
  }

  public function new_videos() {
    ?>
    <h2><?php echo __('Upload Video', 'privtube') ?></h2>
    <div class="container">
      <?php
      load_template( dirname(dirname( __FILE__ )) . '/templates/new-video.php' );
      ?>
    </div>
    <?php
  }
  
  public function google_init() {
    
    $client = $this->google_client;
    
    if (isset($_GET['code'])) {
      $state = get_transient('_google_state_'. get_current_user_id());
      if ($state === strval($_GET['state'])) {
        
        $client->authenticate($_GET['code']);
        $this->set_token($client->getAccessToken());
        
        $redirect = admin_url('admin.php?page=privtube-all-videos');
        header('Location: ' . $redirect);
      }
      
    } else {
      
      $token = $this->get_token();
      if ($token) {
        $client->setAccessToken($token);
      }
    }

    $access_token = $client->getAccessToken();
    if ($access_token) {
      
      $this->set_token($access_token);
      
    } else {
      
      $authUrl = $this->create_auth_url();
      
      $this->google_api_error = array(
        type => __('Authorization Required:', 'privtube'),
        message => sprintf(__('<a href="%s">Authorize</a> before proceeding.', 'privtube'), $authUrl)
      );
    }  
  }

  public function create_auth_url() {
    
      $state = mt_rand();
      $this->google_client->setState($state);
      set_transient('_google_state_'. get_current_user_id(), $state);

      return $this->google_client->createAuthUrl();
  }
  
  public function prepare_videos() {
    
    $client = $this->google_client;
    
    // Check to ensure that the access token was successfully acquired.
    try {
      // Call the channels.list method to retrieve information about the
      // currently authenticated user's channel.
      $youtube = new Google_Service_YouTube($client);
      
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

        //$htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
        foreach ($playlistItemsResponse['items'] as $playlistItem) {
          
          $snippet = $playlistItem['snippet'];
          $video_id = $snippet['resourceId']['videoId'];
          
          $videos []= array(
            id => $video_id,
            title => $snippet['title'],
            publishedAt => $snippet['publishedAt'],
            thumbnail => $snippet['thumbnails']['default']['url'],
            url => 'https://www.youtube.com/watch?v=' . $video_id . '?rel=0',
            status => $playlistItem['status']['privacyStatus']
          );
        }
      }

      return $videos;
      
    } catch (Google_Service_Exception $e) {
      $this->google_api_error = array(
        type => __('Service error:', 'privtube'),
        message => $e->getMessage()
      );
    } catch (Google_Exception $e) {
      $this->google_api_error = array(
        type => __('Client error:', 'privtube'),
        message => $e->getMessage()
      );
    }
    
    return null;
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
  
  private function get_token() {
    return get_transient('_privtube_token');
  }
  
  private function set_token($token) {
    return set_transient('_privtube_token', $token);
  }
}
