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
 * Author URI: http://www.blazersix.com/
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: satispress
 * Domain Path: /languages
 */

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

require SATISPRESS_DIR . 'src/functions.php';

spl_autoload_register( __NAMESPACE__ .  '\\satispress_autoloader' );
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

	// Project namespace
	$prefix = 'SatisPress\\';

	$base_dir = SATISPRESS_DIR . 'src/';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );

	if ( 0 !== strncmp( $prefix, $class, $len ) ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Get the relative class name
	$relative_class = substr( $class, $len );

	// Replace the namespace prefix with the base directory, replace namespace separators
	// with directory separators in the relative class name, append with .php

	$file = $base_dir . 'class-' . \strtolower( \str_replace( '_', '-', \str_replace( '\\', '/', $relative_class ) ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	} else {
		die( $file );
	}
}

SatisPress::instance();
