=== wp-sentry ===
Contributors: stayallive
Tags: sentry,errors,tracking
Requires at least: 4.4
Tested up to: 4.6.1
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT

A (unofficial) WordPress plugin to report PHP errors and JavaScript errors to Sentry.

== Description ==
This plugin can report PHP errors (optionally) and JavaScript errors (optionally) to Sentry and integrates with it\'s release tracking.

== Installation ==
1. Install this plugin by cloning or copying this repository to your wp-contents/plugins folder
2. Activate the plugin through the WordPress admin interface
3. Configure your DSN as explained below, this plugin does not report anything by default

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `DSN` with your actual DSN that you find in Sentry:

`define( 'WP_SENTRY_DSN', 'DSN' );`

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `PUBLIC_DSN` with your actual public DSN that you find in Sentry (**never use your private DSN**):

`define( 'WP_SENTRY_PUBLIC_DSN', 'PUBLIC_DSN' );`

(Optionally) define a version of your site, by default the theme version will be used. This is used for tracking on which version of your site the error occurred, combined with release tracking this is a very powerfull feature.

`define( 'WP_SENTRY_VERSION', 'v1.0.0' );`

== Changelog ==
= 1.0 =

* Initital release
