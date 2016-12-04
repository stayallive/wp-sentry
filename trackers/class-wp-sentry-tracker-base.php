<?php

/**
 * WordPress Sentry Tracker Base class.
 */
abstract class WP_Sentry_Tracker_Base {

	/**
	 * Holds the sentry dsn.
	 *
	 * @var string
	 */
	private $dsn = '';

	/**
	 * Holds the sentry options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Holds the sentry context.
	 *
	 * @var array
	 */
	private $context = [
		'user'  => null,
		'tags'  => [],
		'extra' => [],
	];

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		// Set the default options.
		$this->set_options( $this->get_default_options() );

		// Set the current user when available.
		add_action( 'set_current_user', [ $this, 'on_set_current_user' ] );

		// Bootstrap the tracker
		$this->bootstrap();
	}

	/**
	 * Bootstrap the tracker.
	 */
	protected abstract function bootstrap();

	/**
	 * Target of set_current_user action.
	 *
	 * @access private
	 */
	public function on_set_current_user() {
		$current_user = wp_get_current_user();

		// Default user context to anonymous.
		$user_context = [
			'id'   => 0,
			'name' => 'anonymous',
		];

		// Determine whether the user is logged in assign their details.
		if ( $current_user instanceof WP_User ) {
			if ( $current_user->exists() ) {
				$user_context = [
					'id'       => $current_user->ID,
					'name'     => $current_user->display_name,
					'email'    => $current_user->user_email,
					'username' => $current_user->user_login,
				];
			}
		}

		// Filter the user context so that plugins that manage users on their own
		// can provide alternate user context. ie. members plugin
		if ( has_filter( 'wp_sentry_user_context' ) ) {
			$user_context = apply_filters( 'wp_sentry_user_context', $user_context );
		}

		// Finally assign the user context to the client.
		$this->set_user_context( $user_context );
	}

	/**
	 * Set the sentry dsn.
	 *
	 * @param string $dsn The sentry dsn to use.
	 */
	public function set_dsn( $dsn ) {
		if ( is_string( $dsn ) ) {
			$this->dsn = $dsn;
		}
	}

	/**
	 * Get sentry dsn.
	 *
	 * @return string
	 */
	public function get_dsn() {
		return $this->dsn;
	}

	/**
	 * Set sentry options.
	 *
	 * @param array $options The sentry options to use.
	 */
	public function set_options( array $options ) {
		$this->options = $options;
	}

	/**
	 * Get sentry options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get sentry default options.
	 *
	 * @return array
	 */
	public function get_default_options() {
		return [];
	}

	/**
	 * Get sentry context.
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Sets the user context.
	 *
	 * @param array $data Associative array of user data
	 */
	public function set_user_context( array $data ) {
		$this->context['user'] = $data;
	}

	/**
	 * Get the user context.
	 *
	 * @return array|null
	 */
	public function get_user_context() {
		return $this->context['user'];
	}

	/**
	 * Appends the tags context.
	 *
	 * @param array $data Associative array of tags
	 */
	public function set_tags_context( array $data ) {
		$this->context['tags'] = array_merge( $this->context['tags'], $data );
	}

	/**
	 * Get the tags context.
	 *
	 * @return array
	 */
	public function get_tags_context() {
		return $this->context['tags'];
	}

	/**
	 * Appends the additional context.
	 *
	 * @param array $data Associative array of extra data
	 */
	public function set_extra_context( array $data ) {
		$this->context['extra'] = array_merge( $this->context['extra'], $data );
	}

	/**
	 * Get the additional context.
	 *
	 * @return array
	 */
	public function get_extra_context() {
		return $this->context['extra'];
	}

}
