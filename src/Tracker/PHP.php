<?php
namespace WPSentry\Tracker;

use Sentry;
use WPSentry\Config\ConfigInterface;
use WPSentry\Context\Context;
use WPSentry\Tracker\TrackerBase;

/**
 * Implement Sentry.io for PHP
 *
 * @since 3.0.0
 */
class PHP extends TrackerBase {

  /**
   * Bootstrap the tracker with runtime configs and handle providing
   * additional context to Sentry as it become available.
   *
   * @link https://docs.sentry.io/error-reporting/configuration/?platform=php
   * @since 3.0.0
   */
	protected function bootstrap(){

    Sentry\init( $this->get_init_config() );

    add_action( 'set_current_user', [ $this, 'provide_user_context' ] );
    add_action( 'add_theme_support', [ $this, 'provide_tags_context' ] );
    add_action( 'add_theme_support', [ $this, 'provide_extra_context' ] );

  }

  /**
   * Populate our `user` context with data and send that data to Sentry.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#capturing-the-user
   * @since 3.0.0
   */
  public function provide_user_context(){

    $this->context->hydrate_user_context();

    Sentry\configureScope(function (Sentry\State\Scope $scope){

      $scope->setUser( $this->context->config->get( 'user' ) );

    });

  }

  /**
   * Populate our `tags` context with data and send that data to Sentry.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#tagging-events
   * @since 3.0.0
   */
  public function provide_tags_context(){

    $this->context->hydrate_tags_context();

		Sentry\configureScope(function (Sentry\State\Scope $scope){

      $tags_context = $this->context->config->get( 'tags' );

      // Bail if we have no data
      if( ! $tags_context )
        return;

			foreach( $tags_context as $tagName => $tagValue ){

				$scope->setTag( $tagName, $tagValue );

			}

		});

  }

  /**
   * Populate our `extra` context with data and send that data to Sentry.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#extra-context
   * @since 3.0.0
   */
  public function provide_extra_context(){

    $this->context->hydrate_extra_context();

    Sentry\configureScope(function (Sentry\State\Scope $scope){

      $extra_context = $this->context->config->get( 'extra' );

      // Bail if we have no data
      if( ! $extra_context )
        return;

			foreach( $extra_context as $extraName => $extraValue ){

        $scope->setExtra( $tagName, $tagValue );

			}

		});

  }

  /**
   * Get initial runtime configurations to pass along to Sentry.
   *
   * This configuration is filterable via `wp_sentry_options` to allow plugins and
   * themes to manage their own context.
   *
   * @uses array_filter() to clean out empty properties from the array before returning.
   * @return array
   */
  protected function get_init_config(): array{

    // Get user init options
    $init_config = (array) apply_filters( 'wp_sentry_options', $this->init_config->get() );

    // Clean out empty array properties.
    $init_config = (array) array_filter( $init_config );

    return $init_config;

  }

}
