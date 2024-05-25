<?php

use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;

/**
 * @internal This class is not part of the public API and may be removed or changed at any time.
 */
class WP_Sentry_Tracing_Feature_DB {
	public function __construct() {
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

		add_filter( 'log_query_custom_data', [ $this, 'handle_log_query_custom_data' ], 10, 5 );
	}

	public function handle_log_query_custom_data( array $query_data, string $query, float $query_time, string $query_callstack, float $query_start ): array {
		$parentSpan = SentrySdk::getCurrentHub()->getSpan();

		// If there is no sampled span there is no need to handle the event
		if ( $parentSpan === null || ! $parentSpan->getSampled() ) {
			return $query_data;
		}

		$context = new SpanContext;
		$context->setOp( 'db.sql.query' );
		$context->setDescription( $query );
		$context->setStartTimestamp( $query_start );

		$parentSpan->startChild( $context )->finish( $query_start + $query_time );

		return $query_data;
	}
}
