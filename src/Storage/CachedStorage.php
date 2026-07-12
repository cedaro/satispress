<?php
/**
 * Cached storage adapter.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Storage;

use SatisPress\HTTP\Response;

/**
 * Cached storage adapter class.
 *
 * Decorates another storage adapter and caches checksums using the
 * transients API to avoid hashing artifacts on every request.
 *
 * Checksums for each file are stored together in a single transient, keyed
 * by algorithm, so they can be invalidated when the file changes. Writes go
 * through move(), so the cache is invalidated there and in delete().
 *
 * @since 3.0.0
 */
class CachedStorage implements Storage {
	/**
	 * Prefix for checksum transients.
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'satispress_checksums_';

	/**
	 * Cache time to live in seconds.
	 *
	 * @var int
	 */
	protected $cache_ttl;

	/**
	 * Decorated storage adapter.
	 *
	 * @var Storage
	 */
	protected $storage;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Storage $storage   Storage adapter to decorate.
	 * @param int     $cache_ttl Cache time to live in seconds.
	 */
	public function __construct( Storage $storage, int $cache_ttl ) {
		$this->storage   = $storage;
		$this->cache_ttl = $cache_ttl;
	}

	/**
	 * Retrieve the hash value of the contents of a file.
	 *
	 * @since 3.0.0
	 *
	 * @param string $algorithm Algorithm.
	 * @param string $file      Relative file path.
	 * @throws \SatisPress\Exception\FileNotFound If the file doesn't exist.
	 * @return string
	 */
	public function checksum( string $algorithm, string $file ): string {
		$transient = $this->get_transient_name( $file );

		// Clear stale checksums if the file no longer exists and let the
		// decorated adapter handle the missing file.
		if ( ! $this->exists( $file ) ) {
			delete_transient( $transient );
			return $this->storage->checksum( $algorithm, $file );
		}

		$checksums = get_transient( $transient );

		if ( is_array( $checksums ) && isset( $checksums[ $algorithm ] ) ) {
			return $checksums[ $algorithm ];
		}

		$checksum = $this->storage->checksum( $algorithm, $file );

		$checksums               = is_array( $checksums ) ? $checksums : [];
		$checksums[ $algorithm ] = $checksum;

		set_transient( $transient, $checksums, $this->cache_ttl );

		return $checksum;
	}

	/**
	 * Delete a file.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file Relative file path.
	 * @return bool
	 */
	public function delete( string $file ): bool {
		delete_transient( $this->get_transient_name( $file ) );
		return $this->storage->delete( $file );
	}

	/**
	 * Whether a file exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file Relative file path.
	 * @return bool
	 */
	public function exists( string $file ): bool {
		return $this->storage->exists( $file );
	}

	/**
	 * List files.
	 *
	 * @since 3.0.0
	 *
	 * @param string $path Relative path.
	 * @return array Array of relative file paths.
	 */
	public function list_files( string $path ): array {
		return $this->storage->list_files( $path );
	}

	/**
	 * Move a file.
	 *
	 * @since 3.0.0
	 *
	 * @param string $source      Absolute path to a file on the local file system.
	 * @param string $destination Relative destination path; includes the file name.
	 * @return bool
	 */
	public function move( string $source, string $destination ): bool {
		delete_transient( $this->get_transient_name( $destination ) );
		return $this->storage->move( $source, $destination );
	}

	/**
	 * Send a file for client download.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file Relative file path.
	 * @return Response
	 */
	public function send( string $file ): Response {
		return $this->storage->send( $file );
	}

	/**
	 * Retrieve the name of the transient for caching a file's checksums.
	 *
	 * The file path is hashed to keep the name within the transient name
	 * length limit.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file Relative file path.
	 * @return string
	 */
	protected function get_transient_name( string $file ): string {
		return self::TRANSIENT_PREFIX . md5( $file );
	}
}
