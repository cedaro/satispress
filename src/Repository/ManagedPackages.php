<?php
/**
 * Managed packages repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\Package;
use SatisPress\PackageFactory;

/**
 * Managed packages repository class.
 *
 * @since 3.0.0
 */
class ManagedPackages extends AbstractRepository implements PackageRepository {
	/**
	 * Package factory.
	 *
	 * @var PackageFactory
	 */
	protected $factory;

	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

	/**
	 * Create a repository.
	 *
	 * @since 3.0.0
	 *
	 * @param PackageRepository $repository Package repository.
	 * @param PackageFactory $factory Package factory.
	 */
	public function __construct( PackageRepository $repository, PackageFactory $factory ) {
		$this->factory    = $factory;
		$this->repository = $repository;
	}

	/**
	 * Retrieve all packages in the repository.
	 *
	 * @since 3.0.0
	 *
	 * @return Package[]
	 */
	public function all(): array {
		$items = [];

		foreach ( $this->repository->all() as $package ) {
			$items[] = $this->build( $package );
		}

		return $items;
	}

	/**
	 * Add cached releases to a package.
	 *
	 * @since 3.0.0
	 *
	 * @param Package $package Package.
	 * @return Package
	 */
	protected function build( Package $package ): Package {
		return $this->factory->create( $package->get_type() )
			->with_package( $package )
			->add_cached_releases()
			->build();
	}
}
