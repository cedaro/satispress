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

use SatisPress\PackageManager;
use SatisPress\HTTP\Request;

/**
 * Class for rendering packages.json for Composer.
 *
 * @since 0.3.0
 */
class Composer implements Route {
	/**
	 * Package manager.
	 *
	 * @var PackageManager
	 */
	protected $package_manager;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageManager $package_manager Package manager.
	 */
	public function __construct( PackageManager $package_manager ) {
		$this->package_manager = $package_manager;
	}

	/**
	 * Handle a request to the packages.json endpoint.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request HTTP request instance.
	 */
	public function handle_request( Request $request ) {
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$items = $this->get_items();
		echo wp_json_encode( $items );
		exit;
	}

	/**
	 * Retrieves a collection of packages.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function get_items(): array {
		$items = get_transient( 'satispress_packages' );

		if ( ! $items ) {
			$items    = [];
			$packages = $this->package_manager->get_packages();

			foreach ( $packages as $slug => $package ) {
				$items[ $package->get_package_name() ] = $package->get_package_definition( $package );
			}

			set_transient( 'satispress_packages', $items, HOUR_IN_SECONDS * 12 );
		}

		return $items;
	}
}
