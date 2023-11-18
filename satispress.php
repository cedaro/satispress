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
 * Plugin URI: https://github.com/cedaro/satispress
 * Description: Generate a Composer repository from installed WordPress plugins and themes.
 * Version: 2.0.1
 * Author: Cedaro
 * Author URI: https://www.cedaro.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: satispress
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Network: true
 * GitHub Plugin URI: cedaro/satispress
 * Release Asset: true
 */

declare ( strict_types = 1 );

namespace SatisPress;

// Exit if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @var string
 */
const VERSION = '2.0.1';

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

// Display a notice and bail if dependencies are missing.
if ( ! function_exists( __NAMESPACE__ . '\autoloader_classmap' ) ) {
	require_once __DIR__ . '/src/functions.php';
	add_action( 'admin_notices', __NAMESPACE__ . '\display_missing_dependencies_notice' );
	return;
}

// Autoload mapped classes.
spl_autoload_register( __NAMESPACE__ . '\autoloader_classmap' );

// Load the WordPress plugin administration API.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Create a container and register a service provider.
$satispress_container = new Container();
$satispress_container->register( new ServiceProvider() );

// Initialize the plugin and inject the container.
$satispress = plugin()
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __DIR__ . '/satispress.php' )
	->set_slug( 'satispress' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->set_container( $satispress_container )
	->register_hooks( $satispress_container->get( 'hooks.activation' ) )
	->register_hooks( $satispress_container->get( 'hooks.deactivation' ) )
	->register_hooks( $satispress_container->get( 'hooks.authentication' ) );

add_action( 'plugins_loaded', [ $satispress, 'compose' ], 5 );
