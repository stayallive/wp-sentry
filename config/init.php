<?php
/**
 * Default runtime configurations for WPSentry passed in upon init.
 *
 * @package WPSentry/config
 * @link https://docs.sentry.io/error-reporting/configuration/?platform=php
 * @since 3.0.0
 */

return [

  'dsn'			    => '',
	'release'		  => defined( 'WP_SENTRY_VERSION' ) ? WP_SENTRY_VERSION : wp_get_theme()->get( 'Version' ),
	'environment'	=> defined( 'WP_SENTRY_ENV' ) ? WP_SENTRY_ENV : 'unspecified',

];
