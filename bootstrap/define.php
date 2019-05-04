<?php
/**
 * @package WPSentry/Bootstrap
 */
defined( 'ABSPATH' ) || die;

/**
 * Tells the world the plugin is present and ready to be used.
 *
 * This also gives us an avenue to easily prevent the plugin from loading
 * twice without the user having to set a constant
 * (if the plugin was loaded as a mu-plugin for instance).
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_PRESENT', true );

/**
 * Define a Text Domain for WP Sentry.
 *
 * @since 3.0.0
 */
defined( 'WP_SENTRY_TEXT_DOMAIN' ) || define( 'WP_SENTRY_TEXT_DOMAIN', 'wp-sentry' );

/**
 * Resolve the Sentry Plugin DIR url for enqueuing assets
 *
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
 *
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
