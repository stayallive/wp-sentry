# [WordPress Sentry](https://wordpress.org/plugins/wp-sentry-integration/) (wp-sentry)

A (unofficial) [WordPress plugin](https://wordpress.org/plugins/wp-sentry-integration/) to report PHP and JavaScript errors to [Sentry](https://sentry.io).

## What?

This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to [Sentry](https://sentry.io) and integrates with its release tracking.

It will auto detect authenticated users and add context where possible. All context/tags can be adjusted using filters mentioned below.

## Requirements

This plugin requires PHP 7.1+ starting from version 3.0.0.

If you are on older PHP versions you can use version 2.x.

> Please do use a PHP version that is not end of life (EOL) and no longer supported. 
For an up-to-date list of PHP versions that are still supported see: http://php.net/supported-versions.php.

Version 2.1.* of this plugin will be the last to support PHP 5.3.
Version 2.7.* of this plugin will be the last to support PHP 5.4.

## Usage

1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Configure your DSN as explained below
2. Activate the plugin through the WordPress admin interface

**Note:** this plugin does not do anything by default and has no admin interface. A DSN must be configured first.


## Configuration

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `PHP_DSN` with your actual DSN that you find in Sentry:

```php
define( 'WP_SENTRY_DSN', 'PHP_DSN' );
```

**Note:** Do not set this constant to disable the PHP tracker.

---

(Optionally) set the error types the PHP tracker will track:

```php
define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_USER_DEPRECATED );
```

---

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `JS_DSN` with your actual public DSN that you find in Sentry (**never use your private DSN**):

```php
define( 'WP_SENTRY_PUBLIC_DSN', 'JS_DSN' );
```

**Note:** Do not set this constant to disable the JavaScript tracker.

---

(Optionally) define a version of your site; by default the theme version will be used. This is used for tracking at which version of your site the error occurred. When combined with release tracking this is a very powerful feature.

```php
define( 'WP_SENTRY_VERSION', 'v3.0.0' );
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

**Note:** _This filter fires on when WP Sentry initializes. To change the DSN at runtime use the `wp_sentry_options` filter or set the DSN to the client directly._

---

#### `wp_sentry_options` (array)

You can use this filter to customize the Sentry [options](https://docs.sentry.io/error-reporting/configuration/?platform=php) used to initialize the PHP tracker.

Example usage:

```php
/**
 * Customize sentry options.
 *
 * @param array $options The current sentry options.
 *
 * @return array
 */
function customize_sentry_options( \Sentry\Options $options ) {
    // Only sample 90% of the events
    $options->setSampleRate(0.9);
}
add_filter( 'wp_sentry_options', 'customize_sentry_options' );
```

**Note:** _This filter fires on the WordPress `after_setup_theme` action._

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

You can use this filter to customize/override the Sentry [options](https://docs.sentry.io/error-reporting/configuration/?platform=browser#common-options) used to initialize the JS tracker.

> **WARNING:** These values are exposed to the public, so make sure you do not expose anything private !

Example usage:

```php
/**
 * Customize public sentry options.
 *
 * Note: Items prefixed with `regex:` in blacklistUrls and whitelistUrls option arrays
 * will be translated into pure RegExp.
 * 
 * @param array $options The current sentry public options.
 *
 * @return array
 */
function customize_sentry_public_options( array $options ) {
    return array_merge( $options, array(
        'sampleRate' => '0.5',
        'blacklistUrls' => array(
            'https://github.com/',
            'regex:\\w+\\.example\\.com',
        ),
    ));
}
add_filter( 'wp_sentry_public_options', 'customize_sentry_public_options' );
```


## Catching plugin errors

Since this plugin is called `wp-sentry-integration` it loads a bit late which could miss errors or notices occuring in plugins that load before it.

You can remedy this by loading WordPress Sentry as a must-use plugin by creating the file `wp-content/mu-plugins/wp-sentry-integration.php` (if the `mu-plugins` directory does not exists you must create that too).

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
 
$wp_sentry = __DIR__ . '/../plugins/wp-sentry-integration/wp-sentry.php';

if ( ! file_exists( $wp_sentry ) ) {
	return;
}

require $wp_sentry;

define( 'WP_SENTRY_MU_LOADED', true );
```

Now `wp-sentry-integration` will load always and before all other plugins.

**Note**: We advise you leave the original `wp-sentry-integration` in the `/wp-content/plugins` folder to still have updates come in through the WordPress updater. However enabling or disabling does nothing if the above script is active (since it will always be enabled).


## Security Vulnerabilities

If you discover a security vulnerability within WordPress Sentry (wp-sentry), please send an e-mail to Alex Bouma at me@alexbouma.me. All security vulnerabilities will be swiftly addressed.


## License

The WordPress Sentry (wp-sentry) plugin is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
