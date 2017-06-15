=== WordPress Sentry ===
Contributors: stayallive
Tags: sentry, errors, tracking
Requires at least: 4.4
Tested up to: 4.8
Stable tag: trunk
License: MIT
License URI: https://github.com/stayallive/wp-sentry/blob/master/LICENSE.md

A (unofficial) WordPress plugin to report PHP errors and JavaScript errors to Sentry.

== Description ==
This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to Sentry and integrates with its release tracking.

It will auto detect authenticated users and add context where possible. All context/tags can be adjusted using filters mentioned below.

== Installation ==
1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Configure your DSN as explained below
2. Activate the plugin through the WordPress admin interface

**Note:** this plugin does not do anything by default and has no admin interface. A Sentry DSN must be configured.

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `DSN` with your actual DSN that you find in Sentry:

`define( 'WP_SENTRY_DSN', 'DSN' );`

(Optionally) set the error types the PHP tracker will track:

`define( 'WP_SENTRY_ERROR_TYPES', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_USER_DEPRECATED );`

**Note:** Remove or comment this constant to disable the PHP tracker.

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `PUBLIC_DSN` with your actual public DSN that you find in Sentry (**never use your private DSN**):

`define( 'WP_SENTRY_PUBLIC_DSN', 'PUBLIC_DSN' );`

**Note:** Remove or comment this constant to disable the JavaScript tracker.

(Optionally) define a version of your site; by default the theme version will be used. This is used for tracking at which version of your site the error occurred. When combined with release tracking this is a very powerful feature.

`define( 'WP_SENTRY_VERSION', 'v2.0.17’ );`

(Optionally) define an environment of your site. Defaults to `unspecified`.

`define( 'WP_SENTRY_ENV', 'production' );`

== Filters ==
This plugin provides the following filters to plugin/theme developers. For more information have a look at the README.md file.

Common to both trackers:
- `wp_sentry_user_context`

Specific to PHP tracker:

- `wp_sentry_dsn`
- `wp_sentry_options`
- `wp_sentry_send_data`

Specific to JS tracker:

- `wp_sentry_public_dsn`
- `wp_sentry_public_options`

== Changelog ==
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

stayallive (https://github.com/stayallive)
ikappas (https://github.com/ikappas)
mckernanin (https://github.com/mckernanin)
