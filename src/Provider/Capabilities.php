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
use WP_User;

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
		add_action( 'members_register_cap_groups', [ $this, 'register_members_capability_group' ] );
		add_action( 'members_register_caps', [ $this, 'register_members_capabilities' ] );
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
			case Caps::DOWNLOAD_PACKAGE :
				$caps = [ Caps::DOWNLOAD_PACKAGES ];
				break;

			case Caps::VIEW_PACKAGE :
				$caps = [ Caps::VIEW_PACKAGES ];
				break;
		}

		return $caps;
	}

	/**
	 * Register a capability group for the Members plugin.
	 *
	 * @since 0.3.0
	 *
	 * @link https://wordpress.org/plugins/members/
	 */
	public function register_members_capability_group() {
		members_register_cap_group(
			'satispress',
			[
				'label'    => esc_html__( 'SatisPress', 'satispress' ),
				'caps'     => [],
				'icon'     => 'dashicons-admin-generic',
				'priority' => 50,
			]
		);
	}

	/**
	 * Register capabilities for the Members plugin.
	 *
	 * @since 0.3.0
	 *
	 * @link https://wordpress.org/plugins/members/
	 */
	public function register_members_capabilities() {
		members_register_cap(
			Caps::DOWNLOAD_PACKAGES,
			[
				'label' => esc_html__( 'Download Packages', 'satispress' ),
				'group' => 'satispress',
			]
		);

		members_register_cap(
			Caps::VIEW_PACKAGES,
			[
				'label' => esc_html__( 'View Packages', 'satispress' ),
				'group' => 'satispress',
			]
		);
	}
}
