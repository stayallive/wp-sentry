<?php

declare( strict_types=1 );

use Isolated\Symfony\Component\Finder\Finder;

$symfonyPolyfills = ( static function (): array {
	$files = [];

	foreach (
		Finder::create()
		      ->files()
		      ->in( __DIR__ . '/vendor/symfony/polyfill-*' )
		      ->name( 'bootstrap.php' )
		      ->name( 'bootstrap80.php' ) as $bootstrap
	) {
		$files[] = $bootstrap->getPathName();
	}

	foreach (
		Finder::create()
		      ->files()
		      ->in( __DIR__ . '/vendor/symfony/polyfill-*/Resources/stubs' )
		      ->name( '*' ) as $stub
	) {
		$files[] = $stub->getPathName();
	}

	return $files;
} )();

return [
	'prefix' => 'WPSentry\\ScopedVendor',

	'finders' => [
		Finder::create()
		      ->files()
		      ->ignoreVCS( true )
		      ->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/' )
		      ->exclude( [
			      'doc',
			      'test',
			      'test_old',
			      'tests',
			      'Tests',
			      'vendor-bin',
		      ] )
		      ->in( 'vendor' ),
		Finder::create()->append( [
			'composer.json',
		] ),
	],

	'whitelist' => [
		'Sentry\\*',
		'Monolog\\*',
		'Symfony\\Polyfill\\*',
	],

	'files-whitelist' => array_merge( [
		'vendor/ralouphie/getallheaders/src/getallheaders.php',
	], $symfonyPolyfills ),

	'whitelist-global-classes'   => true,
	'whitelist-global-constants' => true,
	'whitelist-global-functions' => true,
];
