<?php
/**
 * Cached repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

/**
 * Cached repository class.
 *
 * @since 0.3.0
 */
class CachedRepository extends AbstractRepository implements PackageRepository {
	/**
	 * Whether the repository has been initialized.
	 *
	 * @var boolean
	 */
	protected $initialized = false;

	/**
	 * Items in the repository.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

	/**
	 * Create a repository.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $repository Packge repository.
	 */
	public function __construct( PackageRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Retrieve all items.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function all(): array {
		if ( $this->initialized ) {
			return $this->items;
		}

		$this->initialized = true;
		$this->items       = $this->repository->all();

		return $this->items;
	}
}
