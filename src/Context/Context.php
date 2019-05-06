<?php
namespace WPSentry\Context;
use WPSentry\Config\ConfigInterface;

// Exit if plugin isn't running
defined( 'WP_SENTRY_EXISTS' ) || exit;

/**
 * Wordpress Sentry ContextProvider class
 *
 * @package WPSentry\Context;
 * @since 3.0.0
 */
class Context{

  /**
   * Holds an instance of the context runtime config
   *
   * @var ConfigInterface
   */
	public $config;

  /**
   * Class constructor
   *
   * @param ConfigInterface $config - runtime configurations for Context
   */
	public function __construct( ConfigInterface $config ){

    $this->config = $config;

    $this->set_plugin_context_data();
    $this->hydrate_all_contexts();

  }

  /**
   * Add data directly to our contexts as it becomes available.
   *
   * By default, the context data we are loading comes straight from our config file
   * in `wp-sentry/config/context.php`.
   *
   * In most cases, the data we might want to use is not available at the time the config file loads,
   * so we need to wait until we have access to the data before adding it.
   *
   * Using our available context filters (which come into existence once data is available),
   * we are putting our best foot forward in adding sensible initial context data.
   *
   * @since 3.0.0
   */
  private function set_plugin_context_data(){

    add_filter( 'wp_sentry_user_context', [ $this, 'set_user_context_data' ], 0, 1 );
    add_filter( 'wp_sentry_tags_context', [ $this, 'set_tags_context_data' ], 0, 1 );
    add_filter( 'wp_sentry_extra_context', [ $this, 'set_extra_context_data' ], 0, 1 );

  }

  /**
   * Handle orchestrating each context's hydration on the appropriate hook.
   *
   * Our contexts need to be hydrated with data, but we need to be sure the data we need
   * to hydrate them with is available. Each context is associated with a specific hook,
   * which calls that context's hydration function at the appropriate time.
   *
   * @since 3.0.0
   */
  private function hydrate_all_contexts(){

    add_action( 'set_current_user', [ $this, 'hydrate_user_context' ] );
    add_action( 'after_setup_theme', [ $this, 'hydrate_tags_context' ] );
    add_action( 'after_setup_theme', [ $this, 'hydrate_extra_context' ] );

  }

  /**
   * Set context data based on the current Wordpress user.
   * If the user cannot be verified, nothing will be set.
   *
   * @uses wp_get_current_user - get an instance of the currently logged-in user
   * @param array $user_context - existing user context
   * @return array $user_context - new user context
   * @since 3.0.0
   */
  public function set_user_context_data( $user_context ){

    $current_user = (object) wp_get_current_user();

    if ( ! $current_user instanceof \WP_User || ! $current_user->exists() )
      return $user_context;

    $user_context = [

      'id'       		=> $current_user->ID,
      'name'     		=> $current_user->display_name,
      'email'    		=> $current_user->user_email,
      'username' 		=> $current_user->user_login,

    ];

    return $user_context;

  }

  /**
   * Set context data based on the current WordPress environment. It will also
   * set a WooCommerce version of it is available.
   *
   * @uses get_bloginfo - get information about the current WordPress environment
   * @param array $tags_context - existing tags context
   * @return array $tags_context - new tags context
   * @since 3.0.0
   */
  public function set_tags_context_data( $tags_context ){

    $tags_context = [

      'language'    => get_bloginfo( 'language' ),
      'wordpress'		=> get_bloginfo( 'version' ),
      'woocommerce'	=> defined( 'WC_VERSION' ) ? WC_VERSION : '',
      'php'			    => PHP_VERSION,

    ];

    return $tags_context;

  }

  /**
   * Set extra context data
   *
   * * This is a placeholder for possible future use. Currently no data is set,
   * * this function just returns the data without modifying it.
   *
   * @param array $extra_context - existing extra context
   * @return array $extra_context - new extra context
   * @since 3.0.0
   */
  public function set_extra_context_data( $extra_context ){

    return $extra_context;

  }

  /**
   * Hydrate the user context with data passed in via the user context filter
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#capturing-the-user
   *
   * Available Filter: `wp_sentry_user_context` - allow plugins to manage their own context. ie. members plugin.
   * Available Action: `wp_sentry_user_context_hydrated` - run actions after the context is fully hydrated
   *
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
	public function hydrate_user_context(){

    $user_context = $this->config->get( 'user' );

    $user_context = (array) apply_filters( 'wp_sentry_user_context', $user_context );

    $user_context = (array) array_filter( $user_context );

    // Update our context configuration with new data
    $this->config->push( 'user', $user_context );

    // @hooked - provide_user_context - 0 - \WPSentry\Tracker\PHP
    do_action( 'wp_sentry_user_context_hydrated', $this->config->get( 'user' ) );

	}

  /**
   * Hydrate the tags context with data passed in via the tags context filter
   *
   * Available Filter: `wp_sentry_tags_context` - allow plugins to manage their own context.
   * Available Action: `wp_sentry_tags_context_hydrated` - run actions after the context is fully hydrated
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#tagging-events
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
	public function hydrate_tags_context(){

    $tags_context = $this->config->get( 'tags' );

    $tags_context = (array) apply_filters( 'wp_sentry_tags_context', $tags_context );

    // Clean out empty properties from array
    $tags_context = (array) array_filter( $tags_context );

    // Update our context configuration with new data
    $this->config->push( 'tags', $tags_context );

    // @hooked - provide_tags_context - 0 - \WPSentry\Tracker\PHP
    do_action( 'wp_sentry_tags_context_hydrated', $this->config->get( 'tags' ) );

  }

  /**
   * Hydrate the extra context with data passed in via the extra context filter
   *
   * Available Filter: `wp_sentry_extra_context` - allow plugins to manage their own context.
   * Available Action: `wp_sentry_extra_context_hydrated` - run actions after the context is fully hydrated
   *
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#extra-context
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
  public function hydrate_extra_context(){

    $extra_context = $this->config->get( 'extra' );

    $extra_context = (array) apply_filters( 'wp_sentry_extra_context', $extra_context );

    $extra_context = (array) array_filter( $extra_context );

    // Update our context configuration with new data
    $this->config->push( 'extra', $extra_context );

    // @hooked - provide_extra_context - 0 - \WPSentry\Tracker\PHP
    do_action( 'wp_sentry_extra_context_hydrated', $this->config->get( 'extra' ) );

  }

}
