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
use SatisPress\Storage\Local;

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
		register_activation_hook( $this->plugin->get_file(), [ $this, 'activate' ] );
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
		$this->setup_storage();
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}

	/**
	 * Set up the local storage provider.
	 *
	 * Creates the cache path and adds an .htaccess file to prevent HTTP access
	 * on Apache.
	 *
	 * @since 0.3.0
	 */
	public function setup_storage() {
		$container  = $this->plugin->get_container();
		$cache_path = $container->get( 'cache.path' );
		$storage    = $container->get( 'storage' );
		$htaccess   = $container->get( 'htaccess.handler' );

		if ( ! $storage instanceof Local ) {
			return;
		}

		if ( ! wp_mkdir_p( $storage->get_base_directory() ) ) {
			return;
		}

		$htaccess->add_rules( [ 'deny from all' ] );
		$htaccess->save();
	}
}
