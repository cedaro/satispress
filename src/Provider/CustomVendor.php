<?php
 /**
  * Custom vendor provider.
  *
  * @package SatisPress
  * @license GPL-2.0-or-later
  * @since 0.3.0
  */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Custom vendor provider class.
 *
 * @since 0.3.0
 */
class CustomVendor extends AbstractHookProvider {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_filter( 'satispress_vendor', [ $this, 'filter_vendor' ], 5 );
	}

	/**
	 * Update the vendor string based on the vendor setting value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $vendor Vendor string.
	 * @return string
	 */
	public function filter_vendor( string $vendor ): string {
		$option = get_option( 'satispress' );
		if ( ! empty( $option['vendor'] ) ) {
			$vendor = $option['vendor'];
		}

		return $vendor;
	}
}
