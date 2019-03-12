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
	protected $disable_authentication;
	
	public function __construct( bool $disable_authentication = false ) {
		$this->disable_authentication = $disable_authentication;
	}
	
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
		add_filter( 'user_has_cap', [ $this, 'user_has_cap' ], 10, 3 );
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
		switch ( $cap ) {
			case Caps::DOWNLOAD_PACKAGE:
				$caps = [ Caps::DOWNLOAD_PACKAGES ];
				break;

			case Caps::VIEW_PACKAGE:
				$caps = [ Caps::VIEW_PACKAGES ];
				break;
		}

		return $caps;
	}

	public function user_has_cap( array $allcaps, array $caps, array $args ) {
		if ( $this->disable_authentication ) {
			$allcaps[Caps::DOWNLOAD_PACKAGES] = true;
			$allcaps[Caps::VIEW_PACKAGES] = true;
		}

		return $allcaps;
	}
}
