<?php
/**
 * Plugin activation routines.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Class to activate the plugin.
 *
 * @since 0.3.0
 */
class Activation extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		register_activation_hook( $this->plugin->get_file(), array( $this, 'activate' ) );
	}

	/**
	 * Set a flag during activation to flush rewrite rules after plugin rewrite
	 * rules have been registered.
	 *
	 * @see \SatisPress\Provider\RewriteRules::maybe_flush_rewrite_rules()
	 *
	 * @since 0.3.0
	 */
	public function activate() {
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}
}
