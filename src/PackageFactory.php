<?php
/**
 * PackageFactory class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

namespace SatisPress;

use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\Theme;

/**
 * Simple Factory.
 *
 * @since 0.3.0
 */
final class PackageFactory {
	/**
	 * Simple factory.
	 *
	 * @since 0.3.0
	 *
	 * @param string $package_type Package type.
	 * @param string $slug         Package slug.
	 * @param string $cache_path   Base path to cache.
	 * @return Package|false Package or false if package type is not known.
	 */
	public function create( $package_type, $slug, $cache_path ) {
		$version_parser = new ComposerVersionParser();
		switch ( $package_type ) {
			case 'plugin':
				$package = new Plugin( $slug, $cache_path );
				$package->set_version_parser( $version_parser );
				return $package;
			case 'theme':
				$package = new Theme( $slug, $cache_path );
				$package->set_version_parser( $version_parser );
				return $package;
			default:
				return false;
		}
	}
}
