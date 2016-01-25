<?php

// Require the Raven PHP autoloader
require_once WP_SENTRY_DIR . '/raven/php/Raven/Autoloader.php';

// Register the autoloader
Raven_Autoloader::register();

// Require the custom WP Raven Client
require_once __DIR__ . '/wp-raven-client.php';

/**
 * Wraps around the Raven client to make it a singleton.
 */
class WP_Sentry_Raven_Wrapper {

	/**
	 * The Sentry Raven wrapper instance.
	 *
	 * @var WP_Sentry_Raven_Wrapper
	 */
	protected static $instance = null;

	/**
	 * Hook with WordPress.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', function () {
			self::getInstance();
		} );
	}

	/**
	 * Create an instance of WP Raven Client.
	 *
	 * @return WP_Raven_Client
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new WP_Raven_Client;
		}

		return self::$instance;
	}

}

new WP_Sentry_Raven_Wrapper;
