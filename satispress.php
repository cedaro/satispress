<?php
/**
 * SatisPress
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @license GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: SatisPress
 * Plugin URI: https://github.com/blazersix/satispress
 * Description: Generate a Composer repository from installed WordPress plugins and themes.
 * Version: 0.3.0-dev
 * Author: Blazer Six
 * Author URI: https://www.blazersix.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: satispress
 * Domain Path: /languages
 * GitHub Plugin URI: blazersix/satispress
 * Requires PHP: 5.6
 */

declare ( strict_types = 1 );

namespace SatisPress;

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

// Create a container and register a service provider.
$container = new Container();

// Initialize the plugin and inject the container.
$plugin = ( new Plugin() )
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __DIR__ . '/satispress.php' )
	->set_slug( 'satispress' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->set_container( new PsrContainer( $container ) );

add_action( 'plugins_loaded', [ $plugin, 'compose' ] );
