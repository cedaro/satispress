<?php
/**
 * Members plugin integration.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Integration;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Capabilities;

/**
 * Members plugin integration provider class.
 *
 * @since 0.3.0
 */
class Members extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'members_register_cap_groups', [ $this, 'register_capability_group' ] );
		add_action( 'members_register_caps', [ $this, 'register_capabilities' ] );
	}

	/**
	 * Register a capability group for the Members plugin.
	 *
	 * @since 0.3.0
	 *
	 * @link https://wordpress.org/plugins/members/
	 */
	public function register_capability_group() {
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
	public function register_capabilities() {
		members_register_cap(
			Capabilities::DOWNLOAD_PACKAGES,
			[
				'label' => esc_html__( 'Download Packages', 'satispress' ),
				'group' => 'satispress',
			]
		);

		members_register_cap(
			Capabilities::VIEW_PACKAGES,
			[
				'label' => esc_html__( 'View Packages', 'satispress' ),
				'group' => 'satispress',
			]
		);
	}
}
