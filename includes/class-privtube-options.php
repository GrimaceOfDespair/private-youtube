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
  
  public function __construct( $module ) {

    $this->version = $module->get_version();
    $this->assets = $module->get_assets();
    $this->plugin_name = $module->get_plugin_name();

  }
  
  public function menu() {
    
    add_options_page('Settings Admin', _('Private YouTube Settings'), 'manage_options', 'privtube-setting-admin', [&$this, 'manage_options'] );
    
  }
  
  public function manage_options() {
    // Set class property
    $this->options = get_option( 'privtube_options' );
    ?>
    <div class="wrap">
        <h2><?php _('Private YouTube Settings') ?></h2>           
        <form method="post" action="options.php">
        <?php
            settings_fields( 'privtube_option_group' );   
            do_settings_sections( 'privtube-setting-admin' );
            submit_button(); 
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

    add_settings_field(
      'channel_id', 
      _('Channel ID'), 
      array( $this, 'channel_id_callback' ), 
      'privtube-setting-admin', 
      'privtube_settings'
    );      
  }
  
  public function sanitize( $input )
  {
    $new_input = array();

    if( isset( $input['client_id'] ) )
      $new_input['client_id'] = sanitize_text_field( $input['client_id'] );

    if( isset( $input['client_secret'] ) )
      $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );

    if( isset( $input['channel_id'] ) )
      $new_input['channel_id'] = sanitize_text_field( $input['channel_id'] );

    return $new_input;
  }

  public function print_section_info()
  {
    ?>
    Enter your YouTube client id
    <?php
  }

  public function client_id_callback()
  {
    $client_id = isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : '';
    ?>
      <input class="regular-text" type="text" id="client_id" length="" name="privtube_options[client_id]" value="<?= $client_id ?>" />
    <?php
  }

  public function client_secret_callback()
  {
    $client_secret = isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : '';
    ?>
      <input class="regular-text" type="text" id="client_secret" length="" name="privtube_options[client_secret]" value="<?= $client_secret ?>" />
    <?php
  }

  public function channel_id_callback()
  {
    $channel_id = isset( $this->options['channel_id'] ) ? esc_attr( $this->options['channel_id']) : '';
    ?>
      <input class="regular-text" type="text" id="channel_id" length="" name="privtube_options[channel_id]" value="<?= $channel_id ?>" />
    <?php
  }
}