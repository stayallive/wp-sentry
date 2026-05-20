<?php

/**
 * Plugin Name:       Sentry for WordPress
 * Plugin URI:        https://github.com/stayallive/wp-sentry
 * Description:       A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version:           8.10.0
 * Requires at least: 4.4
 * Requires PHP:      7.2.5
 * Author:            Alex Bouma
 * Author URI:        https://alex.bouma.dev
 * License:           MIT
 * License URI:       https://github.com/stayallive/wp-sentry/blob/v8.10.0/LICENSE.md
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// If the plugin was already loaded as a mu-plugin or from somewhere else do not load again
if ( defined( 'WP_SENTRY_MU_LOADED' ) || defined( 'WP_SENTRY_LOADED' ) ) {
	return;
}

define( 'WP_SENTRY_LOADED', true );

define( 'WP_SENTRY_WPINC', ABSPATH . ( defined( 'WPINC' ) ? WPINC : 'wp-includes' ) );
define( 'WP_SENTRY_WPADMIN', ABSPATH . 'wp-admin' );

// Load the WordPress plugin API early so hooks can be used even if Sentry is loaded before WordPress
if ( ! function_exists( 'add_action' ) ) {
	require_once WP_SENTRY_WPINC . '/plugin.php';
}

// Make sure the PHP version is at least 7.2
if ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < 70200 ) {
	function wp_sentry_php_version_notice() { ?>
		<div class="error below-h2">
			<p>
				<?php printf(
					'The Sentry for WordPress plugin requires at least PHP 7.2. You have %s. Sentry for WordPress will not be active unless this is resolved!',
					PHP_VERSION
				); ?>
			</p>
		</div>
	<?php }

	add_action( 'admin_notices', 'wp_sentry_php_version_notice' );

	return;
}

// Resolve the sentry plugin file
if ( ! defined( 'WP_SENTRY_PLUGIN_FILE' ) ) {
	define( 'WP_SENTRY_PLUGIN_FILE', call_user_func( static function () {
		global $wp_plugin_paths;

		$plugin_file      = __FILE__;
		$plugin_real_file = realpath( $plugin_file ) ?: $plugin_file;

		$normalize_path = static function ( string $path ): string {
			if ( function_exists( 'wp_normalize_path' ) ) {
				return wp_normalize_path( $path );
			}

			$path = str_replace( '\\', '/', $path );
			$path = preg_replace( '|(?<=.)/+|', '/', $path );

			if ( ':' === substr( $path, 1, 1 ) ) {
				$path = ucfirst( $path );
			}

			return $path;
		};

		$plugin_real_dir = $normalize_path( dirname( $plugin_real_file ) );

		if ( ! empty( $wp_plugin_paths ) ) {
			$wp_plugin_real_paths = array_flip( $wp_plugin_paths );

			if ( isset( $wp_plugin_real_paths[ $plugin_real_dir ] ) ) {
				return str_replace( $plugin_real_dir, $wp_plugin_real_paths[ $plugin_real_dir ], $plugin_real_file );
			}
		}

		$plugin_dirs = [];

		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			$plugin_dirs[] = WP_PLUGIN_DIR;
		}

		if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
			$plugin_dirs[] = WPMU_PLUGIN_DIR;
		}

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$plugin_dirs[] = WP_CONTENT_DIR . '/plugins';
			$plugin_dirs[] = WP_CONTENT_DIR . '/mu-plugins';
		}

		if ( defined( 'ABSPATH' ) ) {
			$plugin_dirs[] = ABSPATH . 'wp-content/plugins';
			$plugin_dirs[] = ABSPATH . 'wp-content/mu-plugins';
		}

		$plugin_dirs = array_unique( array_filter( array_map( $normalize_path, $plugin_dirs ) ) );

		foreach ( $plugin_dirs as $plugin_dir ) {
			if ( 0 === strpos( $plugin_real_dir . '/', rtrim( $plugin_dir, '/' ) . '/' ) ) {
				return $plugin_file;
			}
		}

		foreach ( $plugin_dirs as $plugin_dir ) {
			foreach ( [ $plugin_dir . '/' . basename( $plugin_file ), $plugin_dir . '/*/' . basename( $plugin_file ) ] as $pattern ) {
				foreach ( glob( $pattern ) ?: [] as $plugin_candidate ) {
					$plugin_candidate_real_file = realpath( $plugin_candidate );

					if (
						false === $plugin_candidate_real_file
						|| $normalize_path( $plugin_candidate_real_file ) !== $normalize_path( $plugin_real_file )
					) {
						continue;
					}

					if (
						function_exists( 'wp_register_plugin_realpath' )
						&& function_exists( 'wp_normalize_path' )
						&& defined( 'WP_PLUGIN_DIR' )
						&& defined( 'WPMU_PLUGIN_DIR' )
					) {
						wp_register_plugin_realpath( $plugin_candidate );
					}

					return $plugin_candidate;
				}
			}
		}

		return $plugin_file;
	} ) );
}

