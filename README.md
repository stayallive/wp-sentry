# WordPress [Sentry](https://sentry.io) (wp-sentry)

A (unofficial) WordPress plugin to report PHP and JavaScript errors to [Sentry](https://sentry.io).

## What?

This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to [Sentry](https://sentry.io) and integrates with its release tracking.

It will auto detect authenticated users and add context where possible. All context/tags can be adjusted using filters mentioned below.

## Usage

1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Configure your DSN as explained below
2. Activate the plugin through the WordPress admin interface

**Note:** this plugin does not do anything by default and has no admin interface. A DSN must be configured first.


## Configuration

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `DSN` with your actual DSN that you find in Sentry:

```php
define( 'WP_SENTRY_DSN', 'DSN' );
```

**Note:** Do not set this constant to disable the PHP tracker.

---

(Optionally) set the error types the PHP tracker will track:

```php
define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_USER_DEPRECATED );
```

---

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `PUBLIC_DSN` with your actual public DSN that you find in Sentry (**never use your private DSN**):

```php
define( 'WP_SENTRY_PUBLIC_DSN', 'PUBLIC_DSN' );
```

**Note:** Do not set this constant to disable the JavaScript tracker.

---

(Optionally) define a version of your site; by default the theme version will be used. This is used for tracking at which version of your site the error occurred. When combined with release tracking this is a very powerful feature.

```php
define( 'WP_SENTRY_VERSION', 'v2.0.17' );
```

(Optionally) define an environment of your site. Defaults to `unspecified`.

```php
define( 'WP_SENTRY_ENV', 'production' );
```

## Filters

This plugin provides the following filters to plugin/theme developers.

Please note that some filters are fired when the Sentry trackers are initialized so they won't fire if you define them in you theme or in a plugin that loads after WP Sentry does.

### Common to PHP & JavaScript trackers

#### `wp_sentry_user_context` (array)

You can use this filter to extend the Sentry user context for both PHP and JS trackers.

> **WARNING:** These values are exposed to the public in the JS tracker, so make sure you do not expose anything private!

Example usage:

```php
/**
 * Customize sentry user context.
 *
 * @param array $user The current sentry user context.
 *
 * @return array
 */
function customize_sentry_user_context( array $user ) {
    return array_merge( $user, array(
        'a-custom-user-meta-key' => 'custom value',
    ));
}
add_filter( 'wp_sentry_user_context', 'customize_sentry_user_context' );
```

**Note:** _This filter fires on the WordPress `set_current_user` action._


### Specific to PHP tracker:

#### `wp_sentry_dsn` (string)

You can use this filter to override the Sentry DSN used for the PHP tracker.

Example usage:

```php
/**
 * Customize sentry dsn.
 *
 * @param string $dsn The current sentry public dsn.
 *
 * @return string
 */
function customize_sentry_dsn( $dsn ) {
    return 'https://<key>:<secret>@sentry.io/<project>';
}
add_filter( 'wp_sentry_dsn', 'customize_sentry_dsn' );
```

**Note:** _This filter fires on when WP Sentry initializes and after the WP `after_setup_theme`._

---

#### `wp_sentry_options` (array)

You can use this filter to customize the Sentry options used to initialize the PHP tracker.

Example usage:

```php
/**
 * Customize sentry options.
 *
 * @param array $options The current sentry options.
 *
 * @return array
 */
function customize_sentry_options( array $options ) {
    return array_merge( $options, array(
        'tags' => array(
            'my-custom-tag' => 'custom value',
        ),
    ));
}
add_filter( 'wp_sentry_options', 'customize_sentry_options' );
```

**Note:** _This filter fires on when WP Sentry initializes and after the WP `after_setup_theme`._

---

#### `wp_sentry_send_data` (array|bool)

Provide a function which will be called before Sentry PHP tracker sends any data, allowing you both to mutate that data, as well as prevent it from being sent to the server.

Example usage:

```php
/**
 * Customize sentry send data.
 *
 * @param array $data The sentry send data.
 *
 * @return array|bool Return the data array or false to cancel the send operation.
 */
function filter_sentry_send_data( array $data ) {
    $data['tags']['my_custom_key'] = 'my_custom_value';

    return $data;
}
add_filter( 'wp_sentry_send_data', 'filter_sentry_send_data' );
```

**Note:** _This filter fires whenever the Sentry SDK is sending data to the Sentry server._

### Specific to JS tracker

#### `wp_sentry_public_dsn` (string)

You can use this filter to override the Sentry DSN used for the JS tracker.

> **WARNING:** This value is exposed to the public, so make sure you do not use your private DSN!

Example usage:

```php
/**
 * Customize public sentry dsn.
 *
 * @param string $dsn The current sentry public dsn.
 *
 * @return string
 */
function customize_public_sentry_dsn( $dsn ) {
    return 'https://<key>@sentry.io/<project>';
}
add_filter( 'wp_sentry_public_dsn', 'customize_public_sentry_dsn' );
```

---

#### `wp_sentry_public_options` (array)

You can use this filter to customize/override the sentry options used to initialize the JS tracker.

> **WARNING:** These values are exposed to the public, so make sure you do not expose anything private !

Example usage:

```php
/**
 * Customize public sentry options.
 *
 * @param array $options The current sentry public options.
 *
 * @return array
 */
function customize_public_sentry_options( array $options ) {
    return array_merge( $options, array(
        'tags' => array(
            'custom-tag' => 'custom value',
        ),
    ));
}
add_filter( 'wp_sentry_public_options', 'customize_sentry_public_options' );
```


## Catching plugin errors

Since this plugin is called `wp-sentry-integration` it loads a bit late which could miss errors or notices occuring in plugins that load before it.

You can remedy this by loading WordPress Sentry as a must-use plugin by creating the file `wp-content/mu-plugins/wp-sentry-integration.php` (if the `mu-plugins` directory does not exists you ust create that too).

```php
<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: must-use-proxy
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.me
 * License: MIT
 */

require __DIR__ . '/../plugins/wp-sentry-integration/wp-sentry.php';

define( 'WP_SENTRY_MU_LOADED', true );
```

Now `wp-sentry-integration` will load always and before all other plugins.

**Note**: We advise you leave the original `wp-sentry-integration` in the `/wp-content/plugins` folder to still have updates come in through the WordPress updater. However enabling or disabling does nothing if the above script is active (since it will always be enabled).


## Security Vulnerabilities

If you discover a security vulnerability within WordPress Sentry (wp-sentry), please send an e-mail to Alex Bouma at me@alexbouma.me. All security vulnerabilities will be swiftly addressed.


## License

The WordPress Sentry (wp-sentry) plugin is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
