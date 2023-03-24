<?php

/**
 * WordPress Sentry JavaScript Tracker.
 */
final class WP_Sentry_Js_Tracker {
	use WP_Sentry_Resolve_User, WP_Sentry_Resolve_Environment;

	/**
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Js_Tracker
	 */
	private static $instance;

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return WP_Sentry_Js_Tracker
	 */
	public static function get_instance(): self {
		return self::$instance ?: self::$instance = new self;
	}

	/**
	 * Class constructor.
	 */
	protected function __construct() {
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
	public function get_dsn(): ?string {
		$dsn = defined( 'WP_SENTRY_BROWSER_DSN' ) ? WP_SENTRY_BROWSER_DSN : null;

		if ( $dsn === null ) {
			$dsn = defined( 'WP_SENTRY_PUBLIC_DSN' ) ? WP_SENTRY_PUBLIC_DSN : null;
		}

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
	public function get_options(): array {
		$context = $this->get_default_context();

		if ( has_filter( 'wp_sentry_public_context' ) ) {
			$context = (array) apply_filters( 'wp_sentry_public_context', $context );
		}

		foreach ( $context as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $context[ $key ] );
			}
		}

		$options = $this->get_default_options();

		$options['context'] = $context;

		if ( has_filter( 'wp_sentry_public_options' ) ) {
			$options = (array) apply_filters( 'wp_sentry_public_options', $options );
		}

		return $options;
	}

	/**
	 * Get sentry default options.
	 *
	 * @return array
	 */
	public function get_default_options(): array {
		$options = [
			'environment' => $this->get_environment(),
		];

		if ( defined( 'WP_SENTRY_VERSION' ) ) {
			$options['release'] = WP_SENTRY_VERSION;
		}

		return $options;
	}

	/**
	 * Get sentry default context.
	 *
	 * @return array
	 */
	public function get_default_context(): array {
		$context = [
			'tags'  => [
				'wordpress' => get_bloginfo( 'version' ),
				'language'  => get_bloginfo( 'language' ),
			],
			'extra' => [],
		];

		if ( defined( 'WP_SENTRY_SEND_DEFAULT_PII' ) && WP_SENTRY_SEND_DEFAULT_PII ) {
			$context['user'] = $this->get_current_user_info();
		}

		return $context;
	}

	/**
	 * Target of set_current_user action.
	 *
	 * @access private
	 */
	public function on_enqueue_scripts(): void {
		$options  = [];
		$features = [ 'browser' ];

		$traces_sample_rate = defined( 'WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE
			: 0.0;

		if ( $traces_sample_rate > 0 ) {
			$options['wpBrowserTracingOptions'] = defined( 'WP_SENTRY_BROWSER_TRACING_OPTIONS' ) ? WP_SENTRY_BROWSER_TRACING_OPTIONS : new stdClass;

			$options['tracesSampleRate'] = $traces_sample_rate;

			$features[] = 'tracing';
		}

		$replays_session_sample_rate = defined( 'WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE
			: 0.0;

		$replays_on_error_sample_rate = defined( 'WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE
			: 0.0;

		$use_es5_bundles = defined( 'WP_SENTRY_BROWSER_USE_ES5_BUNDLES' ) && WP_SENTRY_BROWSER_USE_ES5_BUNDLES;

		if ( ! $use_es5_bundles && ( $replays_session_sample_rate > 0 || $replays_on_error_sample_rate > 0 ) ) {
			$options['wpSessionReplayOptions'] = defined( 'WP_SENTRY_BROWSER_SESSION_REPLAY_OPTIONS' ) ? WP_SENTRY_BROWSER_SESSION_REPLAY_OPTIONS : new stdClass;

			$options['replaysSessionSampleRate'] = $replays_session_sample_rate;
			$options['replaysOnErrorSampleRate'] = $replays_on_error_sample_rate;

			$features[] = 'replay';
		}

		if ( defined( 'WP_SENTRY_BROWSER_USE_ES5_BUNDLES' ) && WP_SENTRY_BROWSER_USE_ES5_BUNDLES ) {
			wp_enqueue_script(
				'wp-sentry-polyfill',
				'https://polyfill.io/v3/polyfill.min.js?features=Promise%2CObject.assign%2CNumber.isNaN%2CArray.prototype.includes%2CString.prototype.startsWith',
				[],
				WP_Sentry_Version::SDK_VERSION
			);

			wp_enqueue_script(
				'wp-sentry-browser',
				$traces_sample_rate > 0
					? plugin_dir_url( WP_SENTRY_PLUGIN_FILE ) . 'public/wp-sentry-browser-tracing.es5.min.js'
					: plugin_dir_url( WP_SENTRY_PLUGIN_FILE ) . 'public/wp-sentry-browser.es5.min.js',
				[ 'wp-sentry-polyfill' ],
				WP_Sentry_Version::SDK_VERSION
			);
		} else {
			$featuresString = implode( '-', $features );

			wp_enqueue_script(
				'wp-sentry-browser',
				plugin_dir_url( WP_SENTRY_PLUGIN_FILE ) . "public/wp-sentry-{$featuresString}.min.js",
				[],
				WP_Sentry_Version::SDK_VERSION
			);
		}

		wp_localize_script(
			'wp-sentry-browser',
			'wp_sentry',
			array_merge( $options, $this->get_options(), [
				'dsn' => $this->get_dsn(),
			] )
		);
	}
}
