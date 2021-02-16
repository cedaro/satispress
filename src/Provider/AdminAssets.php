<?php
/**
 * Assets provider.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Assets provider class.
 *
 * @since 0.3.0
 */
class AdminAssets extends AbstractHookProvider {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 1 );
		add_filter( 'script_loader_tag', [ $this, 'filter_script_type' ], 10, 3 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 0.3.0
	 */
	public function register_assets() {
		wp_register_script(
			'satispress-admin',
			$this->plugin->get_url( 'assets/js/admin.js' ),
			[ 'jquery' ],
			'20210215',
			true
		);

		wp_register_script(
			'satispress-access',
			$this->plugin->get_url( 'assets/js/access.js' ),
			[ 'wp-components', 'wp-data', 'wp-data-controls', 'wp-element', 'wp-i18n' ],
			'20210211',
			true
		);

		wp_set_script_translations(
			'satispress-access',
			'satispress',
			$this->plugin->get_path( 'languages' )
		);

		wp_register_script(
			'satispress-repository',
			$this->plugin->get_url( 'assets/js/repository.js' ),
			[ 'wp-components', 'wp-data', 'wp-data-controls', 'wp-element', 'wp-i18n' ],
			'20210211',
			true
		);

		wp_set_script_translations(
			'satispress-repository',
			'satispress',
			$this->plugin->get_path( 'languages' )
		);

		wp_register_style(
			'satispress-admin',
			$this->plugin->get_url( 'assets/css/admin.css' ),
			[ 'wp-components' ],
			'20180816'
		);
	}

	/**
	 * Filter script tag type attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag    Script tag HTML.
	 * @param string $handle Script identifier.
	 * @return string
	 */
	public function filter_script_type( string $tag, string $handle ): string {
		$modules = [
			'satispress-access',
			'satispress-repository',
		];

		if ( in_array( $handle, $modules, true ) ) {
			$tag = str_replace( '<script', '<script type="module"', $tag );
		}

		return $tag;
	}
}
