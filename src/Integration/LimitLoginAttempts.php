<?php
/**
 * Limit Login Attempts plugin integration class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

namespace SatisPress\Integration;

use WP_Error;
use WP_User;

/**
 * Add support for Limit Login Attempts plugin.
 *
 * @since 0.2.0
 */
class LimitLoginAttempts {
	/**
	 * Load the plugin.
	 *
	 * @since 0.3.0
	 */
	public function load() {
		$options = get_option( 'satispress' );
		if ( isset( $options['enable_basic_authentication'] ) && 'yes' === $options['enable_basic_authentication'] ) {
			add_filter( 'satispress_pre_basic_authentication', [ $this, 'limit_login_attempts' ] );
		}
	}

	/**
	 * Show an error message from the Limit Login Attempts plugin.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error|WP_User $user WP_Error or WP_User objects.
	 * @return WP_Error|WP_User
	 */
	public function limit_login_attempts( $user ) {
		global $error;

		if ( function_exists( 'limit_login_get_message' ) ) {
			$message = limit_login_get_message();
			if ( '' !== $message ) {
				wp_die( wp_kses_post( $error . $message ) );
			}
		}

		return $user;
	}
}