// Load dependencies
if ( ! class_exists( WP_Sentry_Version::class ) ) {
	$scopedAutoloaderExists = file_exists(
		$scopedAutoloaderPath = __DIR__ . '/build/vendor/scoper-autoload.php'
	);

	// If there is a scoped autoloader we use that version, otherwise we use the normal autoloader
	require_once $scopedAutoloaderExists
		? $scopedAutoloaderPath
		: __DIR__ . '/vendor/autoload.php';

	define( 'WP_SENTRY_SCOPED_AUTOLOADER', $scopedAutoloaderExists );
}

// Define the default version if none was set
if ( ! defined( 'WP_SENTRY_VERSION' ) && function_exists( 'add_action' ) ) {
	// We need to wait until the theme is loaded and setup to get the version
	add_action( 'after_setup_theme', function () {
		// It makes no sense to set a version based on the theme version if the plugin is enabled for the network since every site can have a different theme
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( __DIR__ ) ) {
			return;
		}

		// The JS client has not been initialized yet so we can just set the `WP_SENTRY_VERSION` constant
		define( 'WP_SENTRY_VERSION', wp_get_theme()->get( 'Version' ) ?: 'unknown' );

		// The PHP client has probably already been initialized so we need to update the release on the options directly
		add_filter( 'wp_sentry_options', static function ( Sentry\Options $options ) {
			$options->setRelease( WP_SENTRY_VERSION );

			return $options;
		} );
	}, /* priority: */ 1 );
}

// Load the PHP tracker if we have a PHP DSN
if ( defined( 'WP_SENTRY_PHP_DSN' ) || defined( 'WP_SENTRY_DSN' ) || defined( 'WP_SENTRY_SPOTLIGHT' ) ) {
	$sentry_php_tracker_dsn = defined( 'WP_SENTRY_PHP_DSN' )
		? WP_SENTRY_PHP_DSN
		: null;

	if ( $sentry_php_tracker_dsn === null ) {
		$sentry_php_tracker_dsn = defined( 'WP_SENTRY_DSN' )
			? WP_SENTRY_DSN
			: null;
	}

	if ( ! empty( $sentry_php_tracker_dsn ) || WP_Sentry_Php_Tracker::get_spotlight_enabled() ) {
		// Error tracker and Sentry SDK bootstrap
		WP_Sentry_Php_Tracker::get_instance();

		// Performance tracker
		WP_Sentry_Php_Tracing::get_instance();

		// Action Scheduler integration
		WP_Sentry_Action_Scheduler_Integration::get_instance();

		// Redis Object Cache integration
		WP_Sentry_Redis_Object_Cache_Integration::get_instance();
	}
}

// Load the JavaScript tracker if we have a browser/public DSN
if ( defined( 'WP_SENTRY_BROWSER_DSN' ) || defined( 'WP_SENTRY_PUBLIC_DSN' ) ) {
	$sentry_js_tracker_dsn = defined( 'WP_SENTRY_BROWSER_DSN' )
		? WP_SENTRY_BROWSER_DSN
		: WP_SENTRY_PUBLIC_DSN;

	if ( ! empty( $sentry_js_tracker_dsn ) ) {
		WP_Sentry_Js_Tracker::get_instance();
	}
}

// Load the admin page
WP_Sentry_Admin_Page::get_instance();

/**
 * Register a "safe" function to call Sentry functions safer in your own code,
 * the callback only executed if a DSN was set and thus the client is able to sent events.
 *
 * Usage:
 * if ( function_exists( 'wp_sentry_safe' ) ) {
 *     wp_sentry_safe( function ( \Sentry\State\HubInterface $client ) {
 *         $client->captureMessage( 'This is a test message!', \Sentry\Severity::debug() );
 *     } );
 * }
 */
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
