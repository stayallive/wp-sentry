<?php

use Sentry\Breadcrumb;
use Sentry\SentrySdk;

/**
 * WordPress Sentry SQL Tracker.
 */
final class WP_Sentry_Sql_Tracker {
	use WP_Sentry_Resolve_Environment;

	/**
	 * Holds the class instance.
	 *
	 * @var WP_Sentry_Sql_Tracker
	 */
	private static $instance;

	/**
	 * Get the sentry tracker instance.
	 *
	 * @return WP_Sentry_Sql_Tracker
	 */
	public static function get_instance(): WP_Sentry_Sql_Tracker {
		return self::$instance ?: self::$instance = new self;
	}

	/**
	 * WP_Sentry_Sql_Tracker constructor.
	 */
	protected function __construct() {
		add_action( 'log_query_custom_data', [ $this, 'filter_log_query_custom_data' ], 10, 5 );
	}

	/**
	 * Filter for "log_query_custom_data"
	 *
	 * @param array $query_data Custom query data.
	 * @param string $query The query's SQL.
	 * @param float $query_time Total time spent on the query, in seconds.
	 * @param string $query_callstack Comma-separated list of the calling functions.
	 * @param float $query_start Unix timestamp of the time at the start of the query.
	 *
	 * @see wpdb::log_query()
	 *
	 */
	public function filter_log_query_custom_data( $query_data, $query, $query_time, $query_callstack, $query_start ): void {
		SentrySdk::getCurrentHub()->addBreadcrumb( new Breadcrumb(
			Breadcrumb::LEVEL_INFO,
			Breadcrumb::TYPE_DEFAULT,
			'sql.query',
			$query,
			[
				'executionTimeMs' => round( $query_time * 1000, 2 ),
			]
		) );
	}
}
