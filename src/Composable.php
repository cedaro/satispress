<?php
/**
 * Composable interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Segregated interface of something that should be composed.
 */
interface Composable {
	/**
	 * Compose the object graph.
	 *
	 * @since 0.3.0
	 */
	public function compose();
}
