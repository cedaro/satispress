<?php
/**
 * Download request handler.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Route;

use function SatisPress\send_file;
use SatisPress\Package;
use SatisPress\PackageManager;
use SatisPress\ReleaseManager;
use SatisPress\HTTP\Request;

/**
 * Class to handle download requests.
 *
 * @since 0.3.0
 */
class Download implements Route {
	/**
	 * Package manager.
	 *
	 * @var PackageManager
	 */
	protected $package_manager;

	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageManager $package_manager Package manager.
	 * @param ReleaseManager $release_manager Release manager.
	 */
	public function __construct( PackageManager $package_manager, ReleaseManager $release_manager ) {
		$this->package_manager = $package_manager;
		$this->release_manager = $release_manager;
	}

	/**
	 * Process a download request.
	 *
	 * Determines if the current request is for packages.json or a whitelisted
	 * package and routes it to the appropriate method.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request HTTP request instance.
	 */
	public function handle_request( Request $request ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$slug = sanitize_key( $request['slug'] );
		if ( empty( $slug ) ) {
			$this->send_404();
		}

		$version = '';
		if ( ! empty( $request['version'] ) ) {
			$version = preg_replace( '/[^0-9a-z.-]+/i', '', $request['version'] );
		}

		$packages = $this->package_manager->get_packages();

		// Send a 404 response if the package doesn't exist.
		if ( ! isset( $packages[ $slug ] ) ) {
			$this->send_404();
		}

		$this->send_package( $packages[ $slug ], $version );
		exit;
	}

	/**
	 * Send a package zip.
	 *
	 * Sends a 404 header if the specified version isn't available.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package object.
	 * @param string  $version Optional. Version of the package to send. Defaults to the current version.
	 */
	protected function send_package( Package $package, string $version = null ) {
		if ( null === $version ) {
			$version = '';
		}

		$releases = $package->get_releases();
		if ( ! isset( $releases[ $version ] ) ) {
			$this->send_404();
		}

		$release = $package->get_release( $version );
		if ( empty( $release ) ) {
			$this->send_404();
		}

		// Archive the currently installed version if the artifact doesn't
		// already exist.
		if (
			! $this->release_manager->exists( $release ) &&
			$package->get_version() === $version
		) {
			$this->release_manager->archive( $release );
		}

		// Send a 404 if the release isn't available.
		if ( ! $this->release_manager->exists( $release ) ) {
			$this->send_404();
		}

		// @todo Remove this action after updating authentication handlers.
		do_action( 'satispress_send_package', $package, $version );

		$this->release_manager->send( $release );
		exit;
	}

	/**
	 * Send a 404 response.
	 *
	 * @since 0.3.0
	 */
	protected function send_404() {
		status_header( 404 );
		nocache_headers();
		wp_die( esc_html__( 'Package does not exist.', 'satispress' ) );
	}
}
