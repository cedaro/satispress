<?php
/**
 * SatisPress
 *
 * @since 0.1.0
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: SatisPress
 * Plugin URI: https://github.com/bradyvercher/satispress
 * Description: Expose installed plugins as Composer packages.
 * Version: 0.1.0
 * Author: Brady Vercher
 * Author URI: http://www.blazersix.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Load helper functions and libraries.
 */
include( dirname( __FILE__ ) . '/includes/class-satispress-version-parser.php' );
include( dirname( __FILE__ ) . '/includes/functions.php' );

/**
 * Main plugin class.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.1.0
 */
class SatisPress {
	/**
	 * The main SatisPress instance.
	 *
	 * @access private
	 * @var SatisPress
	 */
	private static $instance;

	/**
	 * Main plugin instance.
	 *
	 * @since 0.1.0
	 *
	 * @return SatisPress
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 * @since 0.1.0
	 * @see SatisPress::instance();
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'parse_request', array( $this, 'process_request' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Add a rewrite rule to handle SatiPress requests.
	 *
	 * @since 0.1.0
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule( 'satispress/([^/]+)(/([^/]+))?/?$', 'index.php?satispress=$matches[1]&satispress_version=$matches[3]', 'top' );

		if ( ! is_network_admin() && 'yes' == get_option( 'satispress_flush_rewrite_rules' ) ) {
			update_option( 'satispress_flush_rewrite_rules', 'no' );
			flush_rewrite_rules();
        }
	}

	/**
	 * Whitelist SatisPress query variables.
	 *
	 * @since 0.1.0
	 *
	 * @param array $vars List of query variables.
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = 'satispress';
		$vars[] = 'satispress_version';

		return $vars;
	}

	/**
	 * Process a SatisPress request.
	 *
	 * Determines if the current request is for packages.json or a whitelisted
	 * package and routes to the appropriate method.
	 *
	 * @since 0.1.0
	 *
	 * @param object $wp Current WordPress environment instance (passed by reference).
	 */
	public function process_request( $wp ) {
		if ( ! isset( $wp->query_vars['satispress'] ) || empty( $wp->query_vars['satispress'] ) ) {
			return;
		}

		$slug = $wp->query_vars['satispress'];
		$version = $wp->query_vars['satispress_version'];

		// Send the packages.json
		if ( 'packages.json' == $slug ) {
			echo self::get_packages_json();
			exit;
		}

		$packages = $this->get_plugins();
		if ( ! isset( $packages[ $slug ] ) ) {
			$this->send_404();
		}

		$this->send_package( $packages[ $slug ], $version );
	}

	/**
	 * Retrieve JSON for the packages.json file.
	 *
	 * @todo Include previously cached package versions.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_packages_json() {
		$json = get_transient( 'satispress_packages_json' );
		$json = null; // @todo For testing.

		if ( ! $json ) {
			$whitelist = $this->get_plugins();

			$packages = array();
			$vendor = apply_filters( 'satispress_vendor', 'satispress' );

			foreach ( $whitelist as $slug => $plugin ) {
				$package_name = $vendor . '/' . $slug;

				$packages[ $package_name ] = array(
					$plugin['version'] => array(
						'name'               => $package_name,
						'version'            => wp_strip_all_tags( $plugin['version'] ),
						'version_normalized' => $plugin['version_normalized'],
						'dist'               => array(
							'type' => 'zip',
							'url'  => esc_url_raw( sprintf( home_url( '/satispress/%s/%s' ), $slug, $plugin['version_normalized'] ) ),
						),
						'require'            => array(
							'composer/installers' => '~1.0',
						),
						'type'               => 'wordpress-plugin',
						'authors'            => array(
							array(
								'name'     => wp_strip_all_tags( $plugin['data']['Author'] ),
								'homepage' => esc_url_raw( $plugin['data']['AuthorURI'] ),
							),
						),
						'description'        => wp_strip_all_tags( $plugin['data']['Description'] ),
						'homepage'           => esc_url_raw( $plugin['data']['PluginURI'] ),
					),
				);
			}

			$json = json_encode( array( 'packages' => $packages ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			set_transient( 'satispress_packages_json', $json, 43200 ); // 12 hours.
		}

		return $json;
	}

	/**
	 * Retrieve a list of whitelisted plugins and associated data.
	 *
	 * Plugins should be added to the whitelist by hooking into the
	 * 'satispress_plugins' filter and appending a plugin's basename to the
	 * array. The basename is the main plugin file's relative path from the
	 * plugin directory. Ex. simple-image-widget/simple-image-widget.php
	 *
	 * @since 0.1.0
	 * @todo Create a model for the plugin format.
	 *
	 * @return array
	 */
	public function get_plugins() {
		$plugins = array();
		$whitelist = apply_filters( 'satispress_plugins', array() );

		if ( empty( $whitelist ) ) {
			return array();
		}

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		foreach ( $whitelist as $plugin_basename ) {
			$slug = dirname( $plugin_basename );
			$slug = ( '.' == $slug ) ? basename( $plugin_basename, '.php' ) : $slug;
			$slug = sanitize_title_with_dashes( $slug );

			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin_basename;
			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$plugin_data = get_plugin_data( $plugin_file, false, false );
			$version = $plugin_data['Version'];
			$version_normalized = SatisPress_Version_Parser::normalize( $version );
			if ( empty( $version_normalized ) ) {
				continue;
			}

			$plugins[ $slug ] = array(
				'slug'               => $slug,
				'version'            => $version,
				'version_normalized' => $version_normalized,
				'plugin_basename'    => $plugin_basename,
				'plugin_file'        => $plugin_file,
				'plugin_dir'         => ( '.' == dirname( $plugin_file ) ) ? $plugin_file : dirname( $plugin_file ),
				'data'               => $plugin_data,
			);
		}

		return $plugins;
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
	public function archive_path() {
		$uploads = wp_upload_dir();
		$path = trailingslashit( $uploads['path'] ) . 'satispress/';

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		return apply_filters( 'satispress_archive_path', $path );
	}

	/**
	 * Send a package zip.
	 *
	 * Sends a 404 header if the specified version isn't available.
	 *
	 * @since 0.1.0
	 *
	 * @param array $package Package information in the format returned by SatisPress:get_plugins().
	 * @param string $version Optional. Version of the package to send. Defaults to the current version.
	 */
	protected function send_package( $package, $version = '' ) {
		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

		$satispress_path = $this->archive_path();
		$version = empty( $version ) ? $package['version_normalized'] : $version;
		$filename = $satispress_path . $package['slug'] . '/' . $package['slug'] . '-' . $version . '.zip';

		// Only create the zip if the requested version matches the current version of the plugin.
		if ( $version == $package['version_normalized'] && ! file_exists( $filename ) ) {
			wp_mkdir_p( dirname( $filename ) );

			$zip = new PclZip( $filename );
			$zip->create( $package['plugin_dir'], PCLZIP_OPT_REMOVE_PATH, WP_PLUGIN_DIR );
		}

		// Send a 404 if the package doesn't exit.
		if ( ! file_exists( $filename ) ) {
			$this->send_404();
		}

		satispress_send_file( $filename );
		exit;
	}

	/**
	 * Send a 404 header.
	 *
	 * @since 0.1.0
	 */
	protected function send_404() {
		status_header( 404 );
		nocache_headers();
		wp_die( 'Package doesn\'t exist.' );
	}

	/**
	 *
	 */
	public function activate() {
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}
}

SatisPress::instance();
