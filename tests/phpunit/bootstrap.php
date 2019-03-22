<?php
declare ( strict_types = 1 );

use Cedaro\WP\Tests\TestSuite;

use function Cedaro\WP\Tests\get_current_suite;

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

define( 'SATISPRESS_TESTS_DIR', __DIR__ );
define( 'WP_PLUGIN_DIR', __DIR__ . '/Fixture/wp-content/plugins' );

if ( 'Unit' === get_current_suite() ) {
	return;
}

require_once dirname( __DIR__, 2 ) . '/vendor/antecedent/patchwork/Patchwork.php';

$suite = new TestSuite();

$GLOBALS['wp_tests_options'] = [
	'active_plugins'  => [ 'satispress/satispress.php' ],
	'timezone_string' => 'America/Los_Angeles',
];

$suite->addFilter( 'muplugins_loaded', function() {
	require dirname( __DIR__, 2 ) . '/satispress.php';
} );

$suite->bootstrap();
