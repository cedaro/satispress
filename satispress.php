<?php
/**
 * SatisPress
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: SatisPress
 * Plugin URI: https://github.com/blazersix/satispress
 * Description: Generate a Composer repository from installed WordPress plugins and themes.
 * Version: 0.2.2
 * Author: Blazer Six
 * Author URI: http://www.blazersix.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: satispress
 * Domain Path: /languages
 */

if ( ! defined( 'SATISPRESS_DIR' ) ) {
	/**
	 * Path directory path.
	 *
	 * @since 0.2.0
	 * @type string SATISPRESS_DIR
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
	 * @type string SATISPRESS_URL
	 */
	define( 'SATISPRESS_URL', plugin_dir_url( __FILE__ ) );
}

include( SATISPRESS_DIR . 'includes/functions.php' );

/**
 * Autoloader callback.
 *
 * Converts a class name to a file path and requires it if it exists.
 *
 * @since 0.2.0
 *
 * @param string $class Class name.
 */
function satispress_autoloader( $class ) {
	if ( 0 !== strpos( $class, 'SatisPress' ) ) {
		return;
	}

	$file = SATISPRESS_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}
spl_autoload_register( 'satispress_autoloader' );

SatisPress::instance();
