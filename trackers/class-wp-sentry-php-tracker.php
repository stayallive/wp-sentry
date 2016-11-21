<?php

require_once __DIR__ . '/class-wp-sentry-tracker-base.php';

/**
 * WordPress Sentry PHP Tracker.
 */
final class WP_Sentry_Php_Tracker extends WP_Sentry_Tracker_Base {

	/**
	 * Holds an instance to the sentry client.
	 *
	 * @var Raven_Client
	 */
	protected $client;

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return WP_Sentry_Php_Tracker
	 */
	public static function get_instance() {
		static $instance = null;
		return $instance ?: $instance = new self( WP_SENTRY_DSN );
	}

	/**
	 * Class constructor.
	 *
	 * @param string $dsn    The sentry server dsn.
	 * @param array $options Optional. The sentry client options to use.
	 */
	protected function __construct( $dsn, array $options = [] ) {
		parent::__construct( $dsn, $options );

		// Require the Raven PHP autoloader
		require_once plugin_dir_path( WP_SENTRY_PLUGIN_FILE ) . 'raven/php/Raven/Autoloader.php';

		// Register the autoloader.
		Raven_Autoloader::register();

		// Instantiate the client and install.
		$this->get_client()->install();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_dsn() {

		$dsn = parent::get_dsn();

		if ( has_filter( 'wp_sentry_dsn' ) ) {
			$dsn = (string) apply_filters( 'wp_sentry_dsn', $dsn );
		}

		return $dsn;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_options() {

		$options = parent::get_options();

		if ( has_filter( 'wp_sentry_options' ) ) {
			$options = (array) apply_filters( 'wp_sentry_options', $options );
		}

		return $options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_default_options() {
		return [
			'release'     => WP_SENTRY_VERSION,
			'environment' => defined( 'WP_SENTRY_ENV' ) ? WP_SENTRY_ENV : 'unspecified',
			'tags'        => [
				'wordpress'   => get_bloginfo( 'version' ),
				'language'    => get_bloginfo( 'language' ),
				'php'         => phpversion(),
			],
		];
	}

	/**
	 * Get the sentry client.
	 *
	 * @return Raven_Client
	 */
	public function get_client() {
		return $this->client ?: $this->client = new Raven_Client(
			$this->get_dsn(),
			$this->get_options()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_context() {
		return (array) $this->get_client()->context;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_user_context( array $data ) {
		$this->get_client()->user_context( $data );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_context() {
		return $this->get_context()['user'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_tags_context( array $data ) {
		$this->get_client()->tags_context( $data );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_tags_context() {
		return $this->get_context()['tags'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_extra_context( array $data ) {
		$this->get_client()->extra_context( $data );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_extra_context() {
		return $this->get_context()['extra'];
	}
}
