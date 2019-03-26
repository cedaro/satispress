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

use SatisPress\Exception\HttpException;
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
	 * @throws HttpException If the user has not been authenticated at this point.
	 */
	public function authenticate( Request $request ): int {
		throw HttpException::forAuthenticationRequired();
	}

	/**
	 * Display an error message when authentication fails.
	 *
	 * @since 0.3.0
	 *
	 * @param HttpException $e HTTP exception.
	 */
	public function handle_error( HttpException $e ): WP_Error {
		header( 'WWW-Authenticate: Basic realm="SatisPress"' );

		wp_die(
			wp_kses_data( $e->getMessage() ),
			esc_html__( 'Authentication Required', 'satispress' ),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			[ 'response' => HTTP::UNAUTHORIZED ]
		);
	}
}
