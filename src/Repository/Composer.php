<?php
/**
 * Composer repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\PackageFactory;

/**
 * Composer repository class.
 *
 * Transforms packages from a repository into Composer format for use in
 * packages.json.
 *
 * @since 0.3.0
 */
class Composer extends AbstractRepository implements PackageRepository {
	/**
	 * Package factory.
	 *
	 * @var PackageFactory
	 */
	protected $factory;

	/**
	 * Packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $packages;

	/**
	 * Create a Composer repository.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $packages Packages repository.
	 * @param PackageFactory    $factory  Package factory.
	 */
	public function __construct( PackageRepository $packages, PackageFactory $factory ) {
		$this->packages = $packages;
		$this->factory  = $factory;
	}

	/**
	 * Retrieve all packages in the repository.
	 *
	 * Converts packages to Composer packages.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function all(): array {
		$packages = [];

		foreach ( $this->packages->all() as $slug => $package ) {
			$packages[ $slug ] = $this->factory->create( 'composer' )
				->with_package( $package )
				->build();
		}

		return $packages;
	}
}
