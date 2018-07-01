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
use SatisPress\Capabilities;
use SatisPress\Exception\ExceptionInterface;
use SatisPress\Exception\HTTPException;
use SatisPress\Exception\InvalidReleaseVersion;
use SatisPress\Package;
use SatisPress\PackageManager;
use SatisPress\ReleaseManager;
use SatisPress\HTTP\Request;
use WP_Http as HTTP;

/**
 * Class to handle download requests.
 *
 * @since 0.3.0
 */
class Download implements Route {
	/**
	 * Latest version.
	 *
	 * @var string
	 */
	const LATEST_VERSION = 'latest';

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

		if ( ! current_user_can( Capabilities::DOWNLOAD_PACKAGES ) ) {
			throw HTTPException::forForbiddenResource();
		}

		$slug = sanitize_key( $request['slug'] );
		if ( empty( $slug ) ) {
			throw HTTPException::forUnknownPackage( $slug );
		}

		$version = '';
		if ( ! empty( $request['version'] ) ) {
			$version = preg_replace( '/[^0-9a-z.-]+/i', '', $request['version'] );
		}

		$packages = $this->package_manager->get_packages();

		// Send a 404 response if the package doesn't exist.
		if ( ! isset( $packages[ $slug ] ) ) {
			throw HTTPException::forUknownPackage( $slug );
		}

		$package = $packages[ $slug ];

		// Ensure the user has access to download the package.
		if ( ! current_user_can( Capabilities::DOWNLOAD_PACKAGE, $package->get_slug() ) ) {
			throw HTTPException::forForbiddenPackage( $package );
		}

		$this->send_package( $package, $version );
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
	 * @param string  $version Version of the package to send.
	 */
	protected function send_package( Package $package, string $version ) {
		if ( self::LATEST_VERSION === $version ) {
			$version = $package->get_latest_release()->get_version();
		}

		try {
			$release = $package->get_release( $version );
		} catch ( InvalidReleaseVersion $e ) {
			throw HTTPException::forInvalidRelease( $package, $version );
		}

		// Archive the currently installed version if the artifact doesn't
		// already exist.
		if (
			! $this->release_manager->exists( $release )
			&& $package->get_version() === $version
		) {
			try {
				$this->release_manager->archive( $release );
			} catch ( ExceptionInterface $e ) { }
		}

		// Send a 404 if the release isn't available.
		if ( ! $this->release_manager->exists( $release ) ) {
			throw HTTPException::forMissingRelease( $release );
		}

		$this->release_manager->send( $release );
		exit;
	}
}
