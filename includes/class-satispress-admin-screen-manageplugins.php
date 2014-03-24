<?php
/**
 * Manage plugins screen.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.2.0
 */
class SatisPress_Admin_Screen_ManagePlugins {
	/**
	 * Load the screen.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_action( 'wp_ajax_satispress_toggle_plugin', array( $this, 'ajax_toggle_plugin_status' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'manage_plugins_columns', array( $this, 'register_columns' ) );
		add_action( 'manage_plugins_custom_column', array( $this, 'display_columns' ), 10, 3 );
		add_action( 'admin_footer-plugins.php', array( $this, 'admin_footer' ) );

		//add_action( 'after_plugin_row', array( $this, 'after_plugin_row' ), 10, 3 );
	}

	/**
	 * Toggle whether or not a plugin is included in packages.json.
	 *
	 * @since 0.2.0
	 * @todo Implement the nonce.
	 */
	public function ajax_toggle_plugin_status() {
		if ( ! isset( $_POST['plugin_file'] ) /*|| ! isset( $_POST['nonce']*/ ) {
			return;
		}

		$plugin = $_POST['plugin_file'];
		$plugins = get_option( 'satispress_plugins' );

		if ( false !== ( $key = array_search( $plugin, $plugins ) ) ) {
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
		wp_enqueue_script( 'wp-util' );
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
	 * @param array $plugin_data Array of plugin data.
	 */
	public function display_columns( $column_name, $plugin_file, $plugin_data ) {
		if ( 'satispress' !== $column_name ) {
			return;
		}

		$packages = SatisPress::instance()->get_packages();
		$plugins = get_option( 'satispress_plugins' );
		$plugin = new SatisPress_Package_Plugin( $plugin_file );

		$checked = checked( isset( $packages[ $plugin->get_slug() ] ), true, false );
		$disabled = ( empty( $checked ) || in_array( $plugin_file, $plugins ) ) ? '' : ' disabled="disabled"';

		echo '<input type="checkbox" value="' . esc_attr( $plugin_file ) . '"' . $checked . $disabled . ' class="satispress-status">';
		echo '<span class="spinner"></span>';
	}

	/**
	 * Print script to toggle plugin status.
	 *
	 * @since 0.2.0
	 */
	public function admin_footer() {
		?>
		<script>
		(function( $ ) {
			$( '.satispress-status' ).on( 'change', function() {
				var $checkbox = $( this ),
					$spinner = $( this ).siblings( '.spinner' ).show();

				wp.ajax.post( 'satispress_toggle_plugin', {
					plugin_file: $checkbox.val(),
					status: $checkbox.prop( 'checked' )
				}).done(function() {
					$checkbox.show();
					$spinner.hide();
				});
			});
		})( jQuery );
		</script>
		<style type="text/css">
		.column-satispress input[type="checkbox"] {
			float: left;
			margin-top: 4px;
		}

		.column-satispress .spinner {
			float: left;
		}
		</style>
		<?php
	}

	/*
	public function after_plugin_row( $plugin_file, $plugin_data, $status ) {
		$columns = get_column_headers( get_current_screen()->id );
		$class = is_plugin_active( $plugin_file ) ? 'active' : 'inactive';

		echo '<tr class="' . $class . '">';
			echo '<td colspan="' . count( $columns ) . '">';
				$package = new SatisPress_Package_Plugin( $plugin_file );

				echo '<pre>';
					//print_r( $package->get_package_definition() );
				echo '</pre>';
			echo '</td>';
		echo '</tr>';
	}
	*/
}
