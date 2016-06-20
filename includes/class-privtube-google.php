<?php
/**
 * PrivTube Google
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube_Google {
  
  protected $yt_client_id = '';
  
  protected $yt_client_secret = '';

  protected $google_client = null;
  
  protected $error = null;
  
  protected $authenticated = false;
  
  public function __construct() {
    
    $options = get_option('privtube_options');
    if ($options) {
      $this->yt_client_id = $options['client_id'];
      $this->yt_client_secret = $options['client_secret'];

      $this->google_client = $this->create_google_client();
    }
    
    add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
    add_action( 'admin_init', [$this, 'google_init'] );
    add_action( 'wp_print_scripts', [$this, 'google_signin'] );
  }  
  
  public function get_client_id() {
    
    return $this->yt_client_id;
    
  }

  public function get_yt_client_secret() {
    
    return $this->yt_client_secret;
    
  }

  public function create_google_client() {
    
    set_include_path(dirname(__FILE__)."/../");
    require_once 'vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId($this->yt_client_id);
    $client->setClientSecret($this->yt_client_secret);
    $client->setScopes('https://www.googleapis.com/auth/youtube');
    $redirect = admin_url('options-general.php?page=privtube-setting-admin');
    $client->setRedirectUri($redirect);
    $client->setAccessType('offline');
    
    return $client;
  }
  
  public function is_authenticated() {
    
    return $this->autenticated;
    
  }
  
  private function get_token() {
    
    return get_transient('_privtube_token');
    
  }
  
  private function set_token($token) {
    
    return set_transient('_privtube_token', $token);
    
  }
  
  public function create_youtube_client() {
    
    return new Google_Service_YouTube($this->google_client);
    
  }

  public function enqueue_scripts() {
    wp_register_script( 'youtube-api', 'https://apis.google.com/js/client:plusone.js?onload=onLoadPlus', null, null, false );
    wp_enqueue_script( 'youtube-api');
  }
  
  public function get_auth_url() {
    
      $state = mt_rand();
      $this->google_client->setState($state);
      set_transient('_google_state_'. get_current_user_id(), $state);

      return $this->google_client->createAuthUrl();
  }
  
  public function google_init() {
    
    $client = $this->google_client;
    
    if (isset($_GET['code'])) {
      $state = get_transient('_google_state_'. get_current_user_id());
      if ($state === strval($_GET['state'])) {
        
        $client->authenticate($_GET['code']);
        $this->set_token($client->getAccessToken());
        
        $redirect = admin_url('options-general.php?page=privtube-setting-admin');
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
      
      $this->autenticated = true;
      
      $this->set_token($access_token);
      
    } else {
      
      $this->autenticated = false;
      
    }  
  }

  public function google_signin() {
    if ($this->yt_client_id) {
      echo '<meta name="google-signin-client_id" content="' . $this->yt_client_id . '">';
    }
  }
  
}