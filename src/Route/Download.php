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

use SatisPress\Capabilities;
use SatisPress\Exception\ExceptionInterface;
use SatisPress\Exception\HTTPException;
use SatisPress\Exception\InvalidReleaseVersion;
use SatisPress\HTTP\Request;
use SatisPress\HTTP\Response;
use SatisPress\Package;
use SatisPress\ReleaseManager;
use SatisPress\Repository\PackageRepository;
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
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $repository      Package repository.
	 * @param ReleaseManager    $release_manager Release manager.
	 */
	public function __construct( PackageRepository $repository, ReleaseManager $release_manager ) {
		$this->repository      = $repository;
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
	 * @throws HTTPException For invalid parameters or the user doesn't have
	 *                       permission to download the requested file.
	 * @return Response
	 */
	public function handle( Request $request ): Response {
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

		$package = $this->repository->first_where( [ 'slug' => $slug ] );

		// Send a 404 response if the package doesn't exist.
		if ( ! $package instanceof Package ) {
			throw HTTPException::forUnknownPackage( $slug );
		}

		// Ensure the user has access to download the package.
		if ( ! current_user_can( Capabilities::DOWNLOAD_PACKAGE, $package->get_slug() ) ) {
			throw HTTPException::forForbiddenPackage( $package );
		}

		return $this->send_package( $package, $version );
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
	 * @throws HTTPException For invalid or missing releases.
	 * @return Response
	 */
	protected function send_package( Package $package, string $version ): Response {
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
			&& $package->get_installed_version() === $version
		) {
			$this->release_manager->archive( $release );
		}

		// Send a 404 if the release isn't available.
		if ( ! $this->release_manager->exists( $release ) ) {
			throw HTTPException::forMissingRelease( $release );
		}

		return $this->release_manager->send( $release );
	}
}
