<?php
/**
 * WPBakery plugin integration.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.7.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Integration;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Release;

/**
 * WPBakery plugin integration provider class.
 *
 * @since 0.7.0
 */
class WPBakery extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.7.0
	 */
	public function register_hooks() {
		add_filter( 'satispress_package_download_url', [ $this, 'filter_package_download_url' ], 10, 2 );
	}

	/**
	 * Filter the download URL for package updates from WPBakery.
	 *
	 * @since 0.7.0
	 *
	 * @param string  $download_url Download URL.
	 * @param Release $release      Release instance.
	 * @return string
	 */
	public function filter_package_download_url( string $download_url, Release $release ): string {
		if ( \vc_plugin_name() === $release->get_package()->get_basename() ) {
			// @todo Request and validate the response to automatically cache updates.
			// $response     = \vc_updater()->getDownloadUrl();
			$download_url = '';
		}

		return $download_url;
	}
}
