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
use SatisPress\REST;
use SatisPress\Repository;
use SatisPress\Screen;
use SatisPress\Storage;
use SatisPress\Transformer\ComposerPackageTransformer;
use SatisPress\Transformer\ComposerRepositoryTransformer;
use SatisPress\Validator;

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
		$container['api_key.factory'] = fn() => new ApiKey\Factory();

		$container['api_key.repository'] = fn( $container ) => new ApiKey\Repository(
			$container['api_key.factory']
		);

		$container['archiver'] = fn( $container ) => ( new Archiver( $container['logger'] ) )
				->register_validators( $container['validators.artifact'] );

		$container['authentication.servers'] = function ( $container ) {
			$servers = apply_filters(
				'satispress_authentication_servers',
				[
					20  => 'authentication.api_key',
					100 => 'authentication.unauthorized',
				]
			);

			return new ServiceIterator( $container, $servers );
		};

		$container['authentication.api_key'] = fn( $container ) => new ApiKey\Server(
			$container['api_key.repository']
		);

		$container['authentication.unauthorized'] = fn() => new Authentication\UnauthorizedServer();

		$container['hooks.activation'] = fn() => new Provider\Activation();

		$container['hooks.admin_assets'] = fn() => new Provider\AdminAssets();

		$container['hooks.authentication'] = fn( $container ) => new Provider\Authentication(
			$container['authentication.servers'],
			$container['http.request']
		);

		$container['hooks.capabilities'] = fn() => new Provider\Capabilities();

		$container['hooks.custom_vendor'] = fn() => new Provider\CustomVendor();

		$container['hooks.deactivation'] = fn() => new Provider\Deactivation();

		$container['hooks.health_check'] = fn( $container ) => new Provider\HealthCheck(
			$container['http.request']
		);

		$container['hooks.i18n'] = fn() => new I18n();

		$container['hooks.package_archiver'] = function ( $container ) {
			return new Provider\PackageArchiver(
				$container['repository.installed'],
				$container['repository.managed'],
				$container['release.manager'],
				$container['logger']
			);
		};

		$container['hooks.request_handler'] = fn( $container ) => new Provider\RequestHandler(
			$container['http.request'],
			$container['route.controllers']
		);

		$container['hooks.rest'] = fn( $container ) => new Provider\REST( $container['rest.controllers'] );

		$container['hooks.rewrite_rules'] = fn() => new Provider\RewriteRules();

		$container['hooks.upgrade'] = function ( $container ) {
			return new Provider\Upgrade(
				$container['repository.managed'],
				$container['release.manager'],
				$container['storage.local'],
				$container['htaccess.handler'],
				$container['logger']
			);
		};

		$container['htaccess.handler'] = fn( $container ) => new Htaccess( $container['storage.working_directory'] );

		$container['http.request'] = function () {
			$request = new Request( $_SERVER['REQUEST_METHOD'] ?? '' );

			// phpcs:ignore WordPress.Security.NonceVerification
			$request->set_query_params( wp_unslash( $_GET ) );
			$request->set_header( 'Authorization', get_authorization_header() );

			if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
				$request->set_header( 'PHP_AUTH_USER', $_SERVER['PHP_AUTH_USER'] );
				$request->set_header( 'PHP_AUTH_PW', $_SERVER['PHP_AUTH_PW'] ?? null );
			}

			return $request;
		};

		$container['logger'] = fn( $container ) => new Logger( $container['logger.level'] );

		$container['logger.level'] = function () {
			// Log warnings and above when WP_DEBUG is enabled.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$level = LogLevel::WARNING;
			}

			return $level ?? '';
		};

		$container['package.factory'] = fn( $container ) => new PackageFactory(
			$container['release.manager']
		);

		$container['plugin.envato_market'] = fn() => new Integration\EnvatoMarket();

		$container['plugin.members'] = fn() => new Integration\Members();

		$container['release.manager'] = fn( $container ) => new ReleaseManager(
			$container['storage.packages'],
			$container['archiver']
		);

		$container['repository.installed'] = fn( $container ) => new Repository\MultiRepository(
			[
				$container['repository.plugins'],
				$container['repository.themes'],
			]
		);

		$container['repository.managed'] = function ( $container ) {
			/**
			 * Filter the list of allowed plugins.
			 *
			 * Plugins should be added to the allowlist by appending a plugin's
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
			 * Filter the list of allowed themes.
			 *
			 * @since 0.3.0
			 *
			 * @param array $themes Array of theme slugs.
			 */
			$themes = apply_filters( 'satispress_themes', (array) get_option( 'satispress_themes', [] ) );

			$repository = $container['repository.installed']
				->with_filter(
					function ( $package ) use ( $plugins ) {
						if ( ! $package instanceof Plugin ) {
							return true;
						}

						return in_array( $package->get_basename(), $plugins, true );
					}
				)
				->with_filter(
					function ( $package ) use ( $themes ) {
						if ( ! $package instanceof Theme ) {
							return true;
						}

						return in_array( $package->get_slug(), $themes, true );
					}
				);

			return new Repository\ManagedPackages(
				$repository,
				$container['package.factory']
			);
		};

		$container['repository.plugins'] = function ( $container ) {
			return new Repository\CachedRepository(
				new Repository\InstalledPlugins(
					$container['package.factory']
				)
			);
		};

		$container['repository.themes'] = function ( $container ) {
			return new Repository\CachedRepository(
				new Repository\InstalledThemes(
					$container['package.factory']
				)
			);
		};

		$container['rest.controller.api_keys'] = function ( $container ) {
			return new REST\ApiKeysController(
				'satispress/v1',
				'apikeys',
				$container['api_key.factory'],
				$container['api_key.repository']
			);
		};

		$container['rest.controller.packages'] = function ( $container ) {
			return new REST\PackagesController(
				'satispress/v1',
				'packages',
				$container['repository.managed'],
				$container['repository.installed'],
				$container['transformer.composer_package'],
				$container['package.factory']
			);
		};

		$container['rest.controller.plugins'] = function ( $container ) {
			return new REST\InstalledPackagesController(
				'satispress/v1',
				'plugins',
				$container['repository.plugins']
			);
		};

		$container['rest.controller.themes'] = function ( $container ) {
			return new REST\InstalledPackagesController(
				'satispress/v1',
				'themes',
				$container['repository.themes']
			);
		};

		$container['rest.controllers'] = function ( $container ) {
			return new ServiceIterator(
				$container,
				[
					'api_keys' => 'rest.controller.api_keys',
					'packages' => 'rest.controller.packages',
					'plugins'  => 'rest.controller.plugins',
					'themes'   => 'rest.controller.themes',
				]
			);
		};

		$container['route.composer'] = function ( $container ) {
			return new Route\Composer(
				$container['repository.managed'],
				$container['transformer.composer_repository']
			);
		};

		$container['route.download'] = function ( $container ) {
			return new Route\Download(
				$container['repository.managed'],
				$container['release.manager']
			);
		};

		$container['route.controllers'] = function ( $container ) {
			return new ServiceLocator(
				$container,
				[
					'composer' => 'route.composer',
					'download' => 'route.download',
				]
			);
		};

		$container['screen.edit_user'] = function ( $container ) {
			return new Screen\EditUser(
				$container['api_key.repository']
			);
		};

		$container['screen.settings'] = function ( $container ) {
			return new Screen\Settings( $container['api_key.repository'] );
		};

		$container['storage.local'] = function ( $container ) {
			$path = path_join( $container['storage.working_directory'], 'packages/' );
			return new Storage\Local( $path );
		};

		$container['storage.packages'] = function ( $container ) {
			/**
			 * Filter the time to live for cached checksums.
			 *
			 * @since 3.0.0
			 *
			 * @param int $cache_ttl Time to live in seconds.
			 */
			$cache_ttl = (int) apply_filters( 'satispress_checksum_cache_ttl', MONTH_IN_SECONDS );

			return new Storage\CachedStorage( $container['storage.local'], $cache_ttl );
		};

		$container['storage.working_directory'] = function ( $container ) {
			if ( \defined( 'SATISPRESS_WORKING_DIRECTORY' ) ) {
				return SATISPRESS_WORKING_DIRECTORY;
			}

			$upload_config = wp_upload_dir();
			$path          = path_join( $upload_config['basedir'], $container['storage.working_directory_name'] );

			return (string) trailingslashit( apply_filters( 'satispress_working_directory', $path ) );
		};

		$container['storage.working_directory_name'] = function () {
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

		$container['transformer.composer_package'] = fn( $container ) => new ComposerPackageTransformer( $container['package.factory'] );

		$container['transformer.composer_repository'] = fn( $container ) => new ComposerRepositoryTransformer(
			$container['transformer.composer_package'],
			$container['release.manager'],
			$container['version.parser'],
			$container['logger']
		);

		$container['validator.hidden_directory'] = fn() => new Validator\HiddenDirectoryValidator();

		$container['validator.zip'] = fn() => new Validator\ZipValidator();

		$container['validators.artifact'] = function ( $container ) {
			$servers = apply_filters(
				'satispress_artifact_validators',
				[
					10 => 'validator.zip',
					20 => 'validator.hidden_directory',
				]
			);

			return new ServiceIterator( $container, $servers );
		};

		$container['version.parser'] = fn() => new ComposerVersionParser( new VersionParser() );
	}
}
