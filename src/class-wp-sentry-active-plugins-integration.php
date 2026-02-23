<?php

use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;

/**
 * Adds the list of active WordPress plugins (name + version) to each event as modules.
 */
final class WP_Sentry_Active_Plugins_Integration implements IntegrationInterface {
	/**
	 * Cached modules for the duration of the request.
	 *
	 * @var array|null
	 */
	private static $active_plugins;

	public function setupOnce(): void {
		Scope::addGlobalEventProcessor( static function ( Event $event ): Event {
			$integration = SentrySdk::getCurrentHub()->getIntegration( self::class );

			// The integration could be bound to a client that is not the one
			// attached to the current hub. If this is the case, bail out
			if ( $integration === null ) {
				return $event;
			}

			$event->setModules( self::get_active_plugins() );

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

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once WP_SENTRY_WPADMIN . '/includes/plugin.php';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			return self::$active_plugins = [];
		}

		$all_plugins    = get_plugins();
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$network_active = array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) );
			$active_plugins = array_unique( array_merge( $active_plugins, $network_active ) );
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
}
