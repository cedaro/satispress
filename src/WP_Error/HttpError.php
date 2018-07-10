<?php
/**
 * HTTP errors.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\WP_Error;

use WP_Error;
use WP_Http as HTTP;

/**
 * HTTP errors class.
 *
 * @since 0.3.0
 */
class HttpError extends WP_Error {
	/**
	 * Create an error for requests that require authentication.
	 *
	 * @since 0.3.0
	 *
	 * @return HttpError
	 */
	public static function authenticationRequired(): HttpError {
		return new static(
			'invalid_credentials',
			esc_html__( 'Authentication is required for this resource.', 'satispress' ),
			[ 'status' => HTTP::UNAUTHORIZED ]
		);
	}

	/**
	 * Create an error for invalid credentials.
	 *
	 * @since 0.3.0
	 *
	 * @return HttpError
	 */
	public static function invalidCredentials(): HttpError {
		return new static(
			'invalid_credentials',
			esc_html__( 'Invalid credentials.', 'satispress' ),
			[ 'status' => HTTP::UNAUTHORIZED ]
		);
	}

	/**
	 * Create an error for a missing authorization header.
	 *
	 * @since 0.3.0
	 *
	 * @return HttpError
	 */
	public static function missingAuthorizationHeader(): HttpError {
		return new static(
			'invalid_request',
			esc_html__( 'Missing authorization header.', 'satispress' ),
			[ 'status' => HTTP::UNAUTHORIZED ]
		);
	}
}
