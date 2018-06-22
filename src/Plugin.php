<?php
/**
 * Main plugin class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use Cedaro\WP\Plugin\AbstractPlugin;
use Cedaro\WP\Plugin\Provider\I18n;

/**
 * Main plugin class - composition root.
 *
 * @since 0.3.0
 */
class Plugin extends AbstractPlugin implements Composable {
	/**
	 * Compose the object graph.
	 *
	 * @since 0.3.0
	 */
	public function compose() {
		/**
		 * Start composing the object graph in SatisPress.
		 *
		 * @since 0.3.0
		 */
		do_action( 'satispress_compose' );

		$package_manager = new PackageManager( $this->cache_path() );

		// Register hook providers.
		$this
			->register_hooks( new I18n() )
			->register_hooks( new Provider\RewriteRules() )
			->register_hooks( new Provider\CustomVendor() )
			->register_hooks( new Authentication\Basic\Request() )
			->register_hooks( new Provider\RequestHandler( $package_manager ) )
			->register_hooks( new Provider\LimitLoginAttempts() );

		if ( is_admin() ) {
			$htaccess_handler = new Htaccess( $this->cache_path() );

			$this
				->register_hooks( new Provider\AdminAssets() )
				->register_hooks( new Provider\BasicAuthenticationSettings( $htaccess_handler ) )
				->register_hooks( new Screen\Plugins( $package_manager ) )
				->register_hooks( new Screen\Settings( $package_manager ) );
		}

		/**
		 * Finished composing the object graph in SatisPress.
		 *
		 * @since 0.3.0
		 */
		do_action( 'satispress_composed' );
	}

	/**
	 * Retrieve the path where packages are cached.
	 *
	 * Defaults to 'wp-content/uploads/satispress/'.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function cache_path(): string {
		$uploads = wp_upload_dir();
		$path    = trailingslashit( $uploads['basedir'] ) . 'satispress/';

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		return (string) apply_filters( 'satispress_cache_path', $path );
	}
}
