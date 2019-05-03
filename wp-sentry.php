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

// If the plugin was already loaded as a mu-plugin do not load again.
if ( defined( 'WP_SENTRY_MU_LOADED' ) ) {
	return;
}

/**
 * Define the current plugin version
 * @since 3.0.0
 */
defined( 'WP_SENTRY_PLUGIN_VERSION' ) || define( 'WP_SENTRY_PLUGIN_VERSION', '3.0.0' );

/**
 * Establish a minimum PHP Version for WP Sentry
 * @since 3.0.0
 */
defined ( 'WP_SENTRY_MIN_PHP_VERSION' ) || define( 'WP_SENTRY_MIN_PHP_VERSION', '7.0' );

/**
 * Define a Text Domain for WP Sentry.
 * @since 3.0.0
 */
defined( 'WP_SENTRY_TEXT_DOMAIN' ) || define( 'WP_SENTRY_TEXT_DOMAIN', 'wp-sentry' );

/**
 * Resolve the Sentry Plugin File
 * @since 1.0.0
 */
defined( 'WP_SENTRY_PLUGIN_FILE' ) || define( 'WP_SENTRY_PLUGIN_FILE', __FILE__ );

/**
 * Resolve the Sentry Plugin DIR url for enqueuing assets
 * @since 3.0.0
 */
defined( 'WP_SENTRY_PLUGIN_DIR_URL' ) || define( 'WP_SENTRY_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The composer autoload, absolute unix path.
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_AUTOLOAD_FILE' ) or define( 'WP_SENTRY_AUTOLOAD_FILE', dirname( WP_SENTRY_PLUGIN_FILE ) . '/vendor/autoload.php' );

/**
 * The plugin's config folder location.
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_CONFIG_DIR' ) or define( 'WP_SENTRY_CONFIG_DIR',  dirname( WP_SENTRY_PLUGIN_FILE ) . '//config/' );

/**
 * Define the sentry js script version to load in the browser
 * @since 3.0.0
 */
defined( 'WP_SENTRY_SCRIPT_VERSION' ) || define( 'WP_SENTRY_SCRIPT_VERSION', '4.6.6' );

/**
 * Re-map deprecated dsn constants to new constant values
 * if they exist.
 *
 * Note that Sentry no longer has "secret"
 * and "public" dsn. The dsn is unified and never includes
 * a secret, so now we are just differentiating between
 * "PHP" and "JS" dsn instead of referencing dsn scope in the name.
 *
 * @since 3.0.0
 */
if( defined( 'WP_SENTRY_DSN' ) && ! defined( 'WP_SENTRY_PHP_DSN' ) ){
  define( 'WP_SENTRY_PHP_DSN', WP_SENTRY_DSN );
}

if( defined( 'WP_SENTRY_PUBLIC_DSN' ) && ! defined( 'WP_SENTRY_JS_DSN' ) ){
  define( 'WP_SENTRY_JS_DSN', WP_SENTRY_PUBLIC_DSN );
}


/**
 * Run the plugin if:
 *
 * 1. A DSN is defined
 * 2. An instance of the abstract class TrackerBase does not already exist
 *
 * @since 3.0.0
 */
if( ! class_exists( '\WPSentry\Tracker\TrackerBase' ) ){
  run_wp_sentry();
}

/**
 * The function responsible for running the Wordpress Sentry Plugin
 *
 * @since 3.0.0
 */
function run_wp_sentry(){

  // Load dependencies
  require_once WP_SENTRY_AUTOLOAD_FILE;

  // Instantiate the Context provider
  $context_config = new \WPSentry\Config\Config( WP_SENTRY_CONFIG_DIR . 'context.php' );
  $context = new \WPSentry\Context\Context( $context_config );

  // Get init runtime configs
  $init_config = new \WPSentry\Config\Config( WP_SENTRY_CONFIG_DIR . 'init.php' );

  // Instantiate Sentry PHP Tracker if required DSN is defined
  if( defined( 'WP_SENTRY_PHP_DSN' ) ){

    $sentry_php = new \WPSentry\Tracker\PHP( WP_SENTRY_PHP_DSN, $init_config, $context );

  }

  // Instantiate Sentry JS Tracker if required DSN is defined
  if( defined( 'WP_SENTRY_JS_DSN' ) ){

    $sentry_js = new \WPSentry\Tracker\JS( WP_SENTRY_JS_DSN, $init_config, $context );

  }

}
