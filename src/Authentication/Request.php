<?php
/**
 * Authentication request interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication;

/**
 * Authentication request interface.
 *
 * @since 0.3.0
 */
interface Request {
	/**
	 * Authenticate requests for SatisPress packages.
	 *
	 * @since 0.3.0
	 */
	public function authenticate();
}
