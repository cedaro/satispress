<?php
/**
 * Local storage adapter.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Storage;

use DirectoryIterator;
use SatisPress\Exception\FileNotFound;
use SatisPress\HTTP\Response;
use WP_Error;
use WP_Http as HTTP;

/**
 * Local storage adapter class.
 *
 * @since 0.3.0
 */
class Local implements Storage {
	/**
	 * Base directory.
	 *
	 * @var string
	 */
	protected $base_directory = '';

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param string $base_directory Base storage directory.
	 */
	public function __construct( $base_directory ) {
		$this->set_base_directory( $base_directory );
	}

	/**
	 * Retrieve the hash value of the contents of a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $algorithm Algorithm.
	 * @param string $file      Relative file path.
	 * @throws FileNotFound If the file doesn't exist.
	 * @return string
	 */
	public function checksum( $algorithm, $file ): string {
		$filename = $this->get_absolute_path( $file );

		if ( ! file_exists( $filename ) ) {
			throw FileNotFound::forInvalidChecksum( $filename );
		}

		return hash_file( $algorithm, $filename );
	}

	/**
	 * Delete a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return boolean
	 */
	public function delete( $file ): bool {
		return unlink( $this->get_absolute_path( $file ) );
	}

	/**
	 * Whether a file exists.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return boolean
	 */
	public function exists( $file ): bool {
		$filename = $this->get_absolute_path( $file );
		return file_exists( $filename );
	}

	/**
	 * List files.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Relative path.
	 * @return array Array of relative file paths.
	 */
	public function list_files( $path ): array {
		$directory = $this->get_absolute_path( $path );
		if ( ! file_exists( $directory ) ) {
			return [];
		}

		$iterator = new DirectoryIterator( $directory );
		if ( iterator_count( $iterator ) < 1 ) {
			return [];
		}

		$files = [];
		foreach ( $iterator as $fileinfo ) {
			if ( ! $fileinfo->isFile() || 'zip' !== $fileinfo->getExtension() ) {
				continue;
			}

			$files[] = $fileinfo->getFilename();
		}

		return $files;
	}

	/**
	 * Move a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $source      Absolute path to a file on the local file system.
	 * @param string $destination Relative destination path; includes the file name.
	 * @return boolean
	 */
	public function move( $source, $destination ): bool {
		$filename = $this->get_absolute_path( $destination );

		if ( ! wp_mkdir_p( dirname( $filename ) ) ) {
			return false;
		}

		if ( ! rename( $source, $filename ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Send a file for client download.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return Response
	 */
	public function send( string $file ): Response {
		$filename = $this->get_absolute_path( $file );
		return Response::for_file( $filename );
	}

	/**
	 * Retrieve the base storage directory.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_base_directory(): string {
		return $this->base_directory;
	}

	/**
	 * Set the base storage directory.
	 *
	 * @since 0.3.0
	 *
	 * @param string $directory Absolute path.
	 * @return $this
	 */
	public function set_base_directory( $directory ): Storage {
		$this->base_directory = rtrim( $directory, '/' ) . '/';
		return $this;
	}

	/**
	 * Join a relative path with the base storage directory.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public function get_absolute_path( $path = '' ): string {
		return $this->get_base_directory() . ltrim( $path, '/' );
	}
}
