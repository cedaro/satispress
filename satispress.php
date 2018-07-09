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
 * Release Asset: true
 * Requires PHP: 7.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\Container;
use SatisPress\ServiceProvider;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @var string
 */
const VERSION = '0.3.0-dev';

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

// Autoload mapped classes.
spl_autoload_register( __NAMESPACE__ . '\autoloader_classmap' );

// Load the WordPress plugin administration API.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Create a container and register a service provider.
$container = new Container();
$container->register( new ServiceProvider() );

// Initialize the plugin and inject the container.
$plugin = plugin()
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __DIR__ . '/satispress.php' )
	->set_slug( 'satispress' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->set_container( $container );

$plugin
	->register_hooks( $container->get( 'hooks.activation' ) )
	->register_hooks( $container->get( 'hooks.deactivation' ) );

// Authentication handlers need to be registered early.
add_action( 'plugins_loaded', function() use ( $plugin, $container ) {
	$plugin->register_hooks( $container->get( 'hooks.authentication' ) );
}, 5 );

add_action( 'plugins_loaded', [ $plugin, 'compose' ] );
