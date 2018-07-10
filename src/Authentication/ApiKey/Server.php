<?php
/**
 * API Key authentication server.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication\ApiKey;

use SatisPress\Authentication\AbstractServer;
use SatisPress\HTTP\Request;
use SatisPress\WP_Error\HttpError;
use WP_Error;
use WP_Http as HTTP;

/**
 * API Key authentication server class.
 *
 * @since 0.3.0
 */
class Server extends AbstractServer {
	/**
	 * API Key repository.
	 *
	 * @var ApiKeyRepository
	 */
	protected $repository;

	/**
	 * Constructor method.
	 *
	 * @since 0.3.0
	 *
	 * @param Request          $request    Request instance.
	 * @param ApiKeyRepository $repository API Key repository.
	 */
	public function __construct( Request $request, ApiKeyRepository $repository ) {
		$this->repository = $repository;
		$this->request    = $request;
	}

	/**
	 * Handle authentication.
	 *
	 * @since 0.3.0
	 *
	 * @param int|bool $user_id Current user ID or false if unknown.
	 * @return int|bool A user on success, or false on failure.
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

		// The password field isn't used for API Key authentication.
		$realm = $this->request->get_header( 'PHP_AUTH_PW' );

		// Bail if this isn't a SatisPress authentication request.
		if ( 'satispress' !== $realm ) {
			return $user_id;
		}

		$this->should_attempt = false;

		$api_key_id = $this->request->get_header( 'PHP_AUTH_USER' );

		// Bail if an API Key wasn't provided.
		if ( empty( $api_key_id ) ) {
			$this->auth_status = HttpError::missingAuthorizationHeader();
			return false;
		}

		$api_key = $this->repository->find_by_token( $api_key_id );

		// Bail if the API Key doesn't exist.
		if ( null === $api_key ) {
			$this->auth_status = HttpError::invalidCredentials();
			return false;
		}

		$user = $api_key->get_user();

		// Bail if the user couldn't be determined.
		if ( ! $this->validate_user( $user ) ) {
			$this->auth_status = HttpError::invalidCredentials();
			return false;
		}

		$this->maybe_update_last_used_time( $api_key );
		$this->auth_status = true;

		return $user->ID;
	}

	/**
	 * Handle errors encountered when authenticating.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error $error Error object.
	 */
	protected function handle_error( WP_Error $error ) {
		$error_data = $error->get_error_data();

		if ( ! empty( $error_data['status'] ) && HTTP::UNAUTHORIZED === $error_data['status'] ) {
			header( 'WWW-Authenticate: Basic realm="SatisPress"' );
		}

		$status_code = empty( $error_data['status'] ) ? HTTP::INTERNAL_SERVER_ERROR : $error_data['status'];
		wp_die( wp_kses_data( $error->get_error_message() ), absint( $status_code ) );
	}

	/**
	 * Update the last used time if it's been more than a minute.
	 *
	 * @since 0.3.0
	 *
	 * @param ApiKey $api_key API Key.
	 */
	protected function maybe_update_last_used_time( ApiKey $api_key ) {
		$timestamp = time();
		$last_used = $api_key['last_used'] ?? 0;

		if ( $timestamp - $last_used < MINUTE_IN_SECONDS ) {
			return;
		}

		$api_key['last_used'] = time();
		$this->repository->save( $api_key );
	}

	/**
	 * Whether a user is valid.
	 *
	 * @since 0.3.0
	 *
	 * @param mixed $user WordPress user instance.
	 * @return bool
	 */
	protected function validate_user( $user ): bool {
		return ! empty( $user ) && ! is_wp_error( $user ) && $user->exists();
	}
}
