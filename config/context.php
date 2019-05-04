<?php
/**
 * Default runtime context configurations for WPSentry.
 *
 * @package WPSentry/config
 * @link https://docs.sentry.io/enriching-error-data/context/?platform=php
 * @since 3.0.0
 */

 return [

  /**
   * Default 'user' context config
   * We are setting this to anonymous by default and defining available properties.
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#capturing-the-user
   * @since 3.0.0
   */
  'user' => [

    'id'          => 0,
    'name'        => 'anonymous',
    'username'    => '',
    'email'       => '',
    'ip_address'  => '',

  ],

  /**
   * Default 'tags' context config
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#tagging-events
   * @since 3.0.0
   */
  'tags'  => [],

  /**
   * Default 'extra' context config
   *
   * @link https://docs.sentry.io/enriching-error-data/context/?platform=php#extra-context
   * @since 3.0.0
   */
  'extra' => [],

  // Default from
  'level' => 'error',

 ];
