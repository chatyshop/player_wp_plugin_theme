<?php
/**
 * Plugin Name: CP WP Plugin
 * Description: Turn WordPress into a self-hosted video platform powered by ChatyPlayer.
 * Version: 0.25.0
 * Author: Chaty Technologies
 * License: GPL-2.0-or-later
 * Text Domain: cp-wp-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CPWP_VERSION', '0.25.0' );
define( 'CPWP_FILE', __FILE__ );
define( 'CPWP_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPWP_URL', plugin_dir_url( __FILE__ ) );

require_once CPWP_DIR . 'includes/class-cpwp-plugin.php';

register_activation_hook( __FILE__, array( 'CPWP_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CPWP_Plugin', 'deactivate' ) );

CPWP_Plugin::instance()->run();
