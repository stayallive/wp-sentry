<?php
namespace WPSentry\Tracker;

use WPSentry\Config\ConfigInterface;
use WPSentry\Context\Context;
use WPSentry\Tracker\TrackerBase;

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
   * Note that contexts should be hydrated at this point, but if they are not
   * they will get hydrated here. We are safe to attempt this, as contexts
   * will only ever be hydrated once per page lifecycle no matter how many
   * times the hydration methods are called.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#capturing-the-user
   * @since 3.0.0
   */
  public function load_sentry_js(){

    // If any contexts have not yet been hydrated, they will be hydrated here
    $this->context->hydrate_user_context();
    $this->context->hydrate_tags_context();
    $this->context->hydrate_extra_context();

    $script_data = [

      'init_options' => $this->get_init_config(),
      'context'      => $this->context->config->get(),

    ];

    // TODO: Generate content hash on wp-sentry.js compile and dynamically reference it from a manifest when enqueuing
    wp_enqueue_script(
      'wp-sentry-browser',
      WP_SENTRY_PLUGIN_DIR_URL . 'dist/wp-sentry.js',
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
