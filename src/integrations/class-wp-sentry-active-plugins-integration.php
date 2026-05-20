<?php

use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;

/**
 * This integration adds the list of active plugins and their versions to events.
 *
 * @internal This class is not part of the public API and may be removed or changed at any time.
 */
final class WP_Sentry_Active_Plugins_Integration implements IntegrationInterface {
	/**
	 * In-memory cached modules, populated once per request.
	 *
	 * @var array|null
	 */
	private static $active_plugins;

	public function setupOnce(): void {
		Scope::addGlobalEventProcessor( static function ( Event $event ): Event {
			$integration = SentrySdk::getCurrentHub()->getIntegration( self::class );

			// The integration could be bound to a client that is not the one
			// attached to the current hub. If this is the case, bail out
			if ( $integration !== null ) {
				$event->setModules( self::get_active_plugins() );
			}

			return $event;
		} );
	}

	/**
	 * Gather the active plugins plus their version.
	 *
	 * @return array<string, string>
	 */
	private static function get_active_plugins(): array {
		if ( self::$active_plugins !== null ) {
			return self::$active_plugins;
		}

		if ( ! self::can_collect_active_plugins() ) {
			return [];
		}

		try {
			$all_plugins    = get_plugins();
			$active_plugins = (array) get_option( 'active_plugins', [] );

			if ( function_exists( 'is_multisite' ) && function_exists( 'get_site_option' ) && is_multisite() ) {
				$network_active = array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) );
				$active_plugins = array_unique( array_merge( $active_plugins, $network_active ) );
			}
		} catch ( Throwable $e ) {
			return [];
		}

		$modules = [];

		foreach ( $active_plugins as $plugin_file ) {
			$plugin_data = $all_plugins[ $plugin_file ] ?? null;

			$name    = is_array( $plugin_data ) && isset( $plugin_data['Name'] ) ? (string) $plugin_data['Name'] : (string) $plugin_file;
			$version = is_array( $plugin_data ) && isset( $plugin_data['Version'] ) ? (string) $plugin_data['Version'] : 'unknown';

			$modules[ $name ] = $version;
		}

		ksort( $modules );

		return self::$active_plugins = $modules;
	}

	/**
	 * Check if WordPress has loaded enough APIs to collect active plugin data.
	 */
	private static function can_collect_active_plugins(): bool {
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			return false;
		}

		if (
			! function_exists( 'get_option' ) ||
			! function_exists( 'wp_cache_get' ) ||
			! function_exists( 'wp_cache_set' ) ||
			! function_exists( 'get_file_data' )
		) {
			return false;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			$plugin_api = WP_SENTRY_WPADMIN . '/includes/plugin.php';

			if ( ! is_readable( $plugin_api ) ) {
				return false;
			}

			require_once $plugin_api;
		}

		return function_exists( 'get_plugins' );
	}
}
