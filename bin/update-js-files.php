<?php

$options = getopt( 'v:', [ 'version:' ] );

$version = $options['v'] ?? $options['version'] ?? null;

if ( empty( $version ) ) {
	echo "No version specified!" . PHP_EOL;

	return;
}

function writeRemoteToTargetWithExtrasForVersion( string $remote, string $target, string $extras, string $version ): void {
	$contents = file_get_contents( $remote = sprintf( $remote, $version ) );

	if ( $contents === false ) {
		echo "Unable to download remote using: {$remote}" . PHP_EOL;

		return;
	}

	// Strip out the source mapping URL since we don't bundle that file
	$contents = trim( preg_replace( '/^\/\/# sourceMappingURL=.*$/m', '', $contents ) );

	$contents .= "\n\n" . file_get_contents( $extras );

	file_put_contents( $target, $contents );
}

writeRemoteToTargetWithExtrasForVersion(
	'https://browser.sentry-cdn.com/%s/bundle.min.js',
	__DIR__ . '/../public/wp-sentry-browser.min.js',
	__DIR__ . '/../public/wp-sentry-browser.wp.js',
	$version
);

writeRemoteToTargetWithExtrasForVersion(
	'https://browser.sentry-cdn.com/%s/bundle.es5.min.js',
	__DIR__ . '/../public/wp-sentry-browser.es5.min.js',
	__DIR__ . '/../public/wp-sentry-browser.wp.js',
	$version
);

writeRemoteToTargetWithExtrasForVersion(
	'https://browser.sentry-cdn.com/%s/bundle.tracing.min.js',
	__DIR__ . '/../public/wp-sentry-browser-tracing.min.js',
	__DIR__ . '/../public/wp-sentry-browser-tracing.wp.js',
	$version
);

writeRemoteToTargetWithExtrasForVersion(
	'https://browser.sentry-cdn.com/%s/bundle.tracing.es5.min.js',
	__DIR__ . '/../public/wp-sentry-browser-tracing.es5.min.js',
	__DIR__ . '/../public/wp-sentry-browser-tracing.wp.js',
	$version
);
