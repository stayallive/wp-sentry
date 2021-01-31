<?php

declare( strict_types=1 );

use Isolated\Symfony\Component\Finder\Finder;

$polyfillsBootstrap = Finder::create()
                            ->files()
                            ->in( __DIR__ . '/vendor/symfony/polyfill-*' )
                            ->name( 'bootstrap.php' )
                            ->name( 'bootstrap80.php' );

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
	], array_map(
		static function ( $file ) {
			return $file->getPathName();
		},
		iterator_to_array( $polyfillsBootstrap )
	) ),

	'whitelist-global-classes'   => true,
	'whitelist-global-constants' => true,
	'whitelist-global-functions' => true,
];
