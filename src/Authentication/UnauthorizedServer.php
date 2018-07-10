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

use SatisPress\WP_Error\HttpError;
use WP_Error;
use WP_Http as HTTP;

/**
 * Unauthorized authentication server class.
 *
 * @since 0.3.0
 */
class UnauthorizedServer extends AbstractServer {
	/**
	 * Handle authentication.
	 *
	 * @since 0.3.0
	 *
	 * @param int|bool $user_id Current user ID or false if unknown.
	 * @return int|bool A user ID on success, or false on failure.
	 */
	public function authenticate( $user_id ) {
		if ( ! empty( $user_id ) || ! $this->should_attempt ) {
			return $user_id;
		}

		$this->should_attempt = false;
		$this->auth_status    = HttpError::authenticationRequired();

		return false;
	}

	/**
	 * Display an error message when authentication fails.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error $error Error object.
	 */
	protected function handle_error( WP_Error $error ) {
		header( 'WWW-Authenticate: Basic realm="SatisPress"' );

		wp_die(
			wp_kses_data( $error->get_error_message() ),
			esc_html__( 'Authentication Required', 'satispress' ),
			// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			[ 'response' => HTTP::UNAUTHORIZED ]
		);
	}
}
