<?php
namespace WPSentry\Tracker;

use WPSentry\Config\ConfigInterface;
use WPSentry\Context\Context;
use WPSentry\Tracker\TrackerBase;

// Exit if plugin isn't running
defined( 'WP_SENTRY_EXISTS' ) || exit;

/**
 * Implement Sentry.io for Javascript
 *
 * @package WPSentry\Tracker;
 * @since 3.0.0
 */
final class JS extends TrackerBase{

  /**
   * Bootstrap the tracker
   *
   * Define enqueue actions where our Javascript needs to be loaded
   *
   * @since 3.0.0
   */
	protected function bootstrap(){

    // Register on front-end using the highest priority.
		add_action( 'wp_enqueue_scripts', [ $this, 'load_sentry_js' ], 0 );

		// Register on admin using the highest priority.
		add_action( 'admin_enqueue_scripts', [ $this, 'load_sentry_js' ], 0 );

		// Register on login using the highest priority.
		add_action( 'login_enqueue_scripts', [ $this, 'load_sentry_js' ], 0 );

  }

  /**
   * Enqueue the plugin-defined version of sentry-browser.js and then localize the
   * script with runtime configuration options and the currently defined context.
   *
   * Note that contexts should all be hydrated at this point, as the enqueue scripts hooks
   * run much later than the context hydration functions.
   *
   * @see `wp-sentry/src/Context/Context.php - hydrate_all_contexts()` for more info on the hydration process
   *
   * @since 3.0.0
   */
  public function load_sentry_js(){

    $script_data = [

      'init_options' => $this->get_init_config(),
      'context'      => $this->context->config->get(),

    ];

    // TODO: Generate content hash on wp-sentry.js compile and dynamically reference it from a manifest when enqueuing
    wp_enqueue_script(
      'wp-sentry-browser',
      $this->manifest->get_asset_url( 'wpSentry.js' ),
      [],
      ''
    );

    wp_localize_script(
      'wp-sentry-browser',
      'wp_sentry',
      $script_data
    );

  }

  /**
   * Get initial runtime configurations to pass along to Sentry.
   *
   * Available Filter: 'wp_sentry_public_options' - allow plugins to manage their own context.
   *
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @return array
   */
  protected function get_init_config() : array{

    // Get user init options
    $init_config = (array) apply_filters( 'wp_sentry_public_options', $this->init_config->get() );

    // Clean out empty array properties.
    $init_config = (array) array_filter( $init_config );

    return $init_config;

  }

}
