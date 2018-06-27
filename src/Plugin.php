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

use Cedaro\WP\Plugin\Plugin as WPPlugin;
use Cedaro\WP\Plugin\Provider\I18n;

/**
 * Main plugin class - composition root.
 *
 * @since 0.3.0
 */
class Plugin extends WPPlugin implements Composable {
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

		$package_manager = $this->container->get( 'package.manager' );

		// Register hook providers.
		$this
			->register_hooks( new I18n() )
			->register_hooks( new Provider\Activation() )
			->register_hooks( new Provider\Deactivation() )
			->register_hooks( new Provider\RewriteRules() )
			->register_hooks( new Provider\CustomVendor() )
			->register_hooks( new Authentication\Basic\Request() )
			->register_hooks( new Provider\RequestHandler() )
			->register_hooks( new Provider\PackageArchiver( $package_manager ) )
			->register_hooks( new Provider\LimitLoginAttempts() );

		if ( is_admin() ) {
			$this
				->register_hooks( new Provider\AdminAssets() )
				->register_hooks( new Provider\BasicAuthenticationSettings( $this->container->get( 'htaccess.handler' ) ) )
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
}
