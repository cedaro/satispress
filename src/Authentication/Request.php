<?php
/**
 * Authentication interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress\Authentication;

/**
 * SatisPress authentication interface.
 *
 * @since 0.2.0
 */
interface Request {
	/**
	 * Authenticate requests for SatisPress packages.
	 *
	 * @since 0.3.0
	 */
	public function authenticate();
}
