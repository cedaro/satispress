<?php
/**
 * Package repository interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\Package;

/**
 * Package repository interface.
 *
 * @since 0.3.0
 */
interface PackageRepository {
	/**
	 * Retrieve all packages.
	 *
	 * @since 0.3.0
	 *
	 * @return Package[]
	 */
	public function all(): array;

	/**
	 * Whether a package with the supplied criteria exists.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return bool
	 */
	public function contains( array $args ): bool;

	/**
	 * Retrieve packages that match a list of key/value pairs.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return array
	 */
	public function where( array $args ): array;

	/**
	 * Retrieve the first item to match a list of key/value pairs.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return Package|null
	 */
	public function first_where( array $args );

	/**
	 * Apply a callback to a repository to filter items.
	 *
	 * @since 0.3.0
	 *
	 * @param callable $filter Filter callback.
	 * @return PackageRepository
	 */
	public function with_filter( callable $callback ): PackageRepository;
}
