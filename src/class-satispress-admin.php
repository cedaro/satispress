<?php
/**
 * SatisPress_Admin class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

/**
 * Admininistration class.
 *
 * @since 0.2.0
 */
class SatisPress_Admin {
	/**
	 * Load the admin.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		$manage_screen = new SatisPress_Admin_Screen_ManagePlugins();
		$manage_screen->load();

		$htaccess_handler = new SatisPress_Htaccess(
			SatisPress::instance()->cache_path()
		);

		$settings_screen = new SatisPress_Admin_Screen_Settings( $htaccess_handler );
		$settings_screen->load();

		add_action( 'admin_init', [ $this, 'register_assets' ] );
	}

	/**
	 * Register admin scripts and styles.
	 *
	 * @since 0.2.0
	 */
	public function register_assets() {
		wp_register_script( 'satispress-admin', SATISPRESS_URL . 'assets/js/admin.js', [ 'jquery', 'wp-util' ] );
		wp_register_style( 'satispress-admin', SATISPRESS_URL . 'assets/css/admin.css' );
	}
}
