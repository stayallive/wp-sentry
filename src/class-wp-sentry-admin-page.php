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
	 * @return WP_Sentry_Admin_Page
	 */
	public static function get_instance(): WP_Sentry_Admin_Page {
		return self::$instance ?: self::$instance = new self;
	}

	/**
	 * WP_Sentry_Admin_Page constructor.
	 */
	protected function __construct() {
		add_action( 'init', function () {
			if ( ! is_admin() ) {
				return;
			}

			add_action( 'admin_menu', [ $this, 'admin_menu' ] );
			add_action( 'network_admin_menu', [ $this, 'network_admin_menu' ] );
		} );
	}

	/**
	 * Setup the admin menu page.
	 */
	public function admin_menu(): void {
		if ( is_plugin_active_for_network( plugin_basename( WP_SENTRY_PLUGIN_FILE ) ) ) {
			return;
		}

		add_management_page(
			'WP Sentry test',
			'WP Sentry test',
			'activate_plugins',
			'wp-sentry',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Setup the network admin menu page.
	 */
	public function network_admin_menu(): void {
		if ( ! is_plugin_active_for_network( plugin_basename( WP_SENTRY_PLUGIN_FILE ) ) ) {
			return;
		}

		global $submenu;

		// Network admin has no tools section so we add it ourselfs
		add_menu_page(
			'',
			'Tools',
			'activate_plugins',
			'wp-sentry-tools-menu',
			'',
			'dashicons-admin-tools',
			22
		);

		add_submenu_page(
			'wp-sentry-tools-menu',
			'WP Sentry test',
			'WP Sentry test',
			'activate_plugins',
			'wp-sentry',
			[ $this, 'render_admin_page' ]
		);

		// Remove the submenu item crate by `add_menu_page` that links to `wp-sentry-tools-menu` which does not exist
		if ( ! empty( $submenu['wp-sentry-tools-menu'][0] ) && $submenu['wp-sentry-tools-menu'][0][2] === 'wp-sentry-tools-menu' ) {
			unset( $submenu['wp-sentry-tools-menu'][0] );
		}
	}

	/**
	 * Try to send a test even to Sentry.
	 *
	 * @return string|null
	 */
	private function send_test_event(): ?string {
		$tracker = WP_Sentry_Php_Tracker::get_instance();

		if ( ! empty( $tracker->get_dsn() ) ) {
			return $tracker->get_client()->captureMessage( 'This is a test message sent from the Sentry WP PHP integration.' );
		}

		return null;
	}

	/**
	 * Try to send a test even to Sentry.
	 *
	 * @return string|null
	 */
	private function send_test_exception(): ?string {
		$exception = $this->generateTestException( 'wp sentry test', [ 'foo' => 'bar' ] );

		$tracker = WP_Sentry_Php_Tracker::get_instance();

		if ( ! empty( $tracker->get_dsn() ) ) {
			return $tracker->get_client()->captureException( $exception );
		}

		return null;
	}

	/**
	 * Generate a test exception to send to Sentry.
	 *
	 * @param string $command
	 * @param array  $arg
	 *
	 * @return \Exception
	 */
	private function generateTestException( string $command, array $arg ): ?Exception {
		try {
			throw new Exception( 'This is a test exception sent from the Sentry WP PHP integration.' );
		} catch ( Exception $ex ) {
			return $ex;
		}
	}

	/**
	 * Render the admin page.
	 */
	public function render_admin_page(): void {
		$test_event_sent = false;
		$test_event_id   = null;

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			if ( isset( $_POST['wp-sentry-send-test-event-php'] ) ) {
				$test_event_sent = true;
				$test_event_id   = $this->send_test_event();
			} elseif ( isset( $_POST['wp-sentry-send-test-exception-php'] ) ) {
				$test_event_sent = true;
				$test_event_id   = $this->send_test_exception();
			}
		}

		$enabled_for_js = ! empty( WP_Sentry_Js_Tracker::get_instance()->get_dsn() );

		$browser_traces_sample_rate = defined( 'WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE
			: 0.0;

		$js_tracing_enabled = $enabled_for_js && $browser_traces_sample_rate > 0.0;

		$browser_replays_session_sample_rate = defined( 'WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE
			: 0.0;

		$browser_replays_on_error_sample_rate = defined( 'WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE' )
			? (float) WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE
			: 0.0;

		$js_session_replays_enabled = $enabled_for_js && ( $browser_replays_session_sample_rate > 0.0 || $browser_replays_on_error_sample_rate > 0.0 );

		$enabled_for_php = ! empty( WP_Sentry_Php_Tracker::get_instance()->get_dsn() );

		$options = WP_Sentry_Php_Tracker::get_instance()->get_default_options();

		$uses_scoped_autoloader = defined( 'WP_SENTRY_SCOPED_AUTOLOADER' ) && WP_SENTRY_SCOPED_AUTOLOADER;

		?>
		<div class="wrap">
			<h1>WP Sentry</h1>

			<div class="notice notice-success is-dismissible hidden" id="sentry-test-event-js-success">
				<p><?php echo translate( 'JavaScript test sent successfully, event ID: <code id="sentry-test-event-js-id"></code>!', 'wp-sentry' ); ?></p>
			</div>

			<div class="notice notice-error is-dismissible hidden" id="sentry-test-event-js-error">
				<p><?php esc_html_e( 'JavaScript failed to send test. Check your configuration to make sure your DSN is set correctly.', 'wp-sentry' ); ?></p>
			</div>

			<?php if ( $test_event_sent ): ?>
				<?php if ( $test_event_id !== null ): ?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo translate( "PHP test sent successfully, event ID: <code>{$test_event_id}</code>!", 'wp-sentry' ); ?></p>
					</div>
				<?php else: ?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'PHP failed to send test. Check your configuration to make sure your DSN is set correctly.', 'wp-sentry' ); ?></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<table class="form-table" role="presentation">
				<tbody>
				<tr>
					<th><?php esc_html_e( 'Enabled', 'wp-sentry' ); ?></th>
					<td>
						<fieldset>
							<label title="<?php echo $uses_scoped_autoloader ? 'Using scoped vendor (plugin build)' : 'Using regular vendor (composer)'; ?>">
								<input name="wp-sentry-php-enabled" type="checkbox" id="wp-sentry-php-enabled" value="0" <?php echo $enabled_for_php ? 'checked="checked"' : '' ?> readonly disabled>
								<?php esc_html_e( 'PHP', 'wp-sentry' ); ?>
							</label>
						</fieldset>
						<?php if ( ! $enabled_for_php ): ?>
							<p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_PHP_DSN</code> contains a valid DSN.', 'wp-sentry' ); ?>
							</p>
							<br>
						<?php endif; ?>

						<fieldset>
							<label>
								<input name="wp-sentry-js-enabled" type="checkbox" id="wp-sentry-js-enabled" value="0" <?php echo $enabled_for_js ? 'checked="checked"' : '' ?> readonly disabled>
								<?php esc_html_e( 'JavaScript', 'wp-sentry' ); ?>
							</label>
						</fieldset>
						<?php if ( ! $enabled_for_js ): ?>
							<p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_BROWSER_DSN</code> contains a valid DSN.', 'wp-sentry' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Performance Monitoring', 'wp-sentry' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input name="wp-sentry-js-tracing-enabled" type="checkbox" id="wp-sentry-js-tracing-enabled" value="0" <?php echo $js_tracing_enabled ? 'checked="checked"' : '' ?> readonly disabled>
								<?php esc_html_e( 'JavaScript', 'wp-sentry' ); ?>
							</label>
						</fieldset>
						<?php if ( ! ( $js_tracing_enabled ) ): ?>
							<p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE</code> is set.', 'wp-sentry' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Session Replays', 'wp-sentry' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input name="wp-sentry-js-session-replays-enabled" type="checkbox" id="wp-sentry-js-session-replays-enabled" value="0" <?php echo $js_session_replays_enabled ? 'checked="checked"' : '' ?> readonly disabled>
								<?php esc_html_e( 'JavaScript', 'wp-sentry' ); ?>
							</label>
						</fieldset>
						<?php if ( ! ( $js_session_replays_enabled ) ): ?>
							<p class="description">
								<?php echo translate( 'To enable make sure <code>WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE</code> or <code>WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE</code> is set.', 'wp-sentry' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wp-sentry-release"><?php esc_html_e( 'Release (version)', 'wp-sentry' ); ?></label>
					</th>
					<td>
						<input id="wp-sentry-release" type="text" class="regular-text code" readonly name="wp-sentry-release" value="<?php echo esc_html( $options['release'] ?? '' ); ?>" placeholder="[no value set]"/>
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
						<input id="wp-sentry-environment" type="text" class="regular-text code" readonly name="wp-sentry-environment" value="<?php echo esc_html( $options['environment'] ?? '' ); ?>" placeholder="[no value set]"/>
						<p class="description">
							<?php echo translate( 'Change this value by defining <code>WP_SENTRY_ENV</code> or <code>WP_ENVIRONMENT_TYPE</code> (WordPress 5.5+).', 'wp-sentry' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Test PHP integration', 'wp-sentry' ); ?></label>
					</th>
					<td>
						<form method="post">
							<input type="submit" name="wp-sentry-send-test-event-php" class="button" value="<?php esc_html_e( 'Send PHP test event', 'wp-sentry' ) ?>" <?php echo $enabled_for_php ? '' : 'disabled'; ?>>
							<input type="submit" name="wp-sentry-send-test-exception-php" class="button" value="<?php esc_html_e( 'Send PHP test exception', 'wp-sentry' ) ?>" <?php echo $enabled_for_php ? '' : 'disabled'; ?>>
						</form>
						<?php if ( ! $enabled_for_php ): ?>
							<p class="description">
								<?php echo translate( 'The PHP integration must be enabled to send a test event.', 'wp-sentry' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Test JavaScript integration', 'wp-sentry' ); ?></label>
					</th>
					<td>
						<form method="post">
							<input type="button" id="wp-sentry-send-test-event-js" class="button" value="<?php esc_html_e( 'Send JavaScript test event', 'wp-sentry' ) ?>" <?php echo $enabled_for_js ? '' : 'disabled'; ?>>
							<input type="button" id="wp-sentry-send-test-error-js" class="button" value="<?php esc_html_e( 'Send JavaScript test error', 'wp-sentry' ) ?>" <?php echo $enabled_for_js ? '' : 'disabled'; ?>>
						</form>
						<?php if ( ! $enabled_for_js ): ?>
							<p class="description">
								<?php echo translate( 'The JavaScript integration must be enabled to send a test event.', 'wp-sentry' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<script>
            (function () {
                var testEventButton = document.getElementById('wp-sentry-send-test-event-js');
                var testErrorButton = document.getElementById('wp-sentry-send-test-error-js');

                testEventButton.addEventListener('click', function (e) {
                    e.preventDefault();

                    if (testEventButton.classList.contains('disabled')) {
                        return;
                    }

                    testEventButton.classList.add('disabled');

                    console.log('=> Sending a test message to Sentry...');

                    if (typeof Sentry === 'object' && typeof Sentry.captureMessage === 'function') {
                        var eventId = Sentry.captureMessage('This is a test message sent from the Sentry WP JavaScript integration.');

                        console.log(' > Sent message with event ID:', eventId);

                        if (typeof eventId === 'string' && eventId.length > 1) {
                            document.getElementById('sentry-test-event-js-id').textContent = eventId;
                            document.getElementById('sentry-test-event-js-success').classList.remove('hidden');

                            return;
                        }
                    }

                    console.error('!> Failed to sent a test message to Sentry');

                    document.getElementById('sentry-test-event-js-error').classList.remove('hidden');
                });

                testErrorButton.addEventListener('click', function (e) {
                    e.preventDefault();

                    if (testErrorButton.classList.contains('disabled')) {
                        return;
                    }

                    testErrorButton.classList.add('disabled');

                    console.log('=> Sending a test error to Sentry...');

                    if (typeof Sentry === 'object' && typeof Sentry.captureException === 'function') {
                        try {
                            wpSentryIntegrationTestError();
                        } catch (e) {
                            var eventId = Sentry.captureException(e);

                            console.log(' > Sent error with event ID:', eventId);

                            if (typeof eventId === 'string' && eventId.length > 1) {
                                document.getElementById('sentry-test-event-js-id').textContent = eventId;
                                document.getElementById('sentry-test-event-js-success').classList.remove('hidden');

                                return;
                            }
                        }
                    }

                    console.error('!> Failed to sent a test message to Sentry');

                    document.getElementById('sentry-test-event-js-error').classList.remove('hidden');
                });
            })();
		</script>
	<?php }
}
