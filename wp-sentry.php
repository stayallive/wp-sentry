<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: 3.4.6
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.dev
 * License: MIT
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// If the plugin was already loaded as a mu-plugin do not load again.
if ( defined( 'WP_SENTRY_MU_LOADED' ) ) {
	return;
}

// Make sure the PHP version is at least 7.1.
if ( ! defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID < 70100 ) {
	if ( is_admin() ) {
		function wp_sentry_php_version_notice() { ?>
            <div class="error below-h2">
                <p>
					<?php printf(
						'The WordPress Sentry plugin requires at least PHP 7.1. You have %s. WordPress Sentry will not be active unless this is resolved!',
						PHP_VERSION
					); ?>
                </p>
            </div>
		<?php }

		add_action( 'admin_notices', 'wp_sentry_php_version_notice' );
	}

	return;
}

// Resolve the sentry plugin file.
define( 'WP_SENTRY_PLUGIN_FILE', call_user_func( static function () {
	global $wp_plugin_paths;

	$plugin_file = __FILE__;

	if ( ! empty( $wp_plugin_paths ) ) {
		$wp_plugin_real_paths = array_flip( $wp_plugin_paths );
		$plugin_path          = wp_normalize_path( dirname( $plugin_file ) );

		if ( isset( $wp_plugin_real_paths[ $plugin_path ] ) ) {
			$plugin_file = str_replace( $plugin_path, $wp_plugin_real_paths[ $plugin_path ], $plugin_file );
		}
	}

	return $plugin_file;
} ) );

// Load dependencies
if ( ! class_exists( WP_Sentry_Version::class ) ) {
	$scopedAutoloader = __DIR__ . '/build/vendor/scoper-autoload.php';

	// If ther is a scoped autoloader we use that version, otherwise we use the normal autoloader
	require_once $scopedAutoloaderExists = file_exists( $scopedAutoloader )
		? $scopedAutoloader
		: __DIR__ . '/vendor/autoload.php';

	define( 'WP_SENTRY_SCOPED_AUTOLOADER', $scopedAutoloaderExists );
}

// Define the default version.
if ( ! defined( 'WP_SENTRY_VERSION' ) ) {
	define( 'WP_SENTRY_VERSION', wp_get_theme()->get( 'Version' ) ?: 'unknown' );
}

// Load the PHP tracker if we have a private DSN
if ( defined( 'WP_SENTRY_DSN' ) ) {
	$sentry_dsn = WP_SENTRY_DSN;

	if ( ! empty( $sentry_dsn ) ) {
		add_filter( 'wp_sentry_dsn', static function () {
			return WP_SENTRY_DSN;
		}, 1, 0 );

		WP_Sentry_Php_Tracker::get_instance();
	}
}

// Load the Javascript tracker if we have a public DSN
if ( defined( 'WP_SENTRY_PUBLIC_DSN' ) ) {
	$sentry_public_dsn = WP_SENTRY_PUBLIC_DSN;

	if ( ! empty( $sentry_public_dsn ) ) {
		add_filter( 'wp_sentry_public_dsn', static function () {
			return WP_SENTRY_PUBLIC_DSN;
		}, 1, 0 );

		WP_Sentry_Js_Tracker::get_instance();
	}
}

// Load the admin page when needed
if ( is_admin() ) {
	WP_Sentry_Admin_Page::get_instance();
}

// Register a "safe" function to call Sentry functions safer in your own code,
// the callback only executed if a DSN was set and thus the client is able to sent events.
//
// Usage:
// if ( function_exists( 'wp_sentry_safe' ) ) {
//     wp_sentry_safe( function ( \Sentry\State\HubInterface $client ) {
//         $client->captureMessage( 'This is a test message!', \Sentry\Severity::debug() );
//     } );
// }
if ( ! function_exists( 'wp_sentry_safe' ) ) {

	/**
	 * Call the callback with the Sentry client, or not at all if there is no client.
	 *
	 * @param callable $callback
	 */
	function wp_sentry_safe( callable $callback ) {
		if ( class_exists( 'WP_Sentry_Php_Tracker' ) ) {
			$tracker = WP_Sentry_Php_Tracker::get_instance();

			if ( ! empty( $tracker->get_dsn() ) ) {
				$callback( $tracker->get_client() );
			}
		}
	}

}
