<?php
/**
 * SatisPress request handler.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use function SatisPress\send_file;
use SatisPress\Package;
use SatisPress\PackageManager;

/**
 * Request handler class.
 *
 * @since 0.3.0
 */
class RequestHandler extends AbstractHookProvider {
	/**
	 * Package manager.
	 *
	 * @var PackageManager
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
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'parse_request', [ $this, 'handle_request' ] );

		// Cache the existing version of a plugin before it's updated.
		if ( apply_filters( 'satispress_cache_packages_before_update', true ) ) {
			add_filter( 'upgrader_pre_install', [ $this, 'cache_package_before_update' ], 10, 2 );
		}

		// Delete the 'satispress_packages_json' transient.
		add_action( 'upgrader_process_complete', [ $this, 'flush_packages_json_cache' ] );
		add_action( 'set_site_transient_update_plugins', [ $this, 'flush_packages_json_cache' ] );
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
	public function handle_request( $wp ) {
		if ( ! isset( $wp->query_vars['satispress'] ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$slug    = $wp->query_vars['satispress'];
		$version = isset( $wp->query_vars['satispress_version'] ) ? $wp->query_vars['satispress_version'] : '';

		// Main index request.
		// Ex: https://example.com/satispress/ .
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
	public function get_packages_json(): string {
		$json = get_transient( 'satispress_packages_json' );

		if ( ! $json ) {
			$data     = [];
			$packages = $this->package_manager->get_packages();

			foreach ( $packages as $slug => $package ) {
				$data[ $package->get_package_name() ] = $package->get_package_definition();
			}

			$options = version_compare( PHP_VERSION, '5.3', '>' ) ? JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT : 0;

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
	public function cache_package_before_update( bool $result, array $data ): bool {
		if ( empty( $data['plugin'] ) && empty( $data['theme'] ) ) {
			return $result;
		}

		$type = ( isset( $data['plugin'] ) ) ? 'plugin' : 'theme';

		$package = $this->package_manager->get_package( $data[ $type ], $type );
		if ( $package ) {
			$package->archive();
		}

		return $result;
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
	protected function send_package( Package $package, string $version = null ) {
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
