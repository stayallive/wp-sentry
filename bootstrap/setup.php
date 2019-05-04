<?php
namespace WPSentry;

/**
 * @package WPSentry/bootstrap
 */

// Exit if plugin isn't running
defined( 'WP_SENTRY_EXISTS' ) || exit;

(function() {

  // Load dependencies
  require_once WP_SENTRY_AUTOLOAD_FILE;

  // Instantiate the Context provider
  $context_config = new \WPSentry\Config\Config( WP_SENTRY_CONFIG_DIR . 'context.php' );
  $context = new \WPSentry\Context\Context( $context_config );

  // Get init runtime configs
  $init_config = new \WPSentry\Config\Config( WP_SENTRY_CONFIG_DIR . 'init.php' );

  // Instantiate Sentry PHP Tracker if required DSN is defined
  if( defined( 'WP_SENTRY_PHP_DSN' ) ){
    $sentry_php = new \WPSentry\Tracker\PHP( WP_SENTRY_PHP_DSN, $init_config, $context );
  }

  // Instantiate Sentry JS Tracker if required DSN is defined
  if( defined( 'WP_SENTRY_JS_DSN' ) ){
    $sentry_js = new \WPSentry\Tracker\JS( WP_SENTRY_JS_DSN, $init_config, $context );
  }

})();
