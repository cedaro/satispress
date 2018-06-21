<?php
/**
 * Admin_Screen_ManagePlugins class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress;

/**
 * Manage plugins screen.
 *
 * @since 0.2.0
 */
class Admin_Screen_ManagePlugins {
	/**
	 * Load the screen.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_action( 'wp_ajax_satispress_toggle_plugin', [ $this, 'ajax_toggle_plugin_status' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'manage_plugins_columns', [ $this, 'register_columns' ] );
		add_action( 'manage_plugins_custom_column', [ $this, 'display_columns' ], 10, 3 );
	}

	/**
	 * Toggle whether or not a plugin is included in packages.json.
	 *
	 * @since 0.2.0
	 */
	public function ajax_toggle_plugin_status() {
		if ( ! isset( $_POST['plugin_file'] ) || ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$plugin  = $_POST['plugin_file'];
		$plugins = (array) get_option( 'satispress_plugins' );

		// Bail if the nonce can't be verified.
		check_admin_referer( 'toggle-status_' . $plugin );

		$key = array_search( $plugin, $plugins, true );
		if ( false !== $key ) {
			unset( $plugins[ $key ] );
		} else {
			$plugins[] = $plugin;
		}

		$plugins = array_filter( array_unique( $plugins ) );

		update_option( 'satispress_plugins', $plugins );
		wp_send_json_success();
	}

	/**
	 * Enqueue assets for the screen.
	 *
	 * @since 0.2.0
	 *
	 * @param  string $hook_suffix Screen hook id.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'plugins.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'satispress-admin' );
		wp_enqueue_style( 'satispress-admin' );
	}

	/**
	 * Register admin columns.
	 *
	 * @since 0.2.0
	 *
	 * @param array $columns List of admin columns.
	 * @return array
	 */
	public function register_columns( $columns ) {
		$columns['satispress'] = 'SatisPress';
		return $columns;
	}

	/**
	 * Display admin columns.
	 *
	 * @since 0.2.0
	 *
	 * @param string $column_name Column identifier.
	 * @param string $plugin_file Plugin file basename.
	 * @param array  $plugin_data Array of plugin data.
	 */
	public function display_columns( $column_name, $plugin_file, $plugin_data ) {
		if ( 'satispress' !== $column_name ) {
			return;
		}

		$packages = SatisPress::instance()->get_packages();
		$plugins  = get_option( 'satispress_plugins' );
		$plugin   = SatisPress::instance()->get_package( $plugin_file, 'plugin' );

		printf( '<input type="checkbox" value="%1$s"%2$s%3$s class="satispress-status">',
			esc_attr( $plugin_file ),
			checked( isset( $packages[ $plugin->get_slug() ] ), true, false ),
			( empty( $checked ) || in_array( $plugin_file, $plugins, true ) ) ? '' : ' disabled="disabled"'
		);

		echo '<span class="spinner"></span>';

		printf( '<input type="hidden" value="%s" class="satispress-status-nonce">',
			esc_attr( wp_create_nonce( 'toggle-status_' . $plugin_file ) )
		);
	}
}
