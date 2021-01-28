<?php
/**
 * Zip artifact validator.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.7.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Validator;

use PclZip;
use SatisPress\Exception\InvalidPackageArtifact;
use SatisPress\Release;

/**
 * Zip artifact validator class.
 *
 * @since 0.7.0
 */
class ZipValidator implements ArtifactValidator {
	/**
	 * Validate that a file is a readable zip archive.
	 *
	 * @since 0.7.0
	 *
	 * @param string  $filename Path to the file to validate.
	 * @param Release $release Release instance.
	 * @throws InvalidPackageArtifact If a file cannot be parsed as a zip file.
	 * @return bool
	 */
	public function validate( string $filename, Release $release ): bool {
		$zip = new PclZip( $filename );

		if ( 0 === $zip->properties() ) {
			throw InvalidPackageArtifact::unreadableZip( $filename );
		}

		return true;
	}
}
