# WordPress [Sentry](https://sentry.io) (wp-sentry)

A (unofficial) WordPress plugin to report PHP errors and JavaScript errors to [Sentry](https://sentry.io).

## Usage

1. Install this plugin by cloning or copying this repository to your `wp-contents/plugins` folder
2. Activate the plugin through the WordPress admin interface
3. Configure your DSN as explained below, this plugin does not report anything by default

## Configuration

(Optionally) track PHP errors by adding this snippet to your `wp-config.php` and replace `DSN` with your actual DSN that you find in Sentry:

```php
define( 'WP_SENTRY_DSN', 'DSN' );
```

---

(Optionally) track JavaScript errors by adding this snippet to your `wp-config.php` and replace `PUBLIC_DSN` with your actual public DSN that you find in Sentry (**never use your private DSN**):

```php
define( 'WP_SENTRY_PUBLIC_DSN', 'PUBLIC_DSN' );
```

---

(Optionally) define a version of your site, by default the theme version will be used. This is used for tracking on which version of your site the error occurred, combined with release tracking this is a very powerfull feature.

```php
define( 'WP_SENTRY_VERSION', 'v1.0.0' );
```

(Optionally) define an environment of your site. Defaults to `unspecified`.

```php
define( 'WP_SENTRY_ENV', 'production' );
```