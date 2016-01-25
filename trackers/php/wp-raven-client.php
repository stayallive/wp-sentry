<?php

class WP_Raven_Client extends Raven_Client {

	public function __construct() {
		if ( ! defined( 'WP_SENTRY_DSN' ) || empty( WP_SENTRY_DSN ) ) {
			return;
		}

		$env = 'unspecified';

		if ( defined( 'WP_SENTRY_ENV' ) && ! empty( WP_SENTRY_ENV ) ) {
			$env = WP_SENTRY_ENV;
		}

		$theme   = wp_get_theme();
		$release = null;

		if ( $theme->exists() ) {
			$release = $theme->get( 'Version' );
		}

		parent::__construct( WP_SENTRY_DSN, [
			'release' => $release,
			'tags'    => [
				'environment' => $env,
			]
		] );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();

			if ($current_user instanceof WP_User) {
				$this->user_context( [
                    'id'    => $current_user->ID,
                    'name'  => $current_user->display_name,
                    'email' => $current_user->user_email
                ] );
			} else {
				$this->user_context( [
	                'id' => get_current_user_id(),
                ] );
			}
		}

		$this->setHandlers();
	}

	private function setHandlers() {
		$error_handler = new Raven_ErrorHandler( $this );
		$error_handler->registerErrorHandler();
		$error_handler->registerExceptionHandler();
		$error_handler->registerShutdownFunction();
	}

}
