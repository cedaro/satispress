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

if ( ! defined( 'SATISPRESS_DIR' ) ) {
	/**
	 * Path directory path.
	 *
	 * @since 0.2.0
	 * @var string SATISPRESS_DIR
	 */
	define( 'SATISPRESS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SATISPRESS_URL' ) ) {
	/**
	 * URL to the plugin's root directory.
	 *
	 * Includes trailing slash.
	 *
	 * @since 0.2.0
	 * @var string SATISPRESS_URL
	 */
	define( 'SATISPRESS_URL', plugin_dir_url( __FILE__ ) );
}

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

add_action( 'plugins_loaded', function() {
	( new Plugin() )->compose();
} );
