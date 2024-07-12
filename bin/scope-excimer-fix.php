<?php

echo "\n";
echo "=> Fixing issues with Excimer classes being prefix by php-scoper...\n";

$files = glob( __DIR__ . '/../build/vendor/sentry/sentry/src/Profiling/*.php' );

foreach ( $files as $file ) {
	echo " > Fixing {$file}\n";

	file_put_contents(
		$file,
		preg_replace(
			'/\\\\WPSentry\\\\ScopedVendor\\\\Excimer/',
			'\Excimer',
			file_get_contents( $file )
		)
	);
}
