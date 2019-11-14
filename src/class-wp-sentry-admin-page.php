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
	 * Get the sentry tracker instance.
	 *
	 * @return \WP_Sentry_Admin_Page
	 */
	public static function get_instance(): WP_Sentry_Admin_Page {
		return self::$instance ?: self::$instance = new self();
	}

	/**
	 * Holds the Sentry tracker.
	 *
	 * @var WP_Sentry_Php_Tracker
	 */
	public $tracker;

	/**
	 * WP_Sentry_Admin_Page constructor.
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/** 
	 * Setup the admin menu page.
	 */
	public function admin_menu() {
		add_management_page(
			'WP Sentry',
			'WP Sentry',
			'install_plugins',
			'wp-sentry',
			array( $this, 'render_admin_page' )
		);
	}

	private function send_test_event(): bool {
		$client = $this->tracker->get_client();
		$id = $client->captureMessage( 'This is a test message from WP Sentry' );
		return $id != null;
	}

	/**
	 * Render the admin page.
	 */
	public function render_admin_page() {
		$test_event_sent = false;
		$test_event_result = false;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$test_event_sent = true;
			$test_event_success = $this->send_test_event();
		}

		$options = $this->tracker->get_default_options();
?>
<div class="wrap">
	<h1>WP Sentry</h1>

	<?php if ($test_event_sent): ?>
		<?php if ($test_event_success): ?>
			<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Test event sent successfully!', 'wp-sentry' ); ?></p>
			</div>
		<?php else: ?>
			<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Failed to send test event. Check your configuration.', 'wp-sentry' ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row">
					<label for="wp-sentry-version"><?php esc_html_e( 'Release', 'wp-sentry' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" readonly value="<?php echo esc_html( $options['release'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="wp-sentry-version"><?php esc_html_e( 'Environment', 'wp-sentry' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" readonly value="<?php echo esc_html( $options['environment'] ); ?>" />
				</td>
			</tr>
		</tbody>
	</table>

	<form method="post" action="<?php echo admin_url( 'tools.php?page=wp-sentry' ); ?>">
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Send test event', 'wp-sentry' ) ?>">
		</p>
	</form>
</div>
<?php
	}

}
