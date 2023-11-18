<?php
/**
 * Capabilities provider.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Capabilities as Caps;

/**
 * Capabilities provider class.
 *
 * @since 0.3.0
 */
class Capabilities extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
	}

	/**
	 * Map meta capabilities to primitive capabilities.
	 *
	 * @since 0.3.0
	 *
	 * @param array  $caps Returns the user's actual capabilities.
	 * @param string $cap  Capability name.
	 * @return array
	 */
	public function map_meta_cap( array $caps, string $cap ): array {
		$caps = match ($cap) {
      Caps::DOWNLOAD_PACKAGE => [ Caps::DOWNLOAD_PACKAGES ],
      Caps::VIEW_PACKAGE => [ Caps::VIEW_PACKAGES ],
      default => $caps,
  };

		return $caps;
	}
}
