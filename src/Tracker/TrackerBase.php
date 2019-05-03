<?php
namespace WPSentry\Tracker;

use WPSentry\Config\ConfigInterface;
use WPSentry\Context\Context;
use Sentry;

/**
 * Wordpress Sentry Tracker Base Abstract class
 *
 * This class holds shared configuration and functionality
 * for various Sentry.io implementations
 *
 * @package WPSentry\Context;
 * @since 3.0.0
 */
abstract class TrackerBase{

  /**
   * Holds an instance of the Sentry init runtime configurations.
   *
   * @since 3.0.0
   * @var ConfigInterface
   */
  protected $init_config;

  /**
   * Holds an instance of Context

   * @since 3.0.0
   * @var Context
   */
  protected $context;

  /**
   * Holds the Sentry dsn for the current instance
   *
   * @since 3.0.0
   * @var string
   */
  private $dsn;

  /**
   * Class constructor
   *
   * @param string $dsn - the dsn being used for this tracker instance
   * @param ConfigInterface $config - runtime configurations for this tracker instance
   * @param Context $context - the context being used in this tracker instance
   */
	public function __construct( string $dsn, ConfigInterface $config, Context $context ){

    $this->init_config = $config;
    $this->dsn = $dsn;
    $this->context = $context;

    // Update the init configs with the dsn for this instance
    $this->init_config->push( 'dsn', $this->dsn );

    // Bootstrap the tracker
    $this->bootstrap();

  }

  /**
   * Bootstrap the tracker
   *
   * @since 3.0.0
   */
  abstract protected function bootstrap();

  /**
   * Each class that extends TrackerBase must define a way to
   * retrieve, filter, and clean the $init_config variable.
   *
   * We leave it up to the extended class because each tracker
   * needs to have a unique filter for the user to reference
   * in order to modify and return new configuration values as
   * needed.
   *
   * @since 3.0.0
   * @return array
   */
  abstract protected function get_init_config(): array;

}
