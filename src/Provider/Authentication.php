<?php
/**
 * Authentication provider.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use Pimple\ServiceIterator;
use SatisPress\Authentication\Server;
use SatisPress\Capabilities as Caps;
use SatisPress\Exception\HttpException;
use SatisPress\HTTP\Request;
use WP_Error;

/**
 * Authentication provider class.
 *
 * @since 0.3.0
 */
class Authentication extends AbstractHookProvider {
	/**
	 * Server used for authenticating the request.
	 *
	 * @var Server
	 */
	protected $active_server;

	/**
	 * Errors that occurred during authentication.
	 *
	 * @var HttpException Authentication exception.
	 */
	protected $auth_status;

	/**
	 * Server request.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Authentication servers.
	 *
	 * @var ServiceIterator
	 */
	protected $servers;

	/**
	 * Whether to attempt to authenticate.
	 *
	 * Helps prevent recursion and processing multiple times per request.
	 *
	 * @var bool
	 */
	protected $should_attempt = true;

	/**
	 * Constructor.
	 *
	 * @param ServiceIterator $servers Authentication servers.
	 * @param Request         $request Request instance.
	 */
	public function __construct( ServiceIterator $servers, Request $request ) {
		$this->servers = $servers;
		$this->request = $request;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		if ( ! $this->is_satispress_request() ) {
			return;
		}

		add_filter( 'determine_current_user', [ $this, 'determine_current_user' ] );
		add_filter( 'user_has_cap', [ $this, 'maybe_allow_public_access' ] );

		// Allow cookie authentication to work for download requests.
		if ( 0 === strpos( $this->get_request_path(), '/satispress' ) ) {
			remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
		}
	}

	/**
	 * Handle authentication.
	 *
	 * @since 0.4.0
	 *
	 * @param int|bool $user_id Current user ID or false if unknown.
	 * @throws \LogicException If a registered server doesn't implement the server interface.
	 * @return int|bool A user on success, or false on failure.
	 */
	public function determine_current_user( $user_id ) {
		if ( ! empty( $user_id ) || ! $this->should_attempt ) {
			return $user_id;
		}

		$this->should_attempt = false;

		foreach ( $this->servers as $server ) {
			if ( ! $server instanceof Server ) {
				throw new \LogicException( 'Authentication servers must implement \SatisPress\Authentication\Server.' );
			}

			if ( ! $server->check_scheme( $this->request ) ) {
				continue;
			}

			try {
				$user_id = $server->authenticate( $this->request );
			} catch ( HttpException $e ) {
				$this->auth_status   = $e;
				$this->active_server = $server;
				$user_id             = false;

				add_filter( 'rest_authentication_errors', [ $this, 'get_authentication_errors' ] );
			}

			break;
		}

		return $user_id;
	}

	/**
	 * Report authentication errors.
	 *
	 * @since 0.4.0
	 *
	 * @param WP_Error|mixed $value Error from another authentication handler,
	 *                              null if we should handle it, or another value if not.
	 * @return WP_Error|bool|null
	 */
	public function get_authentication_errors( $value ) {
		if ( null !== $value || is_user_logged_in() ) {
			return $value;
		}

		return $this->active_server
			->handle_error( $this->auth_status );
	}

	/**
	 * Whether the current request is for a SatisPress route or REST endpoint.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	protected function is_satispress_request(): bool {
		$request_path = $this->get_request_path();

		// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		if ( ! empty( $_GET['satispress_route'] ) ) {
			return true;
		}

		if ( 0 === strpos( $request_path, '/satispress' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the request path.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	protected function get_request_path(): string {
		$request_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		$wp_base = get_home_url( null, '/', 'relative' );
		if ( 0 === strpos( $request_path, $wp_base ) ) {
			$request_path = substr( $request_path, \strlen( $wp_base ) );
		}

		return '/' . ltrim( $request_path, '/' );
	}

	/**
	 * Sets and returns all the capabilities the current user has and should have.
	 *
	 * Appends `allcaps` with satispress_download_packages
	 * as well as satispress_view_packages if there are no servers,
	 * meaning that authentication should be skipped.
	 *
	 * @since 0.4.0
	 *
	 * @param array $allcaps All capabilities the current user has.
	 * @return array
	 */
	public function maybe_allow_public_access( array $allcaps ): array {
		if ( 0 >= \iterator_count( $this->servers ) ) {
			$allcaps[ Caps::DOWNLOAD_PACKAGES ] = true;
			$allcaps[ Caps::VIEW_PACKAGES ]     = true;
		}

		return $allcaps;
	}
}
