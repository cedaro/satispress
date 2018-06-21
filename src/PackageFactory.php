<?php
/**
 * PackageFactory class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Simple Factory for creating specific Composer package objects.
 *
 * @since 0.3.0
 */
final class PackageFactory {
	/**
	 * Create a Composer package object.
	 *
	 * Typical objects returned are Plugin and Theme.
	 *
	 * @since 0.3.0
	 *
	 * @throws \Exception If package type not known.
	 *
	 * @param string $package_type Package type.
	 * @param string $slug         Package slug.
	 * @param string $cache_path   Base path to cache.
	 * @return Package|false Package or false if package type is not known.
	 */
	public function create( $package_type, $slug, $cache_path ) {
		$version_parser = new ComposerVersionParser();

		$class_name = 'SatisPress\PackageType\\' . ucfirst( $package_type );

		if ( class_exists( $class_name ) ) {
			return new $class_name( $slug, $cache_path, $version_parser );
		}

		throw new \Exception( 'Package type not found' );
	}
}
