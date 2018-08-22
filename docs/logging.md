# Logging

SatisPress implements a [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/) for logging messages, but doesn't save them by default. To view messages, a logger implementing `Psr\Log\LoggerInterface` needs to be registered with the container.

## Example

The example below demonstrates how to retrieve the SatisPress container and register a new logger. It uses [Monolog](https://github.com/Seldaek/monolog) to send warning messages through PHP's `error_log()` handler:

```php
<?php
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Register the logger before SatisPress is composed.
 */
add_action( 'satispress_compose', function( $plugin, $container ) {
	$container['logger'] = function() {
		$logger = new Logger( 'satispress' );
		$logger->pushHandler( new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, LOGGER::WARNING ) );
		$logger->pushProcessor( new PsrLogMessageProcessor );

		return $logger;
	};
}, 10, 2 );
```

_Monolog should be required with Composer and the autoloader needs to be included before using it in your project._


[Back to Index](index.md)
