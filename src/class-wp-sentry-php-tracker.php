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
	use WP_Sentry_Resolve_User;

	/**
	 * Holds an instance to the sentry client.
	 *
	 * @var \Sentry\ClientInterface
	 */
	protected $client;

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
		return self::$instance ?: self::$instance = new self();
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
		$dsn = defined( 'WP_SENTRY_DSN' ) ? WP_SENTRY_DSN : null;

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
		return [
			'wordpress' => get_bloginfo( 'version' ),
			'language'  => get_bloginfo( 'language' ),
			'php'       => phpversion(),
		];
	}

	/**
	 * Get the default options.
	 *
	 * @return array
	 */
	public function get_default_options(): array {
		$options = [
			'dsn'              => $this->get_dsn(),
			'release'          => WP_SENTRY_VERSION,
			'environment'      => defined( 'WP_SENTRY_ENV' ) ? WP_SENTRY_ENV : 'unspecified',
			'send_default_pii' => defined( 'WP_SENTRY_SEND_DEFAULT_PII' ) ? WP_SENTRY_SEND_DEFAULT_PII : false,
		];

		if ( defined( 'WP_SENTRY_ERROR_TYPES' ) ) {
			$options['error_types'] = WP_SENTRY_ERROR_TYPES;
		}

		$options['project_root'] = defined( 'WP_SENTRY_PROJECT_ROOT' )
			? WP_SENTRY_PROJECT_ROOT
			: ABSPATH;

		return $options;
	}

	/**
	 * Initialize the Sentry client and register it with the Hub.
	 */
	private function initializeClient(): void {
		$clientBuilder = ClientBuilder::create( $this->get_default_options() );

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
