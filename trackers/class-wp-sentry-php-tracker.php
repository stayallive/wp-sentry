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
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Php_Tracker
	 */
	private static $instance = null;

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return WP_Sentry_Php_Tracker
	 */
	public static function get_instance() {
		return self::$instance ?: self::$instance = new self();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function bootstrap() {
		// Require the Raven PHP autoloader
		require_once plugin_dir_path( WP_SENTRY_PLUGIN_FILE ) . 'raven/php/Raven/Autoloader.php';

		// Register the autoloader.
		Raven_Autoloader::register();

		// Instantiate the client and install.
		$this->get_client()->install()->setSendCallback( [ $this, 'on_send_data' ] );
	}

	/**
	 * Execute login on client send data.
	 *
	 * @access private
	 *
	 * @param array $data A reference to the data being sent.
	 *
	 * @return bool True to send data; Otherwise false.
	 */
	public function on_send_data( array &$data ) {
		if ( has_filter( 'wp_sentry_send_data' ) ) {
			$filtered = apply_filters( 'wp_sentry_send_data', $data );

			if ( is_array( $filtered ) ) {
				$data = array_merge( $data, $filtered );
			} else {
				return (bool) $filtered;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_dsn( $dsn ) {
		parent::set_dsn( $dsn );

		if ( is_string( $dsn ) ) {
			// Update Raven client
			$options = Raven_Client::parseDSN( $dsn );
			$client  = $this->get_client();

			foreach ( $options as $key => $value ) {
				$client->$key = $value;
			}
		}
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
				'wordpress' => get_bloginfo( 'version' ),
				'language'  => get_bloginfo( 'language' ),
				'php'       => phpversion(),
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
