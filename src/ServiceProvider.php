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
use SatisPress\HTTP\Request;

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

		$container['http.request'] = function( $container ) {
			$request = new Request( $_SERVER['REQUEST_METHOD'] );

			$request->set_query_params( wp_unslash( $_GET ) );
			$request->set_header( 'Authorization', get_authorization_header() );

			if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
				$request->set_header( 'PHP_AUTH_USER', $_SERVER['PHP_AUTH_USER'] );
				$request->set_header( 'PHP_AUTH_PW', isset( $_SERVER['PHP_AUTH_PW'] ) ? $_SERVER['PHP_AUTH_PW'] : null );
			}

			return $request;
		};

		$container['package.manager'] = function( $container ) {
			return new PackageManager( $container['cache.path'] );
		};

		$container['route.composer'] = function( $container ) {
			return new Route\Composer(
				$container['package.manager'],
				$container['version.parser']
			);
		};

		$container['route.download'] = function( $container ) {
			return new Route\Download(
				$container['package.manager']
			);
		};

		$container['version.parser'] = function( $container ) {
			return new ComposerVersionParser( new VersionParser() );
		};
	}
}
