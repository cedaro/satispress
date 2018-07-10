<?php
/**
 * Composer repository transformer.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Transformer;

use SatisPress\Capabilities;
use SatisPress\Exception\FileNotFound;
use SatisPress\Package;
use SatisPress\ReleaseManager;
use SatisPress\Repository\PackageRepository;
use SatisPress\VersionParser;

/**
 * Composer repository transformer class.
 *
 * @since 0.3.0
 */
class ComposerRepositoryTransformer implements PackageRepositoryTransformer {
	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Version parser.
	 *
	 * @var VersionParser
	 */
	protected $version_parser;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param ReleaseManager $release_manager Release manager.
	 * @param VersionParser  $version_parser  Version parser.
	 */
	public function __construct( ReleaseManager $release_manager, VersionParser $version_parser ) {
		$this->release_manager = $release_manager;
		$this->version_parser  = $version_parser;
	}

	/**
	 * Transform a repository of packages into the format used in packages.json.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $repository Package repository.
	 * @return array
	 */
	public function transform( PackageRepository $repository ): array {
		$items = [];

		foreach ( $repository->all() as $slug => $package ) {
			if ( ! $package->has_releases() ) {
				continue;
			}

			$item = $this->transform_item( $package );

			// Skip if there aren't any viewable releases.
			if ( empty( $item ) ) {
				continue;
			}

			$items[ $package->get_name() ] = $item;
		}

		return [ 'packages' => $items ];
	}

	/**
	 * Transform an item.
	 *
	 * @param Package $package Package instance.
	 * @return array
	 */
	protected function transform_item( Package $package ): array {
		$item = [];

		foreach ( $package->get_releases() as $release ) {
			// Skip if the current user can't view this release.
			if ( ! current_user_can( Capabilities::VIEW_PACKAGE, $package, $release ) ) {
				continue;
			}

			$version = $release->get_version();

			try {
				$item[ $version ] = [
					'name'               => $package->get_name(),
					'version'            => $version,
					'version_normalized' => $this->version_parser->normalize( $version ),
					'dist'               => [
						'type'   => 'zip',
						'url'    => $release->get_download_url(),
						'shasum' => $this->release_manager->checksum( 'sha1', $release ),
					],
					'require'            => [
						'composer/installers' => '^1.0',
					],
					'type'               => $package->get_type(),
					'authors'            => [
						'name'     => $package->get_author(),
						'homepage' => esc_url( $package->get_author_url() ),
					],
					'description'        => $package->get_description(),
					'homepage'           => $package->get_homepage(),
				];
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( FileNotFound $e ) {
				// Continue to allow valid items to be served.
			}
		}

		return $item;
	}
}
