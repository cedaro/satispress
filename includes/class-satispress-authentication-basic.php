<?php
/**
 * SatisPress_Htaccess class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

/**
 * SatisPress basic authentication class.
 *
 * @since 0.2.0
 */
class SatisPress_Authentication_Basic {
	/**
	 * Base path for cached packages.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	public $base_path = '';

	/**
	 * Load the plugin.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_filter( 'update_option_satispress', array( $this, 'maybe_setup' ), 10, 2 );
		$options = get_option( 'satispress' );
		if ( isset( $options['enable_basic_authentication'] ) && 'yes' === $options['enable_basic_authentication'] ) {
			add_action( 'satispress_send_package', array( $this, 'authorize_package_request' ) );
			add_action( 'satispress_pre_basic_authentication', array( $this, 'limit_login_attempts' ) );
		}
	}

	/**
	 * Set the base path for cached packages.
	 *
	 * @since 0.2.0
	 *
	 * @param string $path Base cache path.
	 */
	public function set_base_path( $path ) {
		$this->base_path = $path;
	}

	/**
	 * Authenticate requests for SatisPress packages using HTTP Basic Authentication.
	 *
	 * @since 0.2.0
	 */
	public function authorize_package_request() {
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

	/**
	 * Show an error message from the Limit Login Attempts plugin.
	 *
	 * @since 0.2.0
	 *
	 * @param mixed $user User.
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

	/**
	 * Update .htaccess rules when the setting is changed.
	 *
	 * Creates an .htaccess file in the cache directory with a 'Deny from all' rule to prevent direct access.
	 *
	 * @since 0.2.0
	 *
	 * @param array $old_value Current settings values.
	 * @param array $value Saved settings.
	 */
	public function maybe_setup( $old_value, $value ) {
		if ( ! isset( $value['enable_basic_authentication'] ) ) {
			return;
		}

		$rules = array();
		if ( 'yes' === $value['enable_basic_authentication'] ) {
			$rules[] = 'Deny from all';
		}

		$htaccess = new SatisPress_Htaccess( $this->base_path );
		$htaccess->add_rules( $rules );
		$htaccess->save();
	}
}
