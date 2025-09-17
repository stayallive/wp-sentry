<?php

/**
 * WordPress Sentry Action Scheduler Integration
 *
 * @internal This class is not part of the public API and may be removed or changed at any time.
 */
final class WP_Sentry_Action_Scheduler_Integration {

    /**
     * Holds the class instance.
     *
     * @var WP_Sentry_Action_Scheduler_Integration
     */
    private static $instance;

    /**
     * Get the Sentry admin page instance.
     *
     * @return WP_Sentry_Action_Scheduler_Integration
     */
    public static function get_instance(): WP_Sentry_Action_Scheduler_Integration {
        return self::$instance ?: self::$instance = new self;
    }

    /**
     * WP_Sentry_Action_Scheduler_Integration constructor.
     */
    protected function __construct() {
        if ( class_exists( 'WC' ) ) {
            add_action( 'action_scheduler_failed_execution', array( $this, 'handle_action_scheduler_failure'), 10, 3 );
        }
    }

    /**
     * Capture and send Action Scheduler failures to Sentry.
     *
     * @param int $action_id The action ID that failed.
     * @param \Throwable $e The exception that was thrown.
     * @param string $context The context in which the exception was thrown.
     * @return void
     */
    public function handle_action_scheduler_failure($action_id, $e, $context ) {
        wp_sentry_safe(
            function ( \Sentry\State\HubInterface $client ) use ( $action_id, $e, $context ) {
                $client->configureScope(
                    function ( \Sentry\State\Scope $scope ) use ( $action_id, $e, $context ) {
                        $scope->setExtras(
                            array(
                                'action_id' => $action_id,
                                'context'   => $context,
                            )
                        );
                    }
                );
                $client->captureException( $e );
            }
        );
    }

}