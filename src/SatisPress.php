<?php
/**
 * SatisPress class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress;

/**
 * SatisPress class.
 *
 * @since 0.1.0
 */
class SatisPress {
	/**
	 * Package manager.
	 *
	 * @var string
	 */
	protected $package_manager;

	/**
	 * Initialise SatisPress object.
	 *
	 * @param PackageManager $package_manager Package manager.
	 */
	public function __construct( PackageManager $package_manager ) {
		$this->package_manager = $package_manager;
	}

	/**
	 * Load SatisPress.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'register_assets' ] );
		}

		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
		add_action( 'parse_request', [ $this, 'process_request' ] );
		add_filter( 'satispress_vendor', [ $this, 'filter_vendor' ], 5 );

		// Cache the existing version of a plugin before it's updated.
		if ( apply_filters( 'satispress_cache_packages_before_update', true ) ) {
			add_filter( 'upgrader_pre_install', [ $this, 'cache_package_before_update' ], 10, 2 );
		}

		// Delete the 'satispress_packages_json' transient.
		add_action( 'upgrader_process_complete', [ $this, 'flush_packages_json_cache' ] );
		add_action( 'set_site_transient_update_plugins', [ $this, 'flush_packages_json_cache' ] );
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
		if ( ! isset( $wp->query_vars['satispress'] ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$slug    = $wp->query_vars['satispress'];
		$version = isset( $wp->query_vars['satispress_version'] ) ? $wp->query_vars['satispress_version'] : '';

		// Main index request.
		// Ex: http://example.com/satispress/ .
		if ( empty( $slug ) ) {
			do_action( 'satispress_index' );
			return;
		}

		// Send packages.json.
		if ( 'packages.json' === $slug ) {
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped -- JSON
			echo $this->get_packages_json();
			exit;
		}

		// Send a package if it has been whitelisted.
		$packages = $this->package_manager->get_packages();
		if ( ! isset( $packages[ $slug ] ) ) {
			$this->send_404();
			wp_die();
		}

		$this->send_package( $packages[ $slug ], $version );
	}

	/**
	 * Retrieve JSON for the packages.json file.
	 *
	 * @todo Consider caching to a static file instead.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_packages_json() {
		$json = get_transient( 'satispress_packages_json' );

		if ( ! $json ) {
			$data     = [];
			$packages = $this->package_manager->get_packages();

			foreach ( $packages as $slug => $package ) {
				$data[ $package->get_package_name() ] = $package->get_package_definition();
			}

			$options = version_compare( phpversion(), '5.3', '>' ) ? JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT : 0;

			$json = wp_json_encode(
				[
					'packages' => $data,
				],
				$options
			);
			$json = str_replace( '\\/', '/', $json ); // Unescape slashes (PHP 5.3 compatible method).
			set_transient( 'satispress_packages_json', $json, HOUR_IN_SECONDS * 12 );
		}

		return $json;
	}

	/**
	 * Cache the current version of a plugin before it's udpated.
	 *
	 * @since 0.2.0
	 *
	 * @param bool  $result Whether the plugin update/install process should continue.
	 * @param array $data   Extra data passed by the update/install process.
	 * @return bool
	 */
	public function cache_package_before_update( $result, $data ) {
		if ( empty( $data['plugin'] ) && empty( $data['theme'] ) ) {
			return $result;
		}

		$type = ( isset( $data['plugin'] ) ) ? 'plugin' : 'theme';
		$slug = $data[ $type ];

		$package = $this->package_manager->get_package( $data[ $type ], $type );
		if ( $package ) {
			$package->archive();
		}

		return $result;
	}

	/**
	 * Add a rewrite rule to handle SatisPress requests.
	 *
	 * @since 0.1.0
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule( 'satispress/([^/]+)(/([^/]+))?/?$', 'index.php?satispress=$matches[1]&satispress_version=$matches[3]', 'top' );

		if ( ! is_network_admin() && 'yes' === get_option( 'satispress_flush_rewrite_rules' ) ) {
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
	 * @param Package $package Package object.
	 * @param string  $version Optional. Version of the package to send. Defaults to the current version.
	 */
	protected function send_package( $package, $version = null ) {
		if ( null === $version ) {
			$version = '';
		}

		$file = $package->archive( $version );

		// Send a 404 if the file doesn't exit.
		if ( ! $file ) {
			$this->send_404();
		}

		do_action( 'satispress_send_package', $package, $version, $file );

		send_file( $file );
		exit;
	}

	/**
	 * Update the vendor string based on the vendor setting value.
	 *
	 * @since 0.2.0
	 *
	 * @param string $vendor Vendor string.
	 * @return string
	 */
	public function filter_vendor( $vendor ) {
		$option = get_option( 'satispress' );
		if ( ! empty( $option['vendor'] ) ) {
			$vendor = $option['vendor'];
		}

		return $vendor;
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
}
