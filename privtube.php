<?php

/**
 * Private YouTube channel
 *
 * Manage your private YouTube channel with WordPress and
 * provide access to your WordPress members
 * (50 person limit by YouTube)
 *
 * @link              http://www.bithive.be/
 * @package           BitHive
 *
 * @wordpress-plugin
 * Plugin Name:       Private YouTube
 * Plugin URI:        http://www.bithive.be/
 * Description:       Manager your private YouTube channel with WordPress
 * Version:           1.0.0
 * Author:            BitHive
 * Author URI:        http://www.bithive.be/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       privtube
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// Path to the build directory for front-end assets
if (!defined('DIST_DIR')) {
  define('DIST_DIR', '/dist/');
}

// 'production' or 'development' environment
if (!defined('WP_ENV')) {
//  define('WP_ENV', 'production');
  define('WP_ENV', 'development');
}

require plugin_dir_path( __FILE__ ) . 'includes/class-privtube.php';

/**
 * Run plugin.
 */
function run_privtube() {
  $privtube = new PrivTube();
  $privtube->run();
}
run_privtube();