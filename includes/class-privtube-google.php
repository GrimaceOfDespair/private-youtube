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
  
  public function __construct() {
    
    $options = get_option('privtube_options');
    if ($options) {
      $this->yt_client_id = $options['client_id'];
      $this->yt_client_secret = $options['client_secret'];
    }
    
    add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
    add_action( 'wp_print_scripts', [$this, 'google_signin'] );
  }  
  
  public function get_client_id() {
    
    return $this->yt_client_id;
    
  }

  public function get_yt_client_secret() {
    
    return $this->yt_client_secret;
    
  }

  public function get_google_client() {
    
    if ($this->google_client) {
      return $this->google_client;
    }
    
    $client = new Google_Client();
    $client->setClientId($this->yt_client_id);
    $client->setClientSecret($this->yt_client_secret);
    $client->setScopes('https://www.googleapis.com/auth/youtube');
    $redirect = admin_url('options-general.php?page=privtube-setting-admin');
    $client->setRedirectUri($redirect);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    
    $this->google_init($client);
    
    return $this->google_client = $client;
  }
  
  public function get_token($type) {
    
    return get_transient('privtube_' . $type . '_token');
    
  }
  
  private function set_token($type, $token) {
    
    set_transient('privtube_' . $type . '_token', $token);
    
  }
  
  public function clear_token($types) {
    
    foreach (split(',', $types) as $type) {
      delete_transient('privtube_' . $type . '_token');
    }
    
  }
  
  public function create_youtube_client() {
    
    return new Google_Service_YouTube($this->get_google_client());
    
  }

  public function enqueue_scripts() {
    wp_register_script( 'youtube-api', 'https://apis.google.com/js/client:plusone.js?onload=onLoadPlus', null, null, false );
    wp_enqueue_script( 'youtube-api');
  }
  
  public function get_auth_url() {
    
    $client = $this->get_google_client();
    
    $state = mt_rand();
    $client->setState($state);
    set_transient('privtube_userstate_'. get_current_user_id(), $state);

    return $client->createAuthUrl();
  }
  
  public function google_init($client) {
    
    if (isset($_GET['code'])) {
      $state = get_transient('privtube_userstate_'. get_current_user_id());
      if ($state === strval($_GET['state'])) {
        
        $client->authenticate($_GET['code']);
        $this->set_token('access', $client->getAccessToken());
        $this->set_token('refresh', $client->getRefreshToken());
        
        $redirect = admin_url('options-general.php?page=privtube-setting-admin');
        header('Location: ' . $redirect);
      }
      
    } else {
      
      $token = $this->get_token('access');
      if ($token) {
        
        $client->setAccessToken($token);
        if ($client->isAccessTokenExpired()) {
          
          $refresh_token = $this->get_token('refresh');
          $client->refreshToken($refresh_token);
        }
      }
    }

    $access_token = $client->getAccessToken();
    if ($access_token) {
      
      $this->set_token('access', $access_token);
      
    }  
  }

  public function list_videos() {
    
    $videos = get_transient('privtube_list_videos');
    if ($videos) {
      
      return $videos;
      
    }
    
    // Call the channels.list method to retrieve information about the
    // currently authenticated user's channel.
    $youtube = $this->create_youtube_client();
    
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

    set_transient('privtube_list_videos', $videos, 60 * 60);
    
    return $videos;
  }
  
  public function set_video_status($video_id, $video_status) {
    
    switch ($video_status) {
      case 'unlisted':
      case 'public':
      case 'private':
        break;
        
      default:
        throw new Exception('Video status ' . $video_status . ' not supported');
    }
    
    $status = new Google_Service_YouTube_VideoStatus();
    $status->setPrivacyStatus($video_status);

    $video = new Google_Service_YouTube_Video();
    $video->setId($video_id);
    $video->setStatus($status);
  
    $youtube = $this->create_youtube_client();
    
    $updated_video = $youtube->videos->update('status', $video);
    
    delete_transient('privtube_list_videos');
    
    return $this->create_video($updated_video);
  }
  
  public function google_signin() {
    if ($this->yt_client_id) {
      echo '<meta name="google-signin-client_id" content="' . $this->yt_client_id . '">';
    }
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

}