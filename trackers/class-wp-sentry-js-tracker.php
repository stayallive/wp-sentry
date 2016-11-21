<?php

require_once __DIR__ . '/class-wp-sentry-tracker-base.php';

/**
 * WordPress Sentry Javascript Tracker.
 */
final class WP_Sentry_Js_Tracker extends WP_Sentry_Tracker_Base {

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return WP_Sentry_Js_Tracker
	 */
	public static function get_instance() {
		static $instance = null;
		return $instance ?: $instance = new self( WP_SENTRY_PUBLIC_DSN );
	}

	/**
	 * Register WordPress hooks.
	 */
	protected function register_hooks() {

		// Call parent method.
		parent::register_hooks();

		// Register on front-end using the highest priority.
		add_action( 'wp_enqueue_scripts', [ $this, 'on_enqueue_scripts' ], 0, 1 );

		// Register on admin using the highest priority.
		add_action( 'admin_enqueue_scripts', [ $this, 'on_enqueue_scripts' ], 0, 1 );

		// Register on login using the highest priority.
		add_action( 'login_enqueue_scripts', [ $this, 'on_enqueue_scripts' ], 0, 1 );
	}

	/**
	 * Get sentry dsn.
	 *
	 * @return string
	 */
	public function get_dsn() {

		$dsn = parent::get_dsn();

		if ( has_filter( 'wp_sentry_public_dsn' ) ) {
			$dsn = (string) apply_filters( 'wp_sentry_public_dsn', $dsn );
		}

		return $dsn;
	}

	/**
	 * Get sentry options.
	 *
	 * @return array
	 */
	public function get_options() {

		$options = parent::get_options();

		if ( has_filter( 'wp_sentry_public_options' ) ) {
			$options = (array) apply_filters( 'wp_sentry_public_options', $options );
		}

		return $options;
	}

	/**
	 * Get sentry default options.
	 * @return array
	 */
	public function get_default_options() {
		return [
			'release'     => WP_SENTRY_VERSION,
			'environment' => defined( 'WP_SENTRY_ENV' ) ? WP_SENTRY_ENV : 'unspecified',
			'tags'        => [
				'wordpress' => get_bloginfo( 'version' ),
				'language'  => get_bloginfo( 'language' ),
			],
		];
	}

	/**
	 * Target of set_current_user action.
	 *
	 * @access private
	 */
	public function on_enqueue_scripts() {

		wp_enqueue_script(
			'wp-sentry-raven',
			plugin_dir_url( WP_SENTRY_PLUGIN_FILE ) . 'raven/js/raven-3.8.0.min.js',
			[ 'jquery' ],
			'3.8.0',
			false
		);

		// Cleanup context for JS.
		$context = $this->get_context();
		foreach ( $context as $key => $value ) {
			if ( empty( $context[ $key ] ) ) {
				unset( $context[ $key ] );
			}
		}

		wp_localize_script(
			'wp-sentry-raven',
			'wp_sentry',
			[
				'dsn' => $this->get_dsn(),
				'options' => $this->get_options(),
				'context' => $context,
			]
		);
	}
}