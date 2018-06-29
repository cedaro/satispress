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
use Cedaro\WP\Plugin\Provider\I18n;
use Composer\Semver\VersionParser as ComposerVersionParser;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceIterator;
use Pimple\ServiceProviderInterface;
use Pimple\Psr11\ServiceLocator;
use SatisPress\Authentication;
use SatisPress\HTTP\Request;
use SatisPress\Provider;
use SatisPress\Screen;
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

		$container['authentication.servers'] = function( $container ) {
			$servers = apply_filters( 'satispress_authentication_servers', [
				20  => 'authentication.basic',
				100 => 'authentication.unauthorized',
			] );

			return new ServiceIterator( $container, $servers );
		};

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

		$container['hooks.activation'] = function( $container ) {
			return new Provider\Activation();
		};

		$container['hooks.admin_assets'] = function( $container ) {
			return new Provider\AdminAssets();
		};

		$container['hooks.authentication'] = function( $container ) {
			return new Provider\Authentication( $container['authentication.servers'] );
		};

		$container['hooks.custom_vendor'] = function( $container ) {
			return new Provider\CustomVendor();
		};

		$container['hooks.deactivation'] = function( $container ) {
			return new Provider\Deactivation();
		};

		$container['hooks.i18n'] = function( $container ) {
			return new I18n();
		};

		$container['hooks.package_archiver'] = function( $container ) {
			$locator = new ServiceLocator( $container, [
				'package.manager',
				'release.manager',
			] );

			return new Provider\PackageArchiver( $locator );
		};

		$container['hooks.request_handler'] = function( $container ) {
			return new Provider\RequestHandler(
				$container['http.request'],
				$container['route.controllers']
			);
		};

		$container['hooks.rewrite_rules'] = function( $container ) {
			return new Provider\RewriteRules();
		};

		$container['hooks.upgrade'] = function( $container ) {
			$locator = new ServiceLocator( $container, [
				'package.manager',
				'htaccess' => 'htaccess.handler',
				'release.manager',
				'storage',
			] );

			return new Provider\Upgrade( $locator );
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

		$container['route.controllers'] = function( $container ) {
			return new ServiceLocator( $container, [
				'composer' => 'route.composer',
				'download' => 'route.download',
			] );
		};

		$container['screen.manage_plugins'] = function( $container ) {
			return new Screen\ManagePlugins( $container['package.manager'] );
		};

		$container['screen.settings'] = function( $container ) {
			return new Screen\Settings( $container['package.manager'] );
		};

		$container['storage'] = function( $container ) {
			return new Storage\Local( $container['cache.path'] );
		};

		$container['version.parser'] = function( $container ) {
			return new VersionParser( new ComposerVersionParser() );
		};
	}
}
