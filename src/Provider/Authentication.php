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

/**
 * Authentication provider class.
 *
 * @since 0.3.0
 */
class Authentication extends AbstractHookProvider {
	/**
	 * Authentication servers.
	 *
	 * @var ServiceIterator
	 */
	protected $servers;

	/**
	 * Constructor.
	 *
	 * @param ServiceIteraor $servers Authentication servers.
	 */
	public function __construct( ServiceIterator $servers ) {
		$this->servers = $servers;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'plugins_loaded', [ $this, 'register_authentication_servers' ], 8 );
	}

	/**
	 * Register authentication servers.
	 *
	 * The `determine_current_user` filter needs to be wired up before any
	 * plugins call `wp_get_current_user()`, otherwise the authentication
	 * callbacks won't run.
	 *
	 * These authentication servers are only registered when the request is for
	 * a SatisPress route.
	 *
	 * @since 0.3.0
	 */
	public function register_authentication_servers() {
		if ( ! $this->is_satispress_request() ) {
			return;
		}

		foreach ( $this->servers as $server ) {
			add_filter( 'determine_current_user', [ $server, 'authenticate' ] );
			add_filter( 'rest_authentication_errors', [ $server, 'get_authentication_errors' ] );
		}
	}

	/**
	 * Whether the current request is for a SatisPress route or REST endpoint.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	protected function is_satispress_request(): bool {
		$request_path = $this->get_request_path();

		if ( ! empty( $_GET['satispress_route'] ) ) {
			return true;
		} elseif ( 0 === strpos( $request_path, '/satispress' ) ) {
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
		if ( substr( $request_path, 0, strlen( $wp_base ) ) === $wp_base ) {
			$request_path = substr( $request_path, strlen( $wp_base ) );
		}

		return '/' . ltrim( $request_path, '/' );
	}
}
