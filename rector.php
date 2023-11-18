<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->importShortClasses(false);

	$rectorConfig->autoloadPaths([
		__DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
	]);

	$rectorConfig->skip([
		//  This should stop Rector changing callable arrays to $this->function in WP's add_*
		\Rector\Php81\Rector\Array_\FirstClassCallableRector::class
	]);

	$rectorConfig->paths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
		__DIR__ . '/views',
	]);

	// register a single rule
	$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

	// define sets of rules
	$rectorConfig->sets([
		LevelSetList::UP_TO_PHP_81,
	]);
};
