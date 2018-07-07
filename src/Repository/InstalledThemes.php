<?php
/**
 * Installed themes repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\PackageFactory;
use SatisPress\PackageType\Theme;
use WP_Theme;

/**
 * Installed themes repository class.
 *
 * @since 0.3.0
 */
class InstalledThemes extends AbstractRepository implements PackageRepository {
	/**
	 * Package factory.
	 *
	 * @var PackageFactory
	 */
	protected $factory;

	/**
	 * Create a repository.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageFactory $factory Package factory.
	 */
	public function __construct( PackageFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * Retrieve all installed themes.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function all(): array {
		$items = [];

		foreach ( wp_get_themes() as $slug => $theme ) {
			$package        = $this->build( $slug, $theme );
			$items[ $slug ] = $package;
		}

		return $items;
	}

	/**
	 * Build a theme.
	 *
	 * @since 0.3.0
	 *
	 * @param string   $slug  Theme slug.
	 * @param WP_Theme $theme WP theme instance.
	 * @return Theme
	 */
	protected function build( string $slug, WP_Theme $theme ): Theme {
		return $this->factory->create( 'theme' )
			->from_source( $slug, $theme )
			->build();
	}
}
