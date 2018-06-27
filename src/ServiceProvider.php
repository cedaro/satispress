<?php
/**
 * Plugin service definitions.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use function SatisPress\get_authorization_header;
use Composer\Semver\VersionParser;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;

/**
 * Plugin service provider class.
 *
 * @since 0.3.0
 */
class ServiceProvider implements ServiceProviderInterface {
	/**
	 * Register services.
	 *
	 * @param \Pimple\Container $container Container instance.
	 */
	public function register( PimpleContainer $container ) {
		$container['cache.path'] = function( $container ) {
			$uploads = wp_upload_dir();
			$path    = trailingslashit( $uploads['basedir'] ) . 'satispress/';

			if ( ! file_exists( $path ) ) {
				wp_mkdir_p( $path );
			}

			return (string) apply_filters( 'satispress_cache_path', $path );
		};

		$container['htaccess.handler'] = function( $container ) {
			return new Htaccess( $container['cache.path'] );
		};

		$container['package.manager'] = function( $container ) {
			return new PackageManager( $container['cache.path'] );
		};

		$container['version.parser'] = function( $container ) {
			return new ComposerVersionParser( new VersionParser() );
		};
	}
}
