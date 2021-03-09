# [WordPress Sentry](https://wordpress.org/plugins/wp-sentry-integration/) (wp-sentry)

A (unofficial) [WordPress plugin](https://wordpress.org/plugins/wp-sentry-integration/) to report PHP and JavaScript errors to [Sentry](https://sentry.io).


## What?

This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to [Sentry](https://sentry.io) and integrates with its release tracking.

It will auto detect authenticated users and add context where possible. All context/tags can be adjusted using filters mentioned below.


## Requirements & Sentry PHP SDK

This plugin requires PHP `5.4`+ but urges users to use a PHP version that is not end of life (EOL) and no longer supported. For an up-to-date list of PHP versions that are still supported see: http://php.net/supported-versions.php.

- Version `2.1.*` of this plugin will be the last to support PHP `5.3`.
- Version `2.2.*` of this plugin will be the last to support PHP `5.4`.

Please note that version `3.x` is the most recent version of the wp-sentry plugin and only supports PHP `7.1` and up. If you need PHP `5.4-7.2` support check out version `2.x` but do keep in mind there are a lot of differences in the Sentry PHP SDK used.

- Version [`2.x`](https://github.com/stayallive/wp-sentry/tree/2.x) of the wp-sentry plugin uses the [`1.x`](https://github.com/getsentry/sentry-php/tree/1.x) version of the official Sentry PHP SDK.
- Version [`3.x`](https://github.com/stayallive/wp-sentry/tree/master) of the wp-sentry plugin uses the [`2.x`](https://github.com/getsentry/sentry-php/tree/master) version of the official Sentry PHP SDK.


## Usage

1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Configure your DSN as explained below
2. Activate the plugin through the WordPress admin interface

**Note:** this plugin does not do anything by default and has no admin interface. A DSN must be configured first.


## Configuration

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `PHP_DSN` with your actual DSN that you find inside Sentry in the project settings under "Client Keys (DSN)":
```php
define( 'WP_SENTRY_PHP_DSN', 'PHP_DSN' );
```

**Note:** Do not set this constant to disable the PHP tracker.

**Note:** This constant was previously called `WP_SENTRY_DSN` and is still supported.

---

(Optionally) set the error types the PHP tracker will track:

```php
define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_USER_DEPRECATED );
```

---

(Optionally) If this flag is enabled, certain personally identifiable information is added by active integrations. Without this flag they are never added to the event, to begin with.

If possible, it’s recommended to turn on this feature and use the server side PII stripping to remove the values instead.

When enabled the current logged in user and IP address will be added to the event.

```php
define( 'WP_SENTRY_SEND_DEFAULT_PII', true );
```

---

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `JS_DSN` with your actual DSN that you find inside Sentry in the project settings under "Client Keys (DSN)":
```php
define( 'WP_SENTRY_BROWSER_DSN', 'JS_DSN' );
```

**Note:** Do not set this constant to disable the JavaScript tracker.

**Note:** This constant was previously called `WP_SENTRY_PUBLIC_DSN` and is still supported.

---

(Optionally) enable JavaScript performance tracing by adding this snippet to your `wp-config.php` and replace `0.3` with your desired sampling rate (`0.3` means sample ~30% of your traffic):
```php
define( 'WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE', 0.3 );
```

**Note:** Do not set this constant or set it to `0.0` to disable the JavaScript performance tracing.

---

(Optionally) define a version of your site; by default the theme version will be used. This is used for tracking at which version of your site the error occurred. When combined with release tracking this is a very powerful feature.

```php
define( 'WP_SENTRY_VERSION', 'v4.2.0' );
```

(Optionally) define an environment of your site. Defaults to `unspecified`.

```php
define( 'WP_SENTRY_ENV', 'production' );
```


## Filters

This plugin provides the following filters to plugin/theme developers.

Please note that some filters are fired when the Sentry trackers are initialized so they won't fire if you define them in your theme or in a plugin that loads after WP Sentry does.

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

**Note:** _This filter fires on the WordPress `set_current_user` action and only if the `WP_SENTRY_SEND_DEFAULT_PII` constant is set to `true`._

### Specific to PHP tracker:

#### `wp_sentry_dsn` (string)

You can use this filter to override the Sentry DSN used for the PHP tracker.

Example usage:

```php
/**
 * Customize sentry dsn.
 *
 * @param string $dsn The current sentry DSN.
 *
 * @return string
 */
function customize_sentry_dsn( $dsn ) {
    return 'https://<key>:<secret>@sentry.io/<project>';
}
add_filter( 'wp_sentry_dsn', 'customize_sentry_dsn' );
```

**Note:** _This filter fires on the WordPress `after_setup_theme` action. It is discouraged to use this and instead define the DSN in the `wp-config.php` using the `WP_SENTRY_PHP_DSN` constant_

---

#### `wp_sentry_scope` (void)

You can use this filter to customize the Sentry [scope](https://docs.sentry.io/enriching-error-data/context/?platform=php).

Example usage:

```php
/**
 * Customize Sentry PHP SDK scope.
 *
 * @param \Sentry\State\Scope $scope
 *
 * @return void
 */
function customize_sentry_scope( \Sentry\State\Scope $scope ) {
	$scope->setTag('my-custom-tag', 'tag-value');
}
add_filter( 'wp_sentry_scope', 'customize_sentry_scope' );
```

**Note:** _This filter fires on the WordPress `after_setup_theme` action._

---

#### `wp_sentry_options` (array)

You can use this filter to customize the Sentry [options](https://docs.sentry.io/error-reporting/configuration/?platform=php).

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

#### `wp_sentry_public_context` (array)

You can use this filter to customize/override the Sentry context, you can modify the `user`, `tags` and `extra` context.

> **WARNING:** These values are exposed to the public, so make sure you do not expose anything private !

Example usage:

```php
/**
 * Customize public sentry context.
 *
 * @param array $context The current sentry public context.
 *
 * @return array
 */
function customize_sentry_public_context( array $context ) {
    $context['tags']['my-custom-tag'] = 'tag-value';

    return $context;
}
add_filter( 'wp_sentry_public_context', 'customize_sentry_public_context' );
```


## High volume of notices

Many plugin in the WordPress ecosystem generate notices that are captured by the Senty plugin.

This can cause a high volume of events and even slower page loads because of those events being transmitted to Sentry.

The prevent this you can set the following in your `wp-config.php` to filter out errors of the notice type.

```php
define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_NOTICE );
```


## Capturing handled exceptions

The best thing to do with an exception is to capture it yourself, however you might still want to know about it.

The Sentry plugin only captures unhandled exceptions and fatal errors, to capture handled exception you can do the following:

```php
try {
	myMethodThatCanThrowAnException();
} catch ( \Exception $e ) {
	// We are using wp_sentry_safe to make sure this code runs even if the Sentry plugin is disabled
	if ( function_exists( 'wp_sentry_safe' ) ) {
		wp_sentry_safe( function ( \Sentry\State\HubInterface $client ) use ( $e ) {
			$client->captureException( $e );
		} );
	}

	wp_die( 'There was an error doing this thing you were doing, we have been notified!' );
}
```

If you need to attach extra data only for the handled exception, you could add [Structured Context](https://docs.sentry.io/platforms/php/enriching-events/context/#structured-context):

```php
if (function_exists('wp_sentry_safe')) {
    wp_sentry_safe(function (\Sentry\State\HubInterface $client) use ($e) {
        $client->withScope(function (\Sentry\State\Scope $scope) use ($client, $e) {
            $scope->setExtra('user_data', $e->getData());
            $client->captureException($e);
        });
    });
}
```

If you need to add data to the scope in every case use `configureScope` in [wp_sentry_scope filter](#wp_sentry_scope-void).

## Capturing plugin errors

Since this plugin is called `wp-sentry-integration` it loads a bit late which could miss errors or notices occuring in plugins that load before it.

You can remedy this by loading WordPress Sentry as a must-use plugin by creating the file `wp-content/mu-plugins/wp-sentry-integration.php` (if the `mu-plugins` directory does not exist you must create that too).

```php
<?php

/**
 * Plugin Name: WordPress Sentry
 * Plugin URI: https://github.com/stayallive/wp-sentry
 * Description: A (unofficial) WordPress plugin to report PHP and JavaScript errors to Sentry.
 * Version: must-use-proxy
 * Author: Alex Bouma
 * Author URI: https://alex.bouma.dev
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


## Advanced: Client side hook

When using the Sentry Browser integration it is possible to do some work in the client browser before Sentry is initialized to change options and/or prevent the Browser SDK from initializing at all.

You do this by defining a `wp_sentry_hook` JavaScript function before the Sentry Browser JavaScript file is included (keep this function small and easy since any errors that occur in there are not tracked by the Browser SDK).

A quick example on how you would disable the Browser SDK using `wp_add_inline_script`:

```php
add_action( 'wp_enqueue_scripts', function () {
    wp_add_inline_script( 'wp-sentry-browser', 'function wp_sentry_hook(options) { return someCheckInYourCode() ? true : false; }', 'before' );
} );
```

When the `wp_sentry_hook` function returns `false` the initialization of the Sentry Brower SDK will be stopped. Any other return value will be ignored.

To modify the options you can modify the object passed as the first argument of the `wp_sentry_hook`, this object will later be passed to `Sentry.init` to initialize the Browser SDK.


## Security Vulnerabilities

If you discover a security vulnerability within WordPress Sentry (wp-sentry), please send an e-mail to Alex Bouma at `alex+security@bouma.me`. All security vulnerabilities will be swiftly addressed.


## License

The WordPress Sentry (wp-sentry) plugin is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
