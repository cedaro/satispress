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
use SatisPress\PackageType\ComposerBuilder;
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
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	private $release_manager;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param ReleaseManager $release_manager Release manager.
	 */
	public function __construct( ReleaseManager $release_manager ) {
		$this->release_manager = $release_manager;
	}

	/**
	 * Create a package builder.
	 *
	 * @since 0.3.0
	 *
	 * @param string $package_type Package type.
	 * @return ComposerBuilder|PluginBuilder|ThemeBuilder|PackageBuilder Package builder instance.
	 */
	public function create( string $package_type ): PackageBuilder {
		switch ( $package_type ) {
			case 'composer':
				return new ComposerBuilder( new BasePackage() );
			case 'plugin':
				return new PluginBuilder( new Plugin(), $this->release_manager );
			case 'theme':
				return new ThemeBuilder( new Theme(), $this->release_manager );
		}

		return new PackageBuilder( new BasePackage() );
	}
}
