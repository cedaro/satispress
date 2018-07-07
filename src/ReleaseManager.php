<?php
/**
 * Release manager.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\Exception\FileOperationFailed;
use SatisPress\Exception\InvalidReleaseSource;
use SatisPress\HTTP\Response;
use SatisPress\Storage\Storage;
use WP_Error;

/**
 * Release manager class.
 *
 * @since 0.3.0
 */
class ReleaseManager {
	/**
	 * Archiver.
	 *
	 * @var Archiver
	 */
	protected $archiver;

	/**
	 * Storage.
	 *
	 * @var Storage
	 */
	protected $storage;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param Storage  $storage  Storage service.
	 * @param Archiver $archiver Archiver.
	 */
	public function __construct( Storage $storage, Archiver $archiver ) {
		$this->archiver = $archiver;
		$this->storage  = $storage;
	}

	/**
	 * Retrieve all releases for a package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package instance.
	 * @return array
	 */
	public function all( Package $package ): array {
		$releases = [];

		$files = $this->storage->list_files( $package->get_slug() );

		foreach ( $files as $filename ) {
			$version              = str_replace( $package->get_slug() . '-', '', basename( $filename, '.zip' ) );
			$release              = new Release( $package, $version );
			$releases[ $version ] = $release;
		}

		return $releases;
	}

	/**
	 * Archive a release.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @throws InvalidReleaseSource If a source URL is not available or the
	 *                              version doesn't match the currently installed version.
	 * @throws FileOperationFailed  If the release artifact can't be moved to storage.
	 * @return Release
	 */
	public function archive( Release $release ): Release {
		if ( $this->exists( $release ) ) {
			return $release;
		}

		$source_url = $release->get_source_url();
		if ( ! empty( $source_url ) ) {
			$filename = $this->archiver->archive_from_url( $release );
		} elseif ( $release->get_version() === $release->get_package()->get_installed_version() ) {
			$filename = $this->archiver->archive_from_source( $release );
		} else {
			throw InvalidReleaseSource::forRelease( $release );
		}

		if ( ! $this->storage->move( $filename, $release->get_file_path() ) ) {
			throw FileOperationFailed::unableToMoveReleaseArtifactToStorage( $filename, $release->get_file_path() );
		}

		return $release;
	}

	/**
	 * Retrieve a checksum for a release.
	 *
	 * @since 0.3.0
	 *
	 * @param string  $algorithm Algorithm.
	 * @param Release $release   Release instance.
	 * @return string
	 */
	public function checksum( string $algorithm, Release $release ): string {
		return $this->storage->checksum( $algorithm, $release->get_file_path() );
	}

	/**
	 * Whether an artifact exists for a given release.
	 *
	 * @param Release $release Release instance.
	 * @return boolean
	 */
	public function exists( Release $release ): bool {
		return $this->storage->exists( $release->get_file_path() );
	}

	/**
	 * Send a download.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @return Response
	 */
	public function send( Release $release ): Response {
		do_action( 'satispress_send_release', $release );
		return $this->storage->send( $release->get_file_path() );
	}
}
