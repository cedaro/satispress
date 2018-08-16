<?php
/**
 * Base repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\Package;

use function SatisPress\is_plugin_file;

/**
 * Abstract repository class.
 *
 * @since 0.3.0
 */
abstract class AbstractRepository {
	/**
	 * Retrieve all packages.
	 *
	 * @since 0.3.0
	 *
	 * @return Package[]
	 */
	abstract public function all(): array;

	/**
	 * Whether an item with the supplied criteria exists.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return bool
	 */
	public function contains( array $args ): bool {
		return ! empty( $this->where( $args ) );
	}

	/**
	 * Retrieve items that match a list of key/value pairs.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return array
	 */
	public function where( array $args ): array {
		$args       = $this->parse_args( $args );
		$matches    = [];
		$args_count = count( $args );

		foreach ( $this->all() as $item ) {
			$matched = 0;

			foreach ( $args as $key => $value ) {
				if ( $item[ $key ] && $value === $item[ $key ] ) {
					$matched++;
				}
			}

			if ( $matched === $args_count ) {
				$matches[] = $item;
			}
		}

		return $matches;
	}

	/**
	 * Retrieve the first item to match a list of key/value pairs.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Map of key/value pairs.
	 * @return Package|null
	 */
	public function first_where( array $args ) {
		$items = $this->where( $args );
		return empty( $items ) ? null : reset( $items );
	}

	/**
	 * Apply a callback to a repository to filter items.
	 *
	 * @since 0.3.0
	 *
	 * @param callable $callback Filter callback.
	 * @return PackageRepository
	 */
	public function with_filter( callable $callback ): PackageRepository {
		return new FilteredRepository( $this, $callback );
	}

	/**
	 * Parse arguments used for filtering a collection.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args List of key/value pairs.
	 * @return array
	 */
	protected function parse_args( array $args ): array {
		// If a plugin file is passed as the slug value, convert it to a
		// basename argument.
		if ( isset( $args['slug'] ) && is_plugin_file( $args['slug'] ) ) {
			$args['basename'] = $args['slug'];
			unset( $args['slug'] );
		}

		return $args;
	}
}
