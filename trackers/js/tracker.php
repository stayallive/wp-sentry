<?php

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_script(
		'wp-sentry-raven',
		plugin_dir_url( WP_SENTRY_DIR . '/wp-sentry.php' ) . 'raven/js/raven-3.7.0.min.js',
		[
			'jquery',
		],
		'3.7.0',
		false
	);

	wp_localize_script(
		'wp-sentry-raven',
		'wp_sentry',
		[
			'dsn'     => WP_SENTRY_PUBLIC_DSN,
			'release' => WP_SENTRY_VERSION,
		]
	);
} );
