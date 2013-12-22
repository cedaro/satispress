<?php
/**
 * SatisPress Basic Authentication
 *
 * @package SatisPress\Authentication\Basic
 * @author Brady Vercher <brady@blazersix.com>
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: SatisPress Basic Authentication
 * Plugin URI: https://github.com/bradyvercher/satispress
 * Description: Retstrict access to SatisPress packages with HTTP Basic Authentication.
 * Version: 0.1.0
 * Author: Blazer Six
 * Author URI: http://www.blazersix.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Main plugin class.
 *
 * @package SatisPress\Authentication\Basic
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.1.0
 */
class SatisPress_Authentication_Basic {
	/**
	 * Load the plugin.
	 *
	 * @since 0.1.0
	 */
	public function load() {
		add_action( 'satispress_send_package', array( $this, 'authorize_package_request' ) );
		add_action( 'satispress_pre_basic_authentication', array( $this, 'limit_login_attempts' ) );

		// Setup related.
		add_action( 'admin_init', array( $this, 'maybe_setup' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Authenticate requests for SatisPress packages using HTTP Basic Authentication.
	 *
	 * @since 0.1.0
	 */
	public function authorize_package_request() {
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
	 * @since 0.1.0
	 */
	public function limit_login_attempts( $user ) {
		global $error;

		if ( function_exists( 'limit_login_get_message' ) ) {
			$message = limit_login_get_message();
			if ( '' != $message ) {
				wp_die( $error . $message );
			}
		}

		return $user;
	}

	/**
	 * Set up the plugin if SatisPress is active.
	 *
	 * @since 0.1.0
	 */
	public function maybe_setup() {
		if ( 'yes' != get_option( 'satispress_setup_basicauth' ) || ! class_exists( 'SatisPress' ) ) {
			return;
		}

		$this->save_htaccess();
		update_option( 'satispress_setup_basicauth', 'no' );
	}

	/**
	 * Plugin activation routine.
	 *
	 * @since 0.1.0
	 */
	public function activate() {
		update_option( 'satispress_setup_basicauth', 'yes' );
		$this->maybe_setup();
	}

	/**
	 * Lock down the cache folder with .htaccess.
	 *
	 * Creates an .htaccess file in the cache directory with a 'Deny from all' rule to prevent direct access.
	 *
	 * @since 0.1.0
	 */
	protected function save_htaccess() {
		$cache_path = SatisPress::instance()->cache_path();
		$htaccess_file = $cache_path . '.htaccess';

		if ( ( ! file_exists( $htaccess_file ) && is_writable( $cache_path ) ) || is_writable( $htaccess_file ) ) {
			$htaccess = '';
			if ( file_exists( $htaccess_file ) ) {
				$htaccess = file_get_contents( $htaccess_file );
			}

			$rules = array();
			$directive = 'Deny from all';

			if ( false === strpos( $htaccess, $directive ) ) {
				$rules[] = $directive;
			}

			insert_with_markers( $htaccess_file, 'SatisPress', $rules );
		}
	}
}

global $satispress_basic_auth;
$satispress_basic_auth = new SatisPress_Authentication_Basic();
$satispress_basic_auth->load();
