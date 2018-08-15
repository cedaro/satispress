<?php
/**
 * Package transformer.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Transformer;

use SatisPress\Package;

/**
 * Package transformer interface.
 *
 * @since 0.3.0
 */
interface PackageTransformer {
	/**
	 * Transform a package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package.
	 */
	public function transform( Package $package );
}
