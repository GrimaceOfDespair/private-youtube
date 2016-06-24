<?php

/**
 * PrivTube options
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube_Options {
  
  protected $version;

  protected $plugin_name;

  protected $assets;
  
  protected $google;
  
  protected $action;
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();
    $this->google = $module->get_google();

    add_action( 'admin_menu', [$this, 'menu'] );
    add_action( 'admin_init', [$this, 'menu_init'] );
  }
  
  public function menu() {
    
    $options_page = add_options_page('Settings Admin', _('Private YouTube Settings'), 'manage_options', 'privtube-setting-admin', [&$this, 'manage_options'] );
    
    add_action( 'load-' . $options_page, array( $this, 'enqueue_styles' ) );
    
  }
  
  public function enqueue_styles() {

    wp_enqueue_style( 'admin_css', $this->assets->get_path('styles/admin.css'), array(), $this->version, 'all' );

  }

  public function manage_options() {
    // Set class property
    $this->options = get_option( 'privtube_options' );
    ?>
    <div class="container">
        <h2><?php _('Private YouTube Settings') ?></h2>           
        <form method="post" action="options.php" role="form">
        <?php
            settings_fields( 'privtube_option_group' );   
            do_settings_sections( 'privtube-setting-admin' );
            submit_button( null , 'primary', 'submit_save', false);
        ?>
        <?php
            submit_button( __('Clear cache', 'privtube'), 'secondary', 'submit_clear', false );
        ?>
        </form>
    </div>
    <?php
  }
  
  /**
   * Register and add settings
   */
  public function menu_init()
  {        
    register_setting(
      'privtube_option_group', // Option group
      'privtube_options', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'privtube_settings', // ID
      _('Private YouTube Settings'), // Title
      array( $this, 'print_section_info' ), // Callback
      'privtube-setting-admin' // Page
    );  

    add_settings_field(
      'client_id', 
      _('Client ID'), 
      array( $this, 'client_id_callback' ), 
      'privtube-setting-admin', 
      'privtube_settings'
    );      

    add_settings_field(
      'client_secret', 
      _('Client Secret'), 
      array( $this, 'client_secret_callback' ), 
      'privtube-setting-admin', 
      'privtube_settings'
    );      
  }
  
  public function sanitize( $input )
  {
    if ($_POST['submit_save']) {
      
      $new_input = array();

      if( isset( $input['client_id'] ) )
        $new_input['client_id'] = sanitize_text_field( $input['client_id'] );

      if( isset( $input['client_secret'] ) )
        $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );
      
      $this->google->clear_token('access,refresh');
      
      $this->action = 'submit_save';
      
      return $new_input;
    
    } else if ($_POST['submit_clear']) {
      
      $this->google->clear_videocache();
      
      add_settings_error(
          'submit_clear',
          esc_attr( 'cache_cleared' ),
          __('Cache was cleared', 'privtube'),
          'updated'
      );
    }
  }

  public function print_section_info()
  {
    ?>
    Enter your YouTube client id and secret.<br />
    <br />
    <?php
      $refresh_token = $this->google->get_token('refresh');
      $access_token = $this->google->get_token('access');
      if ($access_token):
        $created = intval($access_token['created']);
        $created_date = date('r', $access_token['created']);
        $expires_in = intval($access_token['expires_in']);
        $expires_date = date('r', $created + $expires_in);
        ?>
        <div class="alert alert-info" role="alert">
          <dl>
            <dt>Access token (<?= $access_token['token_type'] ?>):</dt>
            <dd><?= $access_token['access_token'] ?></dd>
            <dt>Created:</dt>
            <dd><?= $created_date ?></dd>
            <dt>Expires in <?= $expires_in ?> seconds:</dt>
            <dd><?= $expires_date ?></dd>
            <dt>Refresh token:</dt>
            <dd><?= $refresh_token ?></dd>
          </dl>
        </div>
        <?php
      else:
      ?>
        <div class="alert alert-warning" role="alert">
          <strong><?= __('Not authenticated', 'privtube') ?></strong>
          <?php printf(__('Click <a href="%s">here</a> to authenticate', 'privtube'), $this->google->get_auth_url()) ?>
        </div>
      <?php
      endif;
  }

  public function client_id_callback()
  {
    $client_id = isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : '';
    ?>
      <div class="form-group">
        <input class="form-control" type="text" id="client_id" length="" name="privtube_options[client_id]" value="<?= $client_id ?>" />
      </div>
    <?php
  }

  public function client_secret_callback()
  {
    $client_secret = isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : '';
    ?>
      <div class="form-group">
        <input class="form-control" type="text" id="client_secret" length="" name="privtube_options[client_secret]" value="<?= $client_secret ?>" />
      </div>
    <?php
  }
}