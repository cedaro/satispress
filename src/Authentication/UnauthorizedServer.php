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
	 * @param integer|bool $user_id Current user ID or false if unknown.
	 * @return integer|bool A user ID on success, or false on failure.
	 */
	public function authenticate( $user_id ) {
		if ( ! empty( $user_id ) || ! $this->should_attempt ) {
			return $user_id;
		}

		$this->should_attempt = false;

		$this->auth_status = new WP_Error(
			'invalid_credentials',
			esc_html__( 'Authentication is required for this resource.', 'satispress' ),
			[ 'status' => HTTP::UNAUTHORIZED ]
		);

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
		$error_data = $error->get_error_data();

		header( 'WWW-Authenticate: Basic realm="SatisPress"' );

		wp_die(
			wp_kses_data( $error->get_error_message() ),
			esc_html__( 'Authentication Required', 'satispress' ),
			[ 'response' => HTTP::UNAUTHORIZED ]
		); // WPCS: XSS OK.
	}
}
