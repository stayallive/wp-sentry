<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: 2.4.0
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.me
 * License: MIT
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// If the plugin was already loaded as a mu-plugin do not load again.
if ( defined( 'WP_SENTRY_MU_LOADED' ) ) {
	return;
}

// Resolve the sentry plugin file.
define( 'WP_SENTRY_PLUGIN_FILE', call_user_func( function () {
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
if ( ! class_exists( 'WP_Sentry_Tracker_Base' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

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
