<?php
/**
 * Basic class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress\Authentication\Basic;

use SatisPress\Authentication\Request as RequestInterface;
use SatisPress\Htaccess;

/**
 * SatisPress basic authentication class.
 *
 * @since 0.2.0
 */
class Request implements RequestInterface {
	/**
	 * Load the plugin.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		$options = get_option( 'satispress' );
		if ( isset( $options['enable_basic_authentication'] ) && 'yes' === $options['enable_basic_authentication'] ) {
			add_action( 'satispress_send_package', [ $this, 'authenticate' ] );
		}
	}

	/**
	 * Authenticate requests for SatisPress packages.
	 *
	 * @since 0.2.0
	 */
	public function authenticate() {
		// Some CGI/FastCGI implementations don't set the PHP_AUTH_* variables, so
		// potentially set them from a .htaccess environment rule.
		// See https://github.com/blazersix/satispress/wiki/Basic-Auth .
		if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			list( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) =
				explode( ':', base64_decode( substr( $_SERVER['HTTP_AUTHORIZATION'], 6 ) ) );
		}

		$user = is_user_logged_in() ? wp_get_current_user() : false;

		if ( ! $user && isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			$user = wp_authenticate( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
		}

		$user = apply_filters( 'satispress_pre_basic_authentication', $user );

		// Request credentials if the user isn't logged in yet.
		if ( ! $user || is_wp_error( $user ) ) {
			header( 'WWW-Authenticate: Basic realm="SatisPress"' );
			header( 'HTTP/1.0 401 Unauthorized' );
			exit;
		}
	}
}
