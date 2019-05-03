<?php
namespace WPSentry\Context;
use WPSentry\Config\ConfigInterface;

/**
 * Wordpress Sentry Context class
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

  }

  /**
   * Hydrate the user context with data based on the current logged-in user.
   * If this data is not available, nothing will be added to the context.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#capturing-the-user
   *
   * Available Filter: `wp_sentry_user_context` - allow plugins to manage their own context. ie. members plugin.
   * Available Filter: `wp_sentry_user_context_hydrated` - control hydration state
   *
   * @uses wp_get_current_user() to get the currently logged-in user
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
	public function hydrate_user_context(){

    $is_hydrated = apply_filters( 'wp_sentry_user_context_hydrated', false );

    // Only hydrate the context once, otherwise bail
    if( $is_hydrated === true )
      return;

    $current_user = wp_get_current_user();

    // Bail if we can't verify the user
    if ( ! $current_user instanceof \WP_User || ! $current_user->exists() )
      return;

    $user_context = [

      'id'       		=> $current_user->ID,
      'name'     		=> $current_user->display_name,
      'email'    		=> $current_user->user_email,
      'username' 		=> $current_user->user_login,

    ];

    $user_context = (array) apply_filters( 'wp_sentry_user_context', $user_context );

    $user_context = (array) array_filter( $user_context );

    // Update our context configuration with new data
    $this->config->push( 'user', $user_context );

    // This context has been hydrated
    add_filter( 'wp_sentry_user_context_hydrated', '__return_true' );

	}

  /**
   * Hydrate the tags context with data based on the current environment
   *
   * Available Filter: `wp_sentry_tags_context` - allow plugins to manage their own context.
   * Available Filter: `wp_sentry_tags_context_hydrated` - control hydration state
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#tagging-events
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
	public function hydrate_tags_context(){

    $is_hydrated = apply_filters( 'wp_sentry_tags_context_hydrated', false );

    // Only hydrate the context once, otherwise bail
    if( $is_hydrated === true )
      return;

    $tags_context = [

      'language'    => get_bloginfo( 'language' ),
      'wordpress'		=> get_bloginfo( 'version' ),
      'woocommerce'	=> defined( 'WC_VERSION' ) ? WC_VERSION : '',
      'php'			    => PHP_VERSION,

    ];

    $tags_context = (array) apply_filters( 'wp_sentry_tags_context', $tags_context );

    // Clean out empty properties from array
    $tags_context = (array) array_filter( $tags_context );

    // Update our context configuration with new data
    $this->config->push( 'tags', $tags_context );

    // This context has been hydrated
    add_filter( 'wp_sentry_tags_context_hydrated', '__return_true' );

  }

  /**
   * Hydrate the tags context with data based on defined "extra context"
   * If this data is not available, nothing will be added to the context
   *
   * Available Filter: `wp_sentry_extra_context` - allow plugins to manage their own context.
   * Available Filter: `wp_sentry_extra_context_hydrated` - control hydration state
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#extra-context
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @since 3.0.0
   */
  public function hydrate_extra_context(){

    $is_hydrated = apply_filters( 'wp_sentry_extra_context_hydrated', false );

    // Only hydrate the context once, otherwise bail
    if( $is_hydrated === true )
      return;

    $extra_context = [];

    $extra_context = (array) apply_filters( 'wp_sentry_extra_context', $extra_context );

    $extra_context = (array) array_filter( $extra_context );

    // Update our context configuration with new data
    $this->config->push( 'extra', $extra_context );

    // This context has been hydrated
    add_filter( 'wp_sentry_extra_context_hydrated', '__return_true' );

  }

}
