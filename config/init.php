<?php
/**
 * Default runtime configurations for WPSentry passed in upon init.
 */
return [

  'dsn'			    => '',
	'release'		  => defined( 'WP_SENTRY_VERSION' ) ? WP_SENTRY_VERSION : wp_get_theme()->get( 'Version' ),
	'environment'	=> defined( 'WP_SENTRY_ENV' ) ? WP_SENTRY_ENV : 'unspecified',

];
