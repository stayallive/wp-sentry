<?php

/**
 * WordPress Sentry Admin Page.
 */
final class WP_Sentry_Admin_Page {

	/**
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Admin_Page
	 */
	private static $instance;

	/**
	 * Get the Sentry admin page instance.
	 *
	 * @return \WP_Sentry_Admin_Page
	 */
	public static function get_instance(): WP_Sentry_Admin_Page {
		return self::$instance ?: self::$instance = new self();
	}

	/**
	 * WP_Sentry_Admin_Page constructor.
	 */
	protected function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Setup the admin menu page.
	 */
	public function admin_menu(): void {
		add_management_page(
			'WP Sentry test',
			'WP Sentry test',
			'install_plugins',
			'wp-sentry',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Try to send a test even to Sentry.
	 *
	 * @return string|null
	 */
	private function send_test_event(): ?string {
		$exception = $this->generateTestException( 'command name', [ 'foo' => 'bar' ] );

		$tracker = WP_Sentry_Php_Tracker::get_instance();

		if ( ! empty( $tracker->get_dsn() ) ) {
			return $tracker->get_client()->captureException( $exception );
		}

		return null;
	}

	/**
	 * Render the admin page.
	 */
	public function render_admin_page(): void {
		$test_event_sent = false;
		$test_event_id   = null;

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$test_event_sent = true;
			$test_event_id   = $this->send_test_event();
		}

		$enabled_for_js  = ! empty( WP_Sentry_Js_Tracker::get_instance()->get_dsn() );
		$enabled_for_php = ! empty( WP_Sentry_Php_Tracker::get_instance()->get_dsn() );

		$options = WP_Sentry_Php_Tracker::get_instance()->get_default_options();

		?>
        <div class="wrap">
            <h1>WP Sentry</h1>

			<?php if ( $test_event_sent ): ?>
				<?php if ( $test_event_id !== null ): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php echo translate( "Test event sent successfully, with ID: <code>{$test_event_id}</code>!", 'wp-sentry' ); ?></p>
                    </div>
				<?php else: ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php esc_html_e( 'Failed to send test event. Check your configuration to make sure your DSN is set correctly.', 'wp-sentry' ); ?></p>
                    </div>
				<?php endif; ?>
			<?php endif; ?>

            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th><?php esc_html_e( 'Enabled', 'wp-sentry' ); ?></th>
                    <td>
                        <fieldset>
                            <label for="wp-sentry-php-enabled">
                                <input name="wp-sentry-php-enabled" type="checkbox" id="wp-sentry-php-enabled" value="0" <?php echo $enabled_for_php ? 'checked="checked"' : '' ?> readonly>
								<?php esc_html_e( 'PHP', 'wp-sentry' ); ?>
                            </label>
                        </fieldset>
						<?php if ( ! $enabled_for_php ): ?>
                            <p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_DSN</code> contains a valid DSN.', 'wp-sentry' ); ?>
                            </p>
                            <br>
						<?php endif; ?>

                        <fieldset>
                            <label for="wp-sentry-js-enabled">
                                <input name="wp-sentry-js-enabled" type="checkbox" id="wp-sentry-js-enabled" value="0" <?php echo $enabled_for_js ? 'checked="checked"' : '' ?> readonly>
								<?php esc_html_e( 'Browser', 'wp-sentry' ); ?>
                            </label>
                        </fieldset>
						<?php if ( ! $enabled_for_js ): ?>
                            <p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_PUBLIC_DSN</code> contains a valid DSN.', 'wp-sentry' ); ?>
                            </p>
						<?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="wp-sentry-release"><?php esc_html_e( 'Release (version)', 'wp-sentry' ); ?></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text code" readonly name="wp-sentry-release" value="<?php echo esc_html( $options['release'] ); ?>"/>
                        <p class="description">
		                    <?php echo translate( 'Change this value by defining <code>WP_SENTRY_VERSION</code>.', 'wp-sentry' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="wp-sentry-environment"><?php esc_html_e( 'Environment', 'wp-sentry' ); ?></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text code" readonly name="wp-sentry-environment" value="<?php echo esc_html( $options['environment'] ); ?>"/>
                        <p class="description">
		                    <?php echo translate( 'Change this value by defining <code>WP_SENTRY_ENV</code>.', 'wp-sentry' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="wp-sentry-send-test-event"><?php esc_html_e( 'Test integration', 'wp-sentry' ); ?></label>
                    </th>
                    <td>
                        <form method="post">
                            <input type="submit" name="wp-sentry-send-test-event" class="button" value="<?php esc_html_e( 'Send test event', 'wp-sentry' ) ?>" <?php echo $enabled_for_php ? '' : 'disabled'; ?>>
                        </form>
						<?php if ( ! $enabled_for_php ): ?>
                            <p class="description">
								<?php echo translate( 'The PHP tracker must be activated to send a test event.', 'wp-sentry' ); ?>
                            </p>
						<?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
	<?php }

	/**
	 * Generate a test exception to send to Sentry.
	 *
	 * @param string $command
	 * @param array  $arg
	 *
	 * @return \Exception
	 */
	private function generateTestException( string $command, array $arg ): ?Exception {
		// Do something silly
		try {
			throw new Exception( 'This is a test exception sent from the Sentry WP SDK.' );
		} catch ( Exception $ex ) {
			return $ex;
		}
	}

}
