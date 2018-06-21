<?php
/**
 * Package_Factory class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

namespace SatisPress;

/**
 * Simple Factory.
 *
 * @since 0.3.0
 */
final class Package_Factory {
	/**
	 * Simple factory.
	 *
	 * @since 0.3.0
	 *
	 * @param string $package    Package type.
	 * @param string $slug       Package slug.
	 * @param string $cache_path Base path to cache.
	 *
	 * @return [type] [description]
	 */
	public function create( $package, $slug, $cache_path ) {
		switch ( $package ) {
			case 'plugin':
				return new Package_Plugin( $slug, $cache_path );
			case 'theme':
				return new Package_Theme( $slug, $cache_path );
			default:
				return false;
		}
	}
}
