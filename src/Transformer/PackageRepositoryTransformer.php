<?php
/**
 * Package repository transformer interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Transformer;

use SatisPress\Repository\PackageRepository;

/**
 * Package repository transformer interface.
 *
 * @since 0.3.0
 */
interface PackageRepositoryTransformer {
	/**
	 * Transform a package repository.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $repository Package repository.
	 * @return mixed
	 */
	public function transform( PackageRepository $repository );
}
