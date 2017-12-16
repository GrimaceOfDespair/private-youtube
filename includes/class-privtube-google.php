<?php 
/*
 * Copyright Header - A WordPress plugin to list YouTube videos
 * Copyright (C) 2016-2017 Igor Kalders <igor@bithive.be>
 *
 * This file is part of Copyright Header.
 *
 * Copyright Header is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Copyright Header is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Copyright Header.  If not, see <http://www.gnu.org/licenses/>.
 */ ?>
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
    
    foreach (explode(',', $types) as $type) {
      delete_transient('privtube_' . $type . '_token');
    }
    
  }
  
  public function create_youtube_client() {
    
    return new Google_Service_YouTube($this->get_google_client());
    
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

  public function list_videos( $user_roles = null ) {
    
    if (is_null($user_roles)) {
      $cache_key = 'privtube_videos_all';
    } else if (count($user_roles) == 0) {
      $cache_key = 'privtube_videos_public';
    } else {
      $cache_key = 'privtube_videos_' . implode('_', $user_roles);
    }
    
    $videos = get_transient($cache_key);
    
    if ($videos) {
      return $videos;
    }
    
    $videos = $this->list_private_videos( $user_roles );
    
    set_transient($cache_key, $videos, 60 * 60);
    
    return $videos;
  }
    
  private function list_private_videos( $user_roles ) {
    
    // Call the channels.list method to retrieve information about the
    // currently authenticated user's channel.
    $youtube = $this->create_youtube_client();
    
    $query = '';
    if ($user_roles != null) {

      foreach ($user_roles as $user_role) {
        if ($query != '') {
          $query .= ' OR ';
        }
        $query .= '##' . $user_role;
      }
    }
    
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
      'type' => 'video',
      'q' => $query,
      'forMine' => true,
      'maxResults' => 50,
    ));
    
    $video_ids = array();
    foreach ($searchResponse['items'] as $video) {
      
      $video_ids []= $video['id']['videoId'];
    }
    $listResponse = $youtube->videos->listVideos('snippet,status', array(
      'id' => implode( ',', $video_ids ),
      'maxResults' => 50
    ));

    $public = !is_null($user_roles) && count($user_roles) == 0;
    
    $videos = array();
    
    foreach ($listResponse['items'] as $videoDetails) {
      
      $video = $this->create_video($videoDetails);

      if ( $public && $video['status'] != 'public' ) {
        continue;
      }
      
      $videos []= $video;
    }
    
    return $videos;
  }
  
  public function clear_videocache() {
    
    $this->clear('privtube_videos');    
  }
  
  public function set_video_properties($video_id, $video_title, $video_description, $video_status, $video_tags) {
    
    switch ($video_status) {
      case 'unlisted':
      case 'public':
      case 'private':
        break;
        
      default:
        throw new Exception('Video status ' . $video_status . ' not supported');
    }
    
    $youtube = $this->create_youtube_client();
    
    $listResponse = $youtube->videos->listVideos('snippet,status', array(
      'id' => $video_id
    ));
    
    if (count($listResponse['items']) == 0) {
      throw new Exception('Video with id ' . $video_id . ' not found');
    }
    
    $video = $listResponse['items'][0];

    $status = $video['status'];
    $status->setPrivacyStatus($video_status);
    
    $snippet = $video['snippet'];
    $snippet->setTitle($video_title);
    $snippet->setDescription($video_description);
    $snippet->setTags($video_tags);

    $updated_video = $youtube->videos->update('status,snippet', $video);
    
    $this->clear_videocache();
    
    return $this->create_video($updated_video);
  }
  
  public function google_signin() {
    if ($this->yt_client_id) {
      echo '<meta name="google-signin-client_id" content="' . $this->yt_client_id . '">';
    }
  }
  
  private function create_video($video) {
    
    $id = $video->getId();
    $snippet = $video['snippet'];
    $video_id = $video['id'];

    return array(
      'id' => $video_id,
      'title' => $snippet['title'],
      'description' => $snippet['description'],
      'publishedAt' => mysql2date( get_option('date_format'), $snippet['publishedAt']),
      'thumbnail' => $snippet['thumbnails']['default']['url'],
      'url' => 'https://www.youtube.com/watch?v=' . $video_id . '?rel=0',
      'embed' => 'https://www.youtube.com/embed/' . $video_id . '?rel=0&showinfo=0&modestbranding=1',
      'status' => $video['status']['privacyStatus'],
      'uploadStatus' => $video['status']['uploadStatus'],
      'tags' => $snippet['tags']
    );
  }

  private function clear( $prefix = 'privtube' ) {
    
    global $wpdb;

    $options = $wpdb->options;

    $t = esc_sql( "_transient_timeout_" . $prefix . '%');

    $sql = $wpdb->prepare (
      "
        SELECT option_name
        FROM $options
        WHERE option_name LIKE '%s'
      ",
      $t
    );

    $transients = $wpdb->get_col( $sql );

    // For each transient...
    foreach( $transients as $transient ) {

      // Strip away the WordPress prefix in order to arrive at the transient key.
      $key = str_replace( '_transient_timeout_', '', $transient );

      // Now that we have the key, use WordPress core to the delete the transient.
      delete_transient( $key );

    }
    
    // But guess what?  Sometimes transients are not in the DB, so we have to do this too:
    wp_cache_flush();
  }
  
}