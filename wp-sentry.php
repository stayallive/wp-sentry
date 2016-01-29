<?php

/*
	Plugin Name: WP Sentry
	Plugin URI: http://git.alboweb.nl/devalex/wp-sentry
	Description: Send all exceptions & errors to an external tracker, tracks on the PHP and JavaScript side.
	Version: 1.0
	Author: Alex Bouma
	Author URI: https://alexbouma.me
	License: MIT
*/

// Define the base directory for easy access to any libs
define( 'WP_SENTRY_DIR', __DIR__ );

// Define the application version
define( 'WP_SENTRY_VERSION', ( defined( 'WP_SENTRY_VERSION' ) ) ? WP_SENTRY_VERION : wp_get_theme()->get( 'Version' ); );

// Load the PHP tracker if we have a private DSN
if ( defined( 'WP_SENTRY_DSN' ) && ! empty( WP_SENTRY_DSN ) ) {
	require_once __DIR__ . '/trackers/php/tracker.php';
}

// Load the JS tracker if we have a public DSN
if ( defined( 'WP_SENTRY_PUBLIC_DSN' ) || ! empty( WP_SENTRY_PUBLIC_DSN ) ) {
	require_once __DIR__ . '/trackers/js/tracker.php';
}
