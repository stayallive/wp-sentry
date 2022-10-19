=== WordPress Sentry ===
Contributors: stayallive
Tags: sentry, errors, tracking
Requires at least: 4.4
Tested up to: 6.0
Requires PHP: 7.2
Stable tag: trunk
License: MIT
License URI: https://github.com/stayallive/wp-sentry/blob/master/LICENSE.md

A (unofficial) WordPress plugin to report PHP errors and JavaScript errors to Sentry.

== Description ==
This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to Sentry and integrates with its release tracking.

It will auto detect authenticated users and add context where possible. All context/tags can be adjusted/expanded using filters.

_For more information and documentation have a look at the [README.md](https://github.com/stayallive/wp-sentry/blob/v6.1.0/README.md) file._

== Installation ==
1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Configure your DSN as explained below
2. Activate the plugin through the WordPress admin interface

_For more information and documentation have a look at the [README.md](https://github.com/stayallive/wp-sentry/blob/v6.1.0/README.md) file._

**Note:** this plugin does not do anything by default and has only a admin interface to test the integration. A Sentry DSN must be configured in your `wp-config.php`.

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `PHP_DSN` with your actual DSN that you find inside Sentry in the project settings under "Client Keys (DSN)":

`define( 'WP_SENTRY_PHP_DSN', 'PHP_DSN' );`

**Note:** Do not set this constant to disable the PHP tracker.

**Note:** This constant was previously called `WP_SENTRY_DSN` and is still supported.

(Optionally) set the error types the PHP tracker will track:

`define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_USER_DEPRECATED );`

(Optionally) If this flag is enabled, certain personally identifiable information is added by active integrations. Without this flag they are never added to the event, to begin with.

If possible, it’s recommended to turn on this feature and use the server side PII stripping to remove the values instead.

When enabled the current logged in user and IP address will be added to the event.

`define( 'WP_SENTRY_SEND_DEFAULT_PII', true );`

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `JS_DSN` with your actual DSN that you find inside Sentry in the project settings under "Client Keys (DSN)":

`define( 'WP_SENTRY_BROWSER_DSN', 'JS_DSN' );`

**Note:** Do not set this constant to disable the JavaScript tracker.

**Note:** This constant was previously called `WP_SENTRY_PUBLIC_DSN` and is still supported.

(Optionally) define a version of your site; by default the theme version will be used. This is used for tracking at which version of your site the error occurred. When combined with release tracking this is a very powerful feature.

`define( 'WP_SENTRY_VERSION', 'v6.1.0' );`

(Optionally) define an environment of your site. Defaults to `unspecified`.

`define( 'WP_SENTRY_ENV', 'production' );`

_For more information and documentation have a look at the [README.md](https://github.com/stayallive/wp-sentry/blob/v6.1.0/README.md) file._

== Changelog ==
= 6.1.0 =

* Update PHP SDK to version 3.9.1
* Update Sentry Browser to version 7.16.0

= 6.0.0 =

Note: This is a *breaking release* for the Browser/JavaScript SDK

Since 5.0.0 of this plugin, which bundles the Browser SDK version 7, the `whitelistUrls` and `blacklistUrls` are no longer working. They are renamed to `allowUrls` and `denyUrls` respectively, please update your code if you are using those options.

Since this version of the plugin we no longer bundle the ES5 versions of the Browser SDK by default, which means that if you still need to support older browsers (Internet Explorer 11 mainly) you need to enable the new `WP_SENTRY_BROWSER_USE_ES5_BUNDLES` option by setting it to `true`. Enabling the ES5 bundles will also loads an externally hosted polyfill library for the needed polyfills.

If you are still on a WP Sentry version older than version 5.0.0 don't forget to see the Browser SDK [v7 migration guide](https://github.com/getsentry/sentry-javascript/blob/7.0.0/MIGRATION.md#upgrading-from-6x-to-7x).

* Browser: Bundle ES6 compiled browser SDK by default
* Browser: Introduce new `WP_SENTRY_BROWSER_USE_ES5_BUNDLES` flag to enable ES5 bundles + external hosted polyfill library
* Browser: Fix broken `whitelistUrls` and `blacklistUrls` which are now called `allowUrls` and `denyUrls` options (thanks @dr5hn)

= 5.2.0 =

* Update PHP SDK to version 3.7.0
* Update Sentry Browser to version 7.10.0
* Fix potential error when using `wp_sentry_options` filter when no DSN is set

= 5.1.0 =

* Update PHP SDK to version 3.6.0
* Update Sentry Browser to version 7.2.0

= 5.0.0 =

Note: This is a *breaking release* for the Browser/JavaScript SDK, please test it well and read the migration guides if applicable before upgrading!
Note: The v7 version of the JavaScript SDK requires a [self-hosted](https://develop.sentry.dev/self-hosted/) version of Sentry 20.6.0 or higher. If you are using a version of self-hosted Sentry (aka onpremise) older than 20.6.0 then you will need to [upgrade](https://develop.sentry.dev/self-hosted/releases/).

Version 7 of the Sentry JavaScript SDK brings a variety of features and fixes including bundle size and performance improvements, brand new integrations, support for the attachments API, and key bug fixes.
This release does not change or remove any top level public API methods (captureException, captureMessage), and only requires changes to certain configuration options or custom clients/integrations/transports.
For detailed overview of all the changes, please see the [v7 migration guide](https://github.com/getsentry/sentry-javascript/blob/7.0.0/MIGRATION.md#upgrading-from-6x-to-7x).

* Update Sentry Browser to version 7.1.1

= 4.18.0 =

* Update PHP SDK to version 3.5.0

= 4.17.0 =

* Fix Sentry Browser context (thanks @giilby)
* Update Sentry Browser to version 6.19.7

= 4.16.0 =

* Update Sentry Browser to version 6.19.3
* Bump version of bundled `guzzlehttp/psr7` dependency (https://github.com/advisories/GHSA-q7rv-6hp3-vh96)

= 4.15.0 =

* Update PHP SDK to version 3.4.0
* Update Sentry Browser to version 6.18.2

= 4.14.0 =

* Update Sentry Browser to version 6.18.1 ([thanks @arjvand](https://github.com/stayallive/wp-sentry/pull/124))

= 4.13.1 =

* The plugin is now tested on WordPress 5.9

= 4.13.0 =

* Update PHP SDK to version 3.3.7
* Update Sentry Browser to version 6.17.2
* Fix possible undefined index notice on admin page

= 4.12.0 =

* Update PHP SDK to version 3.3.6
* Use `WPINC` constant instead of hardcoded path to `wp-includes`

= 4.11.0 =

* Update PHP SDK to version 3.3.5
* Update Sentry Browser to version 6.16.1

= 4.10.3 =

* Fix possible composer autoloader conflict with Sentry SDK helpers file

= 4.10.2 =

* Fix problem loading plugin in `wp-config.php` with browser DSN enabled

= 4.10.1 =

* Fix `Interface 'Stringable' not found` on PHP < 8.0
* Fix problem loading plugin in `wp-config.php` without `WP_SENTRY_VERSION` defined

= 4.10.0 =

* Allow loading the plugin from `wp-config.php` before WordPress is loaded ([see documentation](https://github.com/stayallive/wp-sentry#loading-sentry-before-wordpress)) (thanks @ocean90)
* Add support for modifying the Sentry PHP SDK `ClientBuilder` builder ([see documentation](https://github.com/stayallive/wp-sentry#modifying-the-php-sdk-clientbuilder-or-options-before-initialization))

= 4.9.0 =

* Update Sentry Browser to version 6.15.0

= 4.8.0 =

* Update PHP SDK to version 3.3.4
* Update Sentry Browser to version 6.14.1

= 4.7.0 =

* Update PHP SDK to version 3.3.3
* Update Sentry Browser to version 6.13.2

= 4.6.0 =

* Update PHP SDK to version 3.3.2
* Update Sentry Browser to version 6.11.0

= 4.5.0 =

* Update Sentry Browser to version 6.8.0

= 4.4.1 =

* Update PHP SDK to version 3.3.1
* Update Sentry Browser to version 6.7.2

= 4.4.0 =

* Update PHP SDK to version 3.3.0
* Update Sentry Browser to version 6.7.0

= 4.3.0 =

* Update PHP SDK to version 3.2.1
* Update Sentry Browser to version 6.3.4

= 4.2.0 =

* Update PHP SDK to version 3.1.5
* Update Sentry Browser to version 6.2.0

= 4.1.1 =

* Allow overwriting the integrations array using the JavaScript `wp_sentry_hook` callback

= 4.1.0 =

* Add `WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE` option to enable browser performance tracing
* Update PHP SDK to version 3.1.4
* Update Sentry Browser to version 6.1.0

= 4.0.1 =

Rereleased 4.0.0 to fix issue with missing dependencies folder, see 4.0.0 changelog for changes!

= 4.0.0 =

Note: This is a *breaking release* for the PHP SDK, please test it well and read the migration guides if applicable before upgrading!

If you are doing anything more than just have this plugin installed and a DSN defined on your wp-config.php, check out the upgrade docs:

- PHP SDK: https://github.com/getsentry/sentry-php/blob/master/UPGRADE-3.0.md

Becasue of the upgrade to the 3.x version of the PHP SDK this plugin now has the requirement that it runs on at least PHP 7.2, for older PHP versions stick to version 3.x.

* Drop PHP 7.1 support and add PHP 8.0 support
* Update PHP SDK to version 3.1.3
* Update Sentry Browser to version 6.0.3

= 3.11.1 =

- Fix missing files in the release

= 3.11.0 =

- Update PHP SDK to version 2.5.0
- Update Sentry Browser to version 5.27.3

= 3.10.0 =

- Update PHP SDK to version 2.4.2
- Update Sentry Browser to version 5.20.0

= 3.9.0 =

- Update PHP SDK to version 2.4.1
- Update Sentry Browser to version 5.19.2

= 3.8.0 =

- Update Sentry Browser to version 5.18.1
- Admin test page will now show for users with the `activate_plugins` capability (instead of `install_plugins`) to support sites that have `install_plugins` disabled globally
- Rename `public/sentry-browser-<Sentry Browser version>.min.js` to `public/wp-sentry-browser.min.js`, this change should have no user impact unless you manually include that file
- Renamed `WP_SENTRY_DSN` to `WP_SENTRY_PHP_DSN`, both will be supported but the latter is preferred because of it's more descriptive name
- Renamed `WP_SENTRY_PUBLIC_DSN` to `WP_SENTRY_BROWSER_DSN`, both will be supported but the latter is preferred because of it's more descriptive name

= 3.7.0 =

- Update PHP SDK to version 2.4.0
- Update Sentry Browser to version 5.17.0

= 3.6.0 =

- Update Sentry Browser to version 5.15.5
- Added a way to filter the Browser SDK options and/or disable loading the Browser SDK using JavaScript (https://github.com/stayallive/wp-sentry/blob/v3.6.0/README.md#advanced-client-side-hook)

= 3.5.1 =

- Fix scope data (user context & tags etc.) being lost when set before the `after_setup_theme` hook

= 3.5.0 =

- Remove undocumented `WP_SENTRY_PROJECT_ROOT` option
- Set a default for `prefixes` to get cleaner file paths on PHP stack traces

= 3.4.6 =

- Fix the Monolog namespace being incorrectly scoped causing errors when using the `\Sentry\Monolog\Handler` class

= 3.4.5 =

- Fix `Call to undefined function getallheaders()` error on PHP versions below 7.3

= 3.4.4 =

- Remove extra files from WordPress plugin release
- Fix fatal error caused by composer autoloader when including Sentry sources in other composer autoloader

= 3.4.3 =

- Fix fatal error caused by composer autoloader conflicts with other plugins

= 3.4.2 =

- Add button to admin page to test JavaScript integration

= 3.4.1 =

Important: In this release we start using a process to prefix our dependencies to prevent conflicts with other WordPress plugins.
This should cause no problems, but if they do please open up a support ticket or GitHub issue with details about your environment.

- Fixes issue in build process causing missing files in 3.4.0

= 3.4.0 =

Important: In this release we start using a process to prefix our dependencies to prevent conflicts with other WordPress plugins.
This should cause no problems, but if they do please open up a support ticket or GitHub issue with details about your environment.

- Update PHP SDK to version 2.3.2
- Update Sentry Browser to version 5.15.4

= 3.3.0 =

- Update PHP SDK to version 2.3.1
- Update Sentry Browser to version 5.12.4

= 3.2.0 =

- Add admin page (Tools > WP Sentry) to test if the Sentry integration is enabled and working (props @federicobond)

= 3.1.0 =

- Update PHP SDK to version 2.2.6
- Update Sentry Browser to version 5.10.2

= 3.0.4 =

- Fixed error when `WP_SENTRY_VERSION` is not defined and theme version returns `false`.
- Update PHP SDK to version 2.2.2

= 3.0.3 =

- Use the ABSPATH constant as default project root.

= 3.0.2 =

- Show a notice that we only support PHP 7.1+ instead of letting 7.1 code break the site.

= 3.0.1 =

- Just a version bump because a version number was not correctly updated.

= 3.0.0 =

Note: This is a *breaking release* for both the PHP SDK and the Browser SDK, please test it well and read the migration guides if applicable before upgrading!

If you are doing anything more than just have this plugin installed and a DSN defined on your wp-config.php, check out the upgrade docs:

- PHP SDK: https://github.com/getsentry/sentry-php/blob/master/UPGRADE-2.0.md
- Browser SDK: https://github.com/getsentry/sentry-javascript/blob/master/MIGRATION.md#upgrading-from-4x-to-5x

Becasue of the upgrade to the 2.x version of the PHP SDK this plugin now has the requirement that it runs on at least PHP 7.1, for older PHP versions stick to version 2.x.

* Update PHP SDK to version 2.2.1
* Update Sentry Browser to version 5.6.3

= 2.8.0 =

* Update Sentry Browser to version 4.6.6

= 2.7.2 =

* Remove unneeded files from the plugin download

= 2.7.1 =

* Fix IE compatibility for JS integration
* Update Sentry Browser to version 4.5.0

= 2.7.0 =

This release _might_ contain breaking changes if you are using the `wp_sentry_public_options` filter.

* Update `wp_sentry_public_options` filter to support the Sentry Browser SDK better ([#28](https://github.com/stayallive/wp-sentry/pull/28))
* Update Sentry Browser to version 4.4.2
* Update Raven PHP to version 1.10.0

= 2.6.1 =

* Update Sentry Browser to version 4.3.2

= 2.6.0 =

If you are doing custom calls to Sentry from your front-end make sure you check out the new docs and migration instructions: https://github.com/getsentry/sentry-javascript/releases/tag/4.0.0

If you are not doing anything custom to the JS side of the SDK you can safely upgrade to this version.

* Upgrade Raven JS to Sentry Browser version 4.2.3
* Tested on WordPress 5.0

= 2.5.0 =

* Update Raven JS to version 3.27.0
* Update Raven PHP to version 1.9.2

= 2.4.0 =

* Update Raven JS to version 3.26.3
* Update Raven PHP to version 1.9.1

This version allows the usage of the new private key-less DSN introduced in Sentry 9.

= 2.3.0 =

* Update Raven JS to version 3.24.2
* Update Raven PHP to version 1.9.0

= 2.2.0 =

* Change minimum PHP requirement to 5.4

= 2.1.5 =

* "Fix" PHP 5.3 support (this will be the last version supporting PHP 5.3)
* Update Raven JS to version 3.22.0

= 2.1.4 =

* Update Raven JS to version 3.21.0
* Update Raven PHP to version 1.8.2

= 2.1.3 =

* Tested up to WordPress 4.9
* Update Raven PHP to version 1.8.1

= 2.1.2 =

* Update Raven PHP to version 1.8.0

= 2.1.0 =

* Switch to a composer based plugin setup (@LeoColomb)
* Update Raven JS to version 3.19.1

= 2.0.18 =

* Update Raven PHP to version 1.7.1 (@ikappas)
* Update Raven JS to version 3.17.0 (@ikappas)

= 2.0.17 =

* Update Raven JS to version 3.16.0
* Update documentation on how to use as a mu-plugin

= 2.0.16 =

* Update Raven JS to version 3.15.0 (@hjanuschka)
* Update Raven PHP to version 1.7.0

= 2.0.15 =

* Update Raven JS to version 3.13.1

= 2.0.14 =

* Update Raven JS to version 3.13.0

= 2.0.13 =

* Update Raven JS to version 3.12.0

= 2.0.12 =

* Allow the `error_types` option to be configured from `wp-config.php`
* Fire the `wp_sentry_options` after the `after_setup_theme` action if it's defined

= 2.0.11 =

* Update Raven JS to version 3.11.0

= 2.0.10 =

* Update Raven JS to version 3.10.0 (@ikappas)

= 2.0.9 =

* Update Raven PHP to version 1.6.2 (@mckernanin)

= 2.0.8 =

* Fix setting the context on the JS SDK

= 2.0.7 =

* Update Raven JS to version 3.9.1

= 2.0.6 =

* Update Raven JS to version 3.9.0

= 2.0.4 =

* Re-release to fix SVN issues

= 2.0.0 =

* Complete rewrite of the plugin for better integration (@ikappas)
* Updated Raven JS tracker to version 3.8.0 (@ikappas)

= 1.0.1 =

* Fix WP_SENTRY_VERSION already defined error (@ikappas)

= 1.0.0 =

* Initital release

== Contributors ==

See: [github.com/stayallive/wp-sentry/graphs/contributors](https://github.com/stayallive/wp-sentry/graphs/contributors)
