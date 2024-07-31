<?php

$options = getopt( 'v:', [ 'version:' ] );

$version = $options['v'] ?? $options['version'] ?? null;

if ( empty( $version ) ) {
	echo "No version specified!" . PHP_EOL;

	return;
}

function writeRemoteToTargetForVersion( string $remote, string $target, string $version ): void {
	$contents = file_get_contents( $remote = sprintf( $remote, $version ) );

	if ( $contents === false ) {
		echo "Unable to download remote using: {$remote}" . PHP_EOL;

		return;
	}

	// Strip out the source mapping URL since we don't bundle that file
	$contents = trim( preg_replace( '/^\/\/# sourceMappingURL=.*$/m', '', $contents ) );

	file_put_contents( $target, $contents );
}

$bundleModifiers = [
	null,
	'replay',
	'tracing',
	'tracing.replay',
];

foreach ( $bundleModifiers as $bundle ) {
	$cdnName   = $bundle ? "bundle.{$bundle}.min.js" : 'bundle.min.js';
	$localName = $bundle ? "wp-sentry-browser.{$bundle}.min.js" : 'wp-sentry-browser.min.js';

	writeRemoteToTargetForVersion(
		"https://browser.sentry-cdn.com/%s/{$cdnName}",
		__DIR__ . "/../public/{$localName}",
		$version
	);
}

writeRemoteToTargetForVersion(
	'https://browser.sentry-cdn.com/%s/feedback.min.js',
	__DIR__ . '/../public/wp-sentry-browser.feedback.min.js',
	$version
);
