<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: 3.0.0
 * Requires PHP: 7.0
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.me
 * License: MIT
 * Text Domain: wp-sentry
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// If the plugin was already loaded, do not load again.
if ( defined( 'WP_SENTRY_PRESENT' ) ) {
	return;
}

/**
 * Define the current plugin version
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_PLUGIN_VERSION' ) || define( 'WP_SENTRY_PLUGIN_VERSION', '3.0.0' );

/**
 * Establish a minimum PHP Version for WP Sentry
 *
 * @since 3.0.0
 */
defined ( 'WP_SENTRY_MIN_PHP_VERSION' ) || define( 'WP_SENTRY_MIN_PHP_VERSION', '7.0' );


/**
 * Resolve the WP Sentry Plugin File
 *
 * @since 1.0.0
 */
defined( 'WP_SENTRY_PLUGIN_FILE' ) || define( 'WP_SENTRY_PLUGIN_FILE', __FILE__ );

/**
 * Resolve the WP Sentry Plugin Bootstrap folder location
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_BOOTSTRAP_DIR' ) || define( 'WP_SENTRY_BOOTSTRAP_DIR', dirname( WP_SENTRY_PLUGIN_FILE ) . '/bootstrap/' );

/**
 * The function responsible for running the plugin setup process.
 *
 * @since 3.0.0
 */
function run_wp_sentry(){

  // Load constant definitions
  require_once WP_SENTRY_BOOTSTRAP_DIR . '/define.php';

  // Load plugin setup
  require_once WP_SENTRY_BOOTSTRAP_DIR . '/setup.php';

}

run_wp_sentry();
