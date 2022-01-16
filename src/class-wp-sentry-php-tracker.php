<?php

use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Sentry\ClientBuilder;
use Sentry\State\HubInterface;

/**
 * WordPress Sentry PHP Tracker.
 */
final class WP_Sentry_Php_Tracker {
	use WP_Sentry_Resolve_User, WP_Sentry_Resolve_Environment;

	/**
	 * Holds an instance to the Sentry client.
	 *
	 * @var \Sentry\ClientInterface
	 */
	protected $client;

	/**
	 * Holds the last DSN which was used to initialize the Sentry client.
	 *
	 * @var string
	 */
	protected $dsn;

	/**
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Php_Tracker
	 */
	private static $instance;

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return \WP_Sentry_Php_Tracker
	 */
	public static function get_instance(): WP_Sentry_Php_Tracker {
		return self::$instance ?: self::$instance = new self;
	}

	/**
	 * WP_Sentry_Php_Tracker constructor.
	 */
	protected function __construct() {
		if ( defined( 'WP_SENTRY_SEND_DEFAULT_PII' ) && WP_SENTRY_SEND_DEFAULT_PII ) {
			add_action( 'set_current_user', [ $this, 'on_set_current_user' ] );
		}

		add_action( 'after_setup_theme', [ $this, 'on_after_setup_theme' ] );

		// Force the initialization of the client immediately
		$this->get_client();

		add_action( 'init', [ $this, 'on_init' ] );
	}

	public function on_init(): void {
		if ( $this->client === null ) {
			return;
		}

		$hub = SentrySdk::getCurrentHub();

		$hub->configureScope( function ( Scope $scope ) {
			foreach ( $this->get_default_tags() as $tag => $value ) {
				$scope->setTag( $tag, $value );
			}
		} );

		SentrySdk::setCurrentHub( $hub );
	}

	/**
	 * Handle the `set_current_user` WP action.
	 */
	public function on_set_current_user(): void {
		$this->get_client()->configureScope( function ( Scope $scope ) {
			$scope->setUser( $this->get_current_user_info() );
		} );
	}

	/**
	 * Handle the `after_setup_theme` WP action.
	 */
	public function on_after_setup_theme(): void {
		// If the DSN potentially has changed, re-initialize the client
		if ( has_filter( 'wp_sentry_dsn' ) ) {
			$this->initializeClient();
		}

		// Apply the filter to config the scope
		if ( has_filter( 'wp_sentry_scope' ) ) {
			$this->get_client()->configureScope( function ( Scope $scope ) {
				apply_filters( 'wp_sentry_scope', $scope );
			} );
		}

		// Apply the filter to configure any options
		if ( has_filter( 'wp_sentry_options' ) ) {
			apply_filters( 'wp_sentry_options', $this->get_client()->getClient()->getOptions() );
		}
	}

	/**
	 * Retrieve the DSN.
	 *
	 * @return string|null
	 */
	public function get_dsn(): ?string {
		$dsn = defined( 'WP_SENTRY_PHP_DSN' ) ? WP_SENTRY_PHP_DSN : null;

		if ( $dsn === null ) {
			$dsn = defined( 'WP_SENTRY_DSN' ) ? WP_SENTRY_DSN : null;
		}

		if ( has_filter( 'wp_sentry_dsn' ) ) {
			$dsn = (string) apply_filters( 'wp_sentry_dsn', $dsn );
		}

		return $dsn;
	}

	/**
	 * Get the sentry client.
	 *
	 * @return \Sentry\State\HubInterface
	 */
	public function get_client(): HubInterface {
		if ( $this->client === null && $this->get_dsn() !== null ) {
			$this->initializeClient();
		}

		return SentrySdk::getCurrentHub();
	}

	/**
	 * Get the default tags.
	 *
	 * @return array
	 */
	public function get_default_tags(): array {
		require WP_SENTRY_WPINC . '/version.php';

		/** @noinspection IssetArgumentExistenceInspection */
		$tags = [
			'wordpress' => $wp_version ?? 'unknown',
		];

		if ( function_exists( 'get_bloginfo' ) ) {
			$tags['language'] = get_bloginfo( 'language' );
		}

		return $tags;
	}

	/**
	 * Get the default options.
	 *
	 * @return array
	 */
	public function get_default_options(): array {
		$options = [
			'dsn'              => $this->get_dsn(),
			'prefixes'         => [ ABSPATH ],
			'environment'      => $this->get_environment(),
			'send_default_pii' => defined( 'WP_SENTRY_SEND_DEFAULT_PII' ) && WP_SENTRY_SEND_DEFAULT_PII,
		];

		if ( defined( 'WP_SENTRY_VERSION' ) ) {
			$options['release'] = WP_SENTRY_VERSION;
		}

		if ( defined( 'WP_SENTRY_ERROR_TYPES' ) ) {
			$options['error_types'] = WP_SENTRY_ERROR_TYPES;
		}

		$options['in_app_exclude'] = [
			WP_SENTRY_WPADMIN, // <base>/wp-admin
			WP_SENTRY_WPINC,   // <base>/wp-includes
		];

		return $options;
	}

	/**
	 * Initialize the Sentry client and register it with the Hub.
	 */
	private function initializeClient(): void {
		$dsn = $this->get_dsn();

		// Do not re-initialize the client when the DSN has not changed
		if ( $this->client !== null && $this->dsn === $dsn ) {
			return;
		}

		$this->dsn = $this->get_dsn();

		$clientBuilder = ClientBuilder::create( $this->get_default_options() );

		if ( defined( 'WP_SENTRY_CLIENTBUILDER_CALLBACK' ) && is_callable( WP_SENTRY_CLIENTBUILDER_CALLBACK ) ) {
			call_user_func( WP_SENTRY_CLIENTBUILDER_CALLBACK, $clientBuilder );
		}

		$clientBuilder->setSdkIdentifier( WP_Sentry_Version::SDK_IDENTIFIER );
		$clientBuilder->setSdkVersion( WP_Sentry_Version::SDK_VERSION );

		$hub = new Hub( $this->client = $clientBuilder->getClient() );

		$hub->configureScope( function ( Scope $scope ) {
			foreach ( $this->get_default_tags() as $tag => $value ) {
				$scope->setTag( $tag, $value );
			}
		} );

		SentrySdk::setCurrentHub( $hub );
	}
}
