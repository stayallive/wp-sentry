<?php

use Sentry\State\HubInterface;

/**
 * WordPress Sentry Redis Object Cache Integration
 *
 * @see      https://wordpress.org/plugins/redis-cache/
 *
 * @internal This class is not part of the public API and may be removed or changed at any time.
 */
final class WP_Sentry_Redis_Object_Cache_Integration {
	/**
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Redis_Object_Cache_Integration
	 */
	private static $instance;

	/**
	 * Get the Sentry Redis Object Cache Integration instance.
	 *
	 * @return WP_Sentry_Redis_Object_Cache_Integration
	 */
	public static function get_instance(): WP_Sentry_Redis_Object_Cache_Integration {
		return self::$instance ?: self::$instance = new self;
	}

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'redis_object_cache_error', [ $this, 'handle_redis_cache_failure' ] );
	}

	/**
	 * Fires when an object cache related error occurs.
	 *
	 * @param \Throwable $e The exception that was thrown.
	 *
	 * @return void
	 */
	public function handle_redis_cache_failure( Throwable $e ): void {
		wp_sentry_safe(
			static function ( HubInterface $client ) use ( $e ) {
				$client->captureException( $e );
			}
		);
	}
}
