<?php
/**
 * Composer packages.json endpoint.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Route;

use SatisPress\Capabilities;
use SatisPress\Exception\ExceptionInterface;
use SatisPress\Exception\FileNotFound;
use SatisPress\Exception\HTTPException;
use SatisPress\HTTP\Request;
use SatisPress\HTTP\Response;
use SatisPress\HTTP\ResponseBody\JsonBody;
use SatisPress\Package;
use SatisPress\ReleaseManager;
use SatisPress\Repository\PackageRepository;
use SatisPress\VersionParser;
use WP_Http as HTTP;

/**
 * Class for rendering packages.json for Composer.
 *
 * @since 0.3.0
 */
class Composer implements Route {
	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

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
	 * @param PackageRepository $repository      Package repository.
	 * @param ReleaseManager    $release_manager Release manager.
	 * @param VersionParser     $version_parser  Version parser.
	 */
	public function __construct( PackageRepository $repository, ReleaseManager $release_manager, VersionParser $version_parser ) {
		$this->repository      = $repository;
		$this->release_manager = $release_manager;
		$this->version_parser  = $version_parser;
	}

	/**
	 * Handle a request to the packages.json endpoint.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request HTTP request instance.
	 * @throws HTTPException If the user doesn't have permission to view packages.
	 * @return Response
	 */
	public function handle( Request $request ): Response {
		if ( ! current_user_can( Capabilities::VIEW_PACKAGES ) ) {
			throw HTTPException::forForbiddenResource();
		}

		return new Response(
			new JsonBody( [ 'packages' => $this->get_items() ] ),
			HTTP::OK,
			[ 'Content-Type' => 'application/json; charset=' . get_option( 'blog_charset' ) ]
		);
	}

	/**
	 * Retrieves a collection of packages.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function get_items(): array {
		$items = [];

		foreach ( $this->repository->all() as $slug => $package ) {
			if ( ! $package->has_releases() ) {
				continue;
			}

			try {
				$item = $this->prepare_item_for_response( $package );

				// Skip if there aren't any viewable releases.
				if ( empty( $item ) ) {
					continue;
				}

				$items[ $package->get_name() ] = $item;
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( FileNotFound $e ) {
				// Continue to allow valid items to be served.
			}
		}

		return $items;
	}

	/**
	 * Prepare a package for response.
	 *
	 * @param Package $package Package instance.
	 * @return array
	 */
	public function prepare_item_for_response( Package $package ): array {
		$item = [];

		foreach ( $package->get_releases() as $release ) {
			// Skip if the current user can't view this release.
			if ( ! current_user_can( Capabilities::VIEW_PACKAGE, $package, $release ) ) {
				continue;
			}

			$item[ $release->get_version() ] = [
				'name'               => $package->get_name(),
				'version'            => $release->get_version(),
				'version_normalized' => $this->version_parser->normalize( $release->get_version() ),
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
		}

		return $item;
	}
}
