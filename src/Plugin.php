<?php
/**
 * Main plugin class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress;

/**
 * Main plugin class - composition root.
 *
 * @since 0.1.0
 */
class Plugin implements Composable {
	/**
	 * Compose the object graph.
	 *
	 * @since 0.2.0
	 */
	public function compose() {
		$htaccess_handler = new Htaccess( $this->cache_path() );

		$package_manager = new PackageManager( $this->cache_path() );

		$basic_auth_request = new Authentication\Basic\Request();
		$basic_auth_request->load();

		$limit_login_attempts = new Integration\LimitLoginAttempts();
		$limit_login_attempts->load();

		if ( is_admin() ) {

			$manage_screen = new Admin\Plugins( $package_manager );
			$manage_screen->load();

			$settings_screen = new Admin\Settings(  $package_manager );
			$settings_screen->load();

			$basic_auth_settings = new Authentication\Basic\Settings( $htaccess_handler );
			$basic_auth_settings->load();
		}

		$satispress = new SatisPress( $package_manager );
		$satispress->load();

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
	}

	/**
	 * Retrieve the path where packages are cached.
	 *
	 * Defaults to 'wp-content/uploads/satispress/'.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function cache_path() {
		$uploads = wp_upload_dir();
		$path    = trailingslashit( $uploads['basedir'] ) . 'satispress/';

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		return apply_filters( 'satispress_cache_path', $path );
	}

	/**
	 * Functionality during activation.
	 *
	 * Sets a flag to flush rewrite rules on the request after activation.
	 *
	 * @since 0.1.0
	 */
	public function activate() {
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}
}
