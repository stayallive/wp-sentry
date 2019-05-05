<?php
namespace WPSentry\Config;

/**
 * Interface ConfigInterface.
 *
 * Config data abstraction that can be used to inject arbitrary Config values
 * into other classes.
 *
 * @package WPSentry\Config
 * @see https://github.com/schlessera/better-settings-v1
 * @since 3.0.0
 */
interface ConfigInterface {

    /**
     * Check whether the Config has a specific key.
     *
     * @since 3.0.0
     *
     * @param  string $key The key to check the existence for.
     * @return bool        Whether the specified key exists.
     */
    public function has( $key );

    /**
     * Get the value of a specific key.
     *
     * @since 3.0.0
     *
     * @param  string $key The key to get the value for.
     * @return mixed       Value of the requested key.
     */
    public function get( $key );

    /**
     * Get an array with all the keys.
     *
     * @since 3.0.0
     *
     * @return array Array of config keys.
     */
    public function get_all_keys();

    /**
	 * Push a configuration in via the key
	 *
	 * @since 3.0.0
	 *
	 * @param string $parameter_key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return null
	 */
    public function push( string $parameter_key, $value );

 }
