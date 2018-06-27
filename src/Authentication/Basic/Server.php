<?php
/**
 * Basic authentication server.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication\Basic;

use SatisPress\Authentication\AbstractServer;
use WP_Error;
use WP_User;

/**
 * Basic authentication server class.
 *
 * @since 0.3.0
 */
class Server extends AbstractServer {
	/**
	 * Handle authentication.
	 *
	 * @since 0.3.0
	 *
	 * @param integer|bool $user_id Current user ID or false if unknown.
	 * @return integer|bool A user on success, or false on failure.
	 */
	public function authenticate( $user_id ) {
		if ( ! empty( $user_id ) || ! $this->should_attempt ) {
			return $user_id;
		}

		$header = $this->request->get_header( 'authorization' );

		// Bail if the authorization header doesn't exist.
		if ( empty( $header ) || 0 !== stripos( $header, 'basic ' ) ) {
			return $user_id;
		}

		$this->should_attempt = false;

		$username = $this->request->get_header( 'PHP_AUTH_USER' );
		$password = $this->request->get_header( 'PHP_AUTH_PW' );

		// Bail if the client details don't exist.
		if ( empty( $username ) || empty( $password ) ) {
			$this->auth_status = new WP_Error(
				'invalid_request',
				esc_html__( 'Missing authorization header.', 'satispress' ),
				[ 'status' => 401 ]
			);

			return false;
		}

		$user = wp_authenticate( $username, $password );

		if ( ! $user || is_wp_error( $user ) ) {
			$this->auth_status = new WP_Error(
				'invalid_credentianls',
				esc_html__( 'Invalid credentials.', 'satispress' ),
				[ 'status' => 401 ]
			);

			return false;
		}

		$this->auth_status = true;

		return $user->ID;
	}

	/**
	 * Handle errors encountered when authenticating with the Basic server.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error $error Error object.
	 */
	protected function handle_error( WP_Error $error ) {
		$error_data = $error->get_error_data();

		if ( ! empty( $error_data['status'] ) && 401 === $error_data['status'] ) {
			header( 'WWW-Authenticate: Basic' );
		}

		wp_die(
			wp_kses_data( $error->get_error_message() ),
			empty( $error_data['status'] ) ? 500 : $error_data['status']
		);
	}
}
