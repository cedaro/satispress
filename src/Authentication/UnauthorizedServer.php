<?php
/**
 * Unauthorized authentication server.
 *
 * Prevents access if all authentication methods have failed.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication;

use SatisPress\Exception\AuthenticationException;
use SatisPress\HTTP\Request;
use WP_Error;
use WP_Http as HTTP;

/**
 * Unauthorized authentication server class.
 *
 * @since 0.3.0
 */
class UnauthorizedServer implements Server {
	/**
	 * Check if the server should handle the current request.
	 *
	 * @since 0.4.0
	 *
	 * @param Request $request Request instance.
	 * @return bool
	 */
	public function check_scheme( Request $request ): bool {
		return true;
	}

	/**
	 * Handle authentication.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request Request instance.
	 * @throws AuthenticationException If the user has not been authenticated at this point.
	 */
	public function authenticate( Request $request ): int {
		throw AuthenticationException::forAuthenticationRequired();
	}
}
