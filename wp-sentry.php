<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: 2.8.0
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

// Load dependencies
if ( ! class_exists( 'WP_Sentry_Tracker_Base' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}
/**
 * The composer autoload, absolute unix path.
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_AUTOLOAD_FILE' ) or define( 'WP_SENTRY_AUTOLOAD_FILE', dirname( WP_SENTRY_PLUGIN_FILE ) . '/vendor/autoload.php' );

// Define the sentry version.
if ( ! defined( 'WP_SENTRY_VERSION' ) ) {
	define( 'WP_SENTRY_VERSION', wp_get_theme()->get( 'Version' ) );
}

// Load the PHP tracker if we have a private DSN
if ( defined( 'WP_SENTRY_DSN' ) ) {
	$sentry_dsn = WP_SENTRY_DSN;

	if ( ! empty( $sentry_dsn ) ) {
		add_filter( 'wp_sentry_dsn', function () {
			return WP_SENTRY_DSN;
		}, 1, 0 );

		WP_Sentry_Php_Tracker::get_instance();
	}
}

// Load the Javascript tracker if we have a public DSN
if ( defined( 'WP_SENTRY_PUBLIC_DSN' ) ) {
	$sentry_public_dsn = WP_SENTRY_PUBLIC_DSN;

	if ( ! empty( $sentry_public_dsn ) ) {
		add_filter( 'wp_sentry_public_dsn', function () {
			return WP_SENTRY_PUBLIC_DSN;
		}, 1, 0 );

		WP_Sentry_Js_Tracker::get_instance();
	}
}
