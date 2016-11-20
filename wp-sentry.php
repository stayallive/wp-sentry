<?php

/*
	Plugin Name: WordPress Sentry
	Plugin URI: https://github.com/stayallive/wp-sentry
	Description: A (unofficial) WordPress plugin to report PHP errors and JavaScript errors to Sentry.
	Version: 1.0
	Author: Alex Bouma
	Author URI: https://alex.bouma.me
	License: MIT
*/

// Define the base directory for easy access to any libs
define( 'WP_SENTRY_DIR', __DIR__ );

// Define the application version
if ( ! defined( 'WP_SENTRY_VERSION' ) ) {
	define( 'WP_SENTRY_VERSION', wp_get_theme()->get( 'Version' ) );
}

// Load the PHP tracker if we have a private DSN
if ( defined( 'WP_SENTRY_DSN' ) && ! empty( WP_SENTRY_DSN ) ) {
	require_once __DIR__ . '/trackers/php/tracker.php';
}

// Load the JS tracker if we have a public DSN
if ( defined( 'WP_SENTRY_PUBLIC_DSN' ) || ! empty( WP_SENTRY_PUBLIC_DSN ) ) {
	require_once __DIR__ . '/trackers/js/tracker.php';
}
