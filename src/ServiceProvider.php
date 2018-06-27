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

use function SatisPress\generate_random_string;
use function SatisPress\get_authorization_header;
use Composer\Semver\VersionParser;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use SatisPress\Authentication;
use SatisPress\HTTP\Request;
use SatisPress\Storage;

/**
 * Plugin service provider class.
 *
 * @since 0.3.0
 */
class ServiceProvider implements ServiceProviderInterface {
	/**
	 * Register services.
	 *
	 * @param PimpleContainer $container Container instance.
	 */
	public function register( PimpleContainer $container ) {
		$container['archiver'] = function( $container ) {
			return new Archiver();
		};

		$container['authentication.servers'] = [
			20  => 'authentication.basic',
			100 => 'authentication.unauthorized',
		];

		$container['authentication.basic'] = function( $container ) {
			return new Authentication\Basic\Server(
				$container['http.request']
			);
		};

		$container['authentication.unauthorized'] = function( $container ) {
			return new Authentication\UnauthorizedServer(
				$container['http.request']
			);
		};

		$container['cache.directory'] = function( $container ) {
			$directory = get_option( 'satispress_cache_directory' );

			if ( ! empty( $directory ) ) {
				return $directory;
			}

			// Append a random string to help hide it from nosey visitors.
			$directory = sprintf( 'satispress-%s', generate_random_string() );
			update_option( 'satispress_cache_directory', $directory );

			return $directory;
		};

		$container['cache.path'] = function( $container ) {
			if ( defined( 'SATISPRESS_CACHE_PATH' ) ) {
				return SATISPRESS_CACHE_PATH;
			}

			$upload_config = wp_upload_dir();
			$path          = trailingslashit( path_join( $upload_config['basedir'], $container['cache.directory'] ) );

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

		$container['package.factory'] = function( $container ) {
			return new PackageFactory(
				$container['release.manager']
			);
		};

		$container['package.manager'] = function( $container ) {
			return new PackageManager(
				$container['package.factory']
			);
		};

		$container['release.manager'] = function( $container ) {
			return new ReleaseManager(
				$container['storage'],
				$container['archiver']
			);
		};

		$container['route.composer'] = function( $container ) {
			return new Route\Composer(
				$container['package.manager'],
				$container['release.manager'],
				$container['version.parser']
			);
		};

		$container['route.download'] = function( $container ) {
			return new Route\Download(
				$container['package.manager'],
				$container['release.manager']
			);
		};

		$container['storage'] = function( $container ) {
			return new Storage\Local( $container['cache.path'] );
		};

		$container['version.parser'] = function( $container ) {
			return new ComposerVersionParser( new VersionParser() );
		};
	}
}
