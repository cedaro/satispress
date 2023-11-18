<?php
/**
 * Package factory.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\PackageType\BasePackage;
use SatisPress\PackageType\PackageBuilder;
use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\PluginBuilder;
use SatisPress\PackageType\Theme;
use SatisPress\PackageType\ThemeBuilder;

/**
 * Factory for creating package builders.
 *
 * @since 0.3.0
 */
final class PackageFactory {
	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param ReleaseManager $release_manager Release manager.
	 */
	public function __construct(
     /**
      * Release manager.
      */
     private readonly ReleaseManager $release_manager
 )
 {
 }

	/**
	 * Create a package builder.
	 *
	 * @since 0.3.0
	 *
	 * @param string $package_type Package type.
	 * @return PluginBuilder|ThemeBuilder|PackageBuilder Package builder instance.
	 */
	public function create(string $package_type): PackageBuilder
 {
     return match ($package_type) {
         'plugin' => new PluginBuilder( new Plugin(), $this->release_manager ),
         'theme' => new ThemeBuilder( new Theme(), $this->release_manager ),
         default => new PackageBuilder( new BasePackage(), $this->release_manager ),
     };
 }
}
