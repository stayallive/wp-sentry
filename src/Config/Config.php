<?php
namespace WPSentry\Config;

use WPSentry\Config\ConfigInterface;
use ArrayObject;
use Exception;
use RuntimeException;

// Exit if plugin isn't running
defined( 'WP_SENTRY_EXISTS' ) || exit;

/**
 * Class Config.
 *
 * This is a very basic Config class that can be used to abstract away the
 * loading of a PHP array from a file.
 *
 * @since   0.1.0
 *
 * @package WPSentry\Config;
 * @link https://github.com/schlessera/better-settings-v1
 */
class Config extends ArrayObject implements ConfigInterface {

	/**
	 * Instantiate the Config object.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $config Array with settings or path to Config file.
	 */
	public function __construct( $config ) {

		// If a string was passed to the constructor, assume it is the path to
		// a PHP Config file.

		$config = $this->load_file( $config );

		// Make sure the config entries can be accessed as properties.
		parent::__construct( $config, ArrayObject::ARRAY_AS_PROPS );

	}

	/**
	 * Check whether the Config has a specific key.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key The key to check the existence for.
	 *
	 * @return bool Whether the specified key exists.
	 */
	public function has( $key ) {

		return array_key_exists( $key, (array) $this );

	}

	/**
	 * Get the value of a specific key.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key The key to get the value for.
	 *
	 * @return mixed Value of the requested key.
	 */
	public function get( $key = '', $value = '' ) {

		// If we are passing in a key, let's get the value
    if( $key && ! $value){

        return $this[ $key ];

    }

    // Let's handle getting nested values
    if( $key && $value){

        return $this[ $key ][ $value ];

    }

    // Return the whole array if no key is set
    return $this;

	}

	/**
	 * Get an array with all the keys.
	 *
	 * @since 0.1.0
	 *
	 * @return array Array of config keys.
	 */
	public function get_all_keys() {

		return array_keys( (array) $this );

	}

	/**
	 * Push a configuration in via the key
	 *
	 * @since 1.0.0
	 *
	 * @param string $parameter_key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return null
	 */
	public function push( $parameter_key, $value ) {

    $this[ $parameter_key ] = $value;

		$this->offsetSet( $parameter_key, $value );

	}

	/***************************
	 * Helpers
	 **************************/
	/**
	 * Loads the config file
	 *
	 * @since 1.0.0
	 *
	 * @param string $config_file
	 * @return string
	 */
	protected function load_file( $config_file ) {

		if ( $this->is_file_valid( $config_file ) ) {

			return include $config_file;

		}
	}

	/**
	 * Build the config file's full qualified path
	 *
	 * @since 1.0.0
	 *
	 * @param string $file
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function is_file_valid( $file ) {

		if ( ! $file ) {
			throw new InvalidArgumentException( __( 'A config filename must not be empty.', SENTRY_WP_TEXT_DOMAIN ) );
		}

		if ( ! is_readable( $file ) ) {
			throw new RuntimeException( sprintf( '%s %s', __( 'The specified config file is not readable', SENTRY_WP_TEXT_DOMAIN ), $file ) );
		}

		return true;
	}

}
