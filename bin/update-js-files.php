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

$modifiers = [
	null,
	'es5',
	'tracing',
	'tracing.es5',
	'replay',
	'tracing.replay',
];

foreach ( $modifiers as $bundle ) {
	$cdnName   = $bundle ? "bundle.{$bundle}.min.js" : 'bundle.min.js';
	$localName = $bundle ? "wp-sentry-browser.{$bundle}.min.js" : 'wp-sentry-browser.min.js';

	writeRemoteToTargetWithExtrasForVersion(
		"https://browser.sentry-cdn.com/%s/{$cdnName}",
		__DIR__ . "/../public/{$localName}",
		__DIR__ . "/../public/wp-sentry-browser.wp.js",
		$version
	);
}
