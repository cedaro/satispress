<?php
/**
 * Storage interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Storage;

use SatisPress\HTTP\Response;

/**
 * Storage interface.
 *
 * @since 0.3.0
 */
interface Storage {
	/**
	 * Retrieve the hash value of the contents of a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $algorithm Algorithm.
	 * @param string $file      Relative file path.
	 * @return string
	 */
	public function checksum( string $algorithm, string $file ): string;

	/**
	 * Delete a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return bool
	 */
	public function delete( string $file ): bool;

	/**
	 * Whether a file exists.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return bool
	 */
	public function exists( string $file ): bool;

	/**
	 * List files.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Relative path.
	 * @return array Array of relative file paths.
	 */
	public function list_files( string $path ): array;

	/**
	 * Move a file.
	 *
	 * @param string $source      Absolute path to a file on the local file system.
	 * @param string $destination Relative destination path; includes the file name.
	 * @return bool
	 */
	public function move( string $source, string $destination ): bool;

	/**
	 * Send a file for client download.
	 *
	 * @since 0.3.0
	 *
	 * @param string $file Relative file path.
	 * @return Response
	 */
	public function send( string $file ): Response;
}
