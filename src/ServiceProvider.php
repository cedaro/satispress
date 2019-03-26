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

use Cedaro\WP\Plugin\Provider\I18n;
use Composer\Semver\VersionParser;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceIterator;
use Pimple\ServiceProviderInterface;
use Pimple\Psr11\ServiceLocator;
use Psr\Log\LogLevel;
use SatisPress\Authentication\ApiKey;
use SatisPress\Authentication;
use SatisPress\HTTP\Request;
use SatisPress\Integration;
use SatisPress\Logger;
use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\Theme;
use SatisPress\Provider;
use SatisPress\Repository;
use SatisPress\Screen;
use SatisPress\Storage;
use SatisPress\Transformer\ComposerPackageTransformer;
use SatisPress\Transformer\ComposerRepositoryTransformer;

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
		$container['api_key.factory'] = function() {
			return new ApiKey\Factory();
		};

		$container['api_key.repository'] = function( $container ) {
			return new ApiKey\Repository(
				$container['api_key.factory']
			);
		};

		$container['archiver'] = function( $container ) {
			return new Archiver( $container['logger'] );
		};

		$container['authentication.servers'] = function( $container ) {
			$servers = apply_filters(
				'satispress_authentication_servers',
				[
					20  => 'authentication.api_key',
					100 => 'authentication.unauthorized',
				]
			);

			return new ServiceIterator( $container, $servers );
		};

		$container['authentication.api_key'] = function( $container ) {
			return new ApiKey\Server(
				$container['api_key.repository']
			);
		};

		$container['authentication.unauthorized'] = function( $container ) {
			return new Authentication\UnauthorizedServer();
		};

		$container['hooks.activation'] = function() {
			return new Provider\Activation();
		};

		$container['hooks.admin_assets'] = function() {
			return new Provider\AdminAssets();
		};

		$container['hooks.ajax.api_key'] = function( $container ) {
			return new Provider\ApiKeyAjax(
				$container['api_key.factory'],
				$container['api_key.repository']
			);
		};

		$container['hooks.authentication'] = function( $container ) {
			return new Provider\Authentication(
				$container['authentication.servers'],
				$container['http.request']
			);
		};

		$container['hooks.capabilities'] = function() {
			return new Provider\Capabilities();
		};

		$container['hooks.custom_vendor'] = function() {
			return new Provider\CustomVendor();
		};

		$container['hooks.deactivation'] = function() {
			return new Provider\Deactivation();
		};

		$container['hooks.i18n'] = function() {
			return new I18n();
		};

		$container['hooks.package_archiver'] = function( $container ) {
			return new Provider\PackageArchiver(
				$container['repository.installed'],
				$container['repository.whitelist'],
				$container['release.manager'],
				$container['logger']
			);
		};

		$container['hooks.request_handler'] = function( $container ) {
			return new Provider\RequestHandler(
				$container['http.request'],
				$container['route.controllers']
			);
		};

		$container['hooks.rewrite_rules'] = function() {
			return new Provider\RewriteRules();
		};

		$container['hooks.upgrade'] = function( $container ) {
			return new Provider\Upgrade(
				$container['repository.whitelist'],
				$container['release.manager'],
				$container['storage.packages'],
				$container['htaccess.handler'],
				$container['logger']
			);
		};

		$container['htaccess.handler'] = function( $container ) {
			return new Htaccess( $container['storage.working_directory'] );
		};

		$container['http.request'] = function() {
			$request = new Request( $_SERVER['REQUEST_METHOD'] );

			// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$request->set_query_params( wp_unslash( $_GET ) );
			$request->set_header( 'Authorization', get_authorization_header() );

			if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
				$request->set_header( 'PHP_AUTH_USER', $_SERVER['PHP_AUTH_USER'] );
				$request->set_header( 'PHP_AUTH_PW', $_SERVER['PHP_AUTH_PW'] ?? null );
			}

			return $request;
		};

		$container['logger'] = function( $container ) {
			return new Logger( $container['logger.level'] );
		};

		$container['logger.level'] = function( $container ) {
			// Log warnings and above when WP_DEBUG is enabled.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$level = LogLevel::WARNING;
			}

			return $level ?? '';
		};

		$container['package.factory'] = function( $container ) {
			return new PackageFactory(
				$container['release.manager']
			);
		};

		$container['plugin.members'] = function() {
			return new Integration\Members();
		};

		$container['release.manager'] = function( $container ) {
			return new ReleaseManager(
				$container['storage.packages'],
				$container['archiver']
			);
		};

		$container['repository.installed'] = function( $container ) {
			return new Repository\MultiRepository(
				[
					$container['repository.plugins'],
					$container['repository.themes'],
				]
			);
		};

		$container['repository.plugins'] = function( $container ) {
			return new Repository\CachedRepository(
				new Repository\InstalledPlugins(
					$container['package.factory']
				)
			);
		};

		$container['repository.themes'] = function( $container ) {
			return new Repository\CachedRepository(
				new Repository\InstalledThemes(
					$container['package.factory']
				)
			);
		};

		$container['repository.whitelist'] = function( $container ) {
			/**
			 * Filter the list of whitelisted plugins.
			 *
			 * Plugins should be added to the whitelist by appending a plugin's
			 * basename to the array. The basename is the main plugin file's
			 * relative path from the root plugin directory.
			 *
			 * Example: plugin-name/plugin-name.php
			 *
			 * @since 0.3.0
			 *
			 * @param array $plugins Array of plugin basenames.
			 */
			$plugins = apply_filters( 'satispress_plugins', (array) get_option( 'satispress_plugins', [] ) );

			/**
			 * Filter the list of whitelisted themes.
			 *
			 * @since 0.3.0
			 *
			 * @param array $themes Array of theme slugs.
			 */
			$themes = apply_filters( 'satispress_themes', (array) get_option( 'satispress_themes', [] ) );

			return $container['repository.installed']
				->with_filter(
					function( $package ) use ( $plugins ) {
						if ( ! $package instanceof Plugin ) {
							return true;
						}

							return in_array( $package->get_basename(), $plugins, true );
					}
				)
				->with_filter(
					function( $package ) use ( $themes ) {
						if ( ! $package instanceof Theme ) {
							return true;
						}

							return in_array( $package->get_slug(), $themes, true );
					}
				);
		};

		$container['route.composer'] = function( $container ) {
			return new Route\Composer(
				$container['repository.whitelist'],
				$container['transformer.composer_repository']
			);
		};

		$container['route.download'] = function( $container ) {
			return new Route\Download(
				$container['repository.whitelist'],
				$container['release.manager']
			);
		};

		$container['route.controllers'] = function( $container ) {
			return new ServiceLocator(
				$container,
				[
					'composer' => 'route.composer',
					'download' => 'route.download',
				]
			);
		};

		$container['screen.edit_user'] = function( $container ) {
			return new Screen\EditUser(
				$container['api_key.repository']
			);
		};

		$container['screen.manage_plugins'] = function( $container ) {
			return new Screen\ManagePlugins( $container['repository.whitelist'] );
		};

		$container['screen.settings'] = function( $container ) {
			return new Screen\Settings(
				$container['repository.whitelist'],
				$container['api_key.repository'],
				$container['transformer.composer_package']
			);
		};

		$container['storage.packages'] = function( $container ) {
			$path = path_join( $container['storage.working_directory'], 'packages/' );
			return new Storage\Local( $path );
		};

		$container['storage.working_directory'] = function( $container ) {
			if ( \defined( 'SATISPRESS_WORKING_DIRECTORY' ) ) {
				return SATISPRESS_WORKING_DIRECTORY;
			}

			$upload_config = wp_upload_dir();
			$path          = path_join( $upload_config['basedir'], $container['storage.working_directory_name'] );

			return (string) trailingslashit( apply_filters( 'satispress_working_directory', $path ) );
		};

		$container['storage.working_directory_name'] = function() {
			$directory = get_option( 'satispress_working_directory' );

			if ( ! empty( $directory ) ) {
				return $directory;
			}

			// Use old option name if it exists.
			$directory = get_option( 'satispress_cache_directory' );
			delete_option( 'satispress_cache_directory' );

			if ( empty( $directory ) ) {
				// Append a random string to help hide it from nosey visitors.
				$directory = sprintf( 'satispress-%s', generate_random_string() );
			}

			update_option( 'satispress_working_directory', $directory );

			return $directory;
		};

		$container['transformer.composer_package'] = function( $container ) {
			return new ComposerPackageTransformer( $container['package.factory'] );
		};

		$container['transformer.composer_repository'] = function( $container ) {
			return new ComposerRepositoryTransformer(
				$container['transformer.composer_package'],
				$container['release.manager'],
				$container['version.parser'],
				$container['logger']
			);
		};

		$container['version.parser'] = function() {
			return new ComposerVersionParser( new VersionParser() );
		};
	}
}
