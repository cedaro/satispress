<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
	->withAutoloadPaths([
		__DIR__ . 'vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
	])
	->withRootFiles()
	->withPaths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
		__DIR__ . '/views',
	])
	->withSkip([
		// This should stop Rector changing callable arrays to $this->function in WP's add_*.
		FirstClassCallableRector::class,
	])
	->withRules([
		InlineConstructorDefaultToPropertyRector::class,
	])
	->withSets([
		LevelSetList::UP_TO_PHP_81,
	]);
