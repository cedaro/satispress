<?php
/**
 * SatisPress
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: SatisPress
 * Plugin URI: https://github.com/bradyvercher/satispress
 * Description: Generate a Composer repository from installed WordPress plugins.
 * Version: 0.2.0
 * Author: Blazer Six
 * Author URI: http://www.blazersix.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Load helpers.
 */
include( dirname( __FILE__ ) . '/includes/class-satispress-package.php' );
include( dirname( __FILE__ ) . '/includes/class-satispress-plugin.php' );
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
		add_action( 'plugins_loaded', array( $this, 'load' ) );
	}

	/**
	 * Load SatisPress.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'parse_request', array( $this, 'process_request' ) );

		// Cache the existing version of a plugin before it's updated.
		if ( apply_filters( 'satispress_cache_plugins_before_update', true ) ) {
			add_filter( 'upgrader_pre_install', array( $this, 'cache_plugin_before_update' ), 10, 2 );
		}

		// Delete the 'satispress_packages_json' transient.
		add_action( 'upgrader_process_complete', array( $this, 'flush_packages_json_cache' ) );
		add_action( 'set_site_transient_update_plugins', array( $this, 'flush_packages_json_cache' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Process a SatisPress request.
	 *
	 * Determines if the current request is for packages.json or a whitelisted
	 * package and routes it to the appropriate method.
	 *
	 * @since 0.1.0
	 *
	 * @param object $wp Current WordPress environment instance (passed by reference).
	 */
	public function process_request( $wp ) {
		if ( ! isset( $wp->query_vars['satispress'] ) || empty( $wp->query_vars['satispress'] ) ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$slug = $wp->query_vars['satispress'];
		$version = isset( $wp->query_vars['satispress_version'] ) ? $wp->query_vars['satispress_version'] : '';

		// Send packages.json
		if ( 'packages.json' == $slug ) {
			echo self::get_packages_json();
			exit;
		}

		$plugins = $this->get_plugins();
		if ( ! isset( $plugins[ $slug ] ) ) {
			$this->send_404();
			wp_die();
		}

		$package = new SatisPress_Package( $plugins[ $slug ] );

		$this->send_package( $package, $version );
	}

	/**
	 * Retrieve JSON for the packages.json file.
	 *
	 * @todo Consider caching to a satic file instead.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_packages_json() {
		$json = get_transient( 'satispress_packages_json' );

		if ( ! $json ) {
			$packages = array();
			$plugins = $this->get_plugins();

			foreach ( $plugins as $slug => $plugin ) {
				$package = new SatisPress_Package( $plugin );
				$packages[ $package->get_name() ] = $package->get_package_definition();
			}

			$json = json_encode( array( 'packages' => $packages ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			set_transient( 'satispress_packages_json', $json, 43200 ); // 12 hours.
		}

		return $json;
	}

	/**
	 * Retrieve a list of plugin objects.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_plugins() {
		$plugins = array();
		$whitelist = $this->get_whitelist();

		if ( empty( $whitelist ) ) {
			return array();
		}

		foreach ( $whitelist as $plugin_basename ) {
			$plugin = new SatisPress_Plugin( $plugin_basename );

			if ( ! file_exists( $plugin->get_file() ) || '' == $plugin->get_version( 'normalized' ) ) {
				continue;
			}

			$plugins[ $plugin->get_slug() ] = $plugin;
		}

		return $plugins;
	}

	/**
	 * Retrieve a list of whitelisted plugins.
	 *
	 * Plugins should be added to the whitelist by hooking into the
	 * 'satispress_plugins' filter and appending a plugin's basename to the
	 * array. The basename is the main plugin file's relative path from the
	 * plugin directory. Ex. simple-image-widget/simple-image-widget.php
	 *
	 * @access protected
	 * @since 0.2.0
	 *
	 * @return string
	 */
	protected function get_whitelist() {
		return apply_filters( 'satispress_plugins', array() );
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
		$path = trailingslashit( $uploads['path'] ) . 'satispress/';

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		return apply_filters( 'satispress_cache_path', $path );
	}

	/**
	 * Cache the current version of a plugin before it's udpated.
	 *
	 * @since 0.2.0
	 *
	 * @param bool $result Whether the plugin update/install process should continue.
	 * @param array $data Extra data passed by the update/install process.
	 * @return bool
	 */
	public function cache_plugin_before_update( $result, $data ) {
		if ( ! empty( $data['plugin'] ) && in_array( $data['plugin'], $this->get_whitelist() ) ) {
			$plugin = new SatisPress_Plugin( $data['plugin'] );
			$package = new SatisPress_Package( $plugin );
			$package->archive();
		}

		return $result;
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
	 * Send a package zip.
	 *
	 * Sends a 404 header if the specified version isn't available.
	 *
	 * @since 0.1.0
	 *
	 * @param SatisPress_Package $package Package object.
	 * @param string $version Optional. Version of the package to send. Defaults to the current version.
	 */
	protected function send_package( $package, $version = '' ) {
		$file = $package->archive( $version );

		// Send a 404 if the file doesn't exit.
		if ( ! $file ) {
			$this->send_404();
		}

		do_action( 'satispress_send_package', $package, $version, $file );

		satispress_send_file( $file );
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
	 * Flush the packages.json cache.
	 *
	 * @since 0.2.0
	 */
	public function flush_packages_json_cache() {
		delete_transient( 'satispress_packages_json' );
	}

	/**
	 * Functionality during activation.
	 *
	 * Sets a flag to flush rewrite rules on the request after actvation.
	 *
	 * @since 0.1.0
	 */
	public function activate() {
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}
}

SatisPress::instance();
