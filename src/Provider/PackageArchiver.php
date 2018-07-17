<?php
/**
 * Package archiver.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Exception\ExceptionInterface;
use SatisPress\InstalledPackage;
use SatisPress\Release;
use SatisPress\ReleaseManager;
use SatisPress\Repository\PackageRepository;

/**
 * Package archiver class.
 *
 * @since 0.3.0
 */
class PackageArchiver extends AbstractHookProvider {

	/**
	 * Installed packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $packages;

	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Whitelisted packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $whitelisted_packages;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $packages             Installed packages repository.
	 * @param PackageRepository $whitelisted_packages Whitelisted packages repository.
	 * @param ReleaseManager    $release_manager      Release manager.
	 */
	public function __construct( PackageRepository $packages, PackageRepository $whitelisted_packages, ReleaseManager $release_manager ) {
		$this->packages             = $packages;
		$this->whitelisted_packages = $whitelisted_packages;
		$this->release_manager      = $release_manager;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'add_option_satispress_plugins', [ $this, 'archive_on_option_add' ], 10, 2 );
		add_action( 'add_option_satispress_themes', [ $this, 'archive_on_option_add' ], 10, 2 );
		add_action( 'update_option_satispress_plugins', [ $this, 'archive_on_option_update' ], 10, 3 );
		add_action( 'update_option_satispress_themes', [ $this, 'archive_on_option_update' ], 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'archive_updates' ], 9999 );
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'archive_updates' ], 9999 );
	}

	/**
	 * Archive packages when they're added to the whitelist.
	 *
	 * Archiving packages when they're whitelisted helps ensure a checksum can
	 * be included in packages.json.
	 *
	 * @since 0.3.0
	 *
	 * @param string $option_name Option name.
	 * @param array  $value       Value.
	 */
	public function archive_on_option_add( string $option_name, $value ) {
		if ( empty( $value ) || ! \is_array( $value ) ) {
			return;
		}

		$type = 'satispress_plugins' === $option_name ? 'plugin' : 'theme';
		$this->archive_packages( $value, $type );
	}

	/**
	 * Archive packages when they're added to the whitelist.
	 *
	 * Archiving packages when they're whitelisted helps ensure a checksum can
	 * be included in packages.json.
	 *
	 * @since 0.3.0
	 *
	 * @param array  $old_value   Old value.
	 * @param array  $value       New value.
	 * @param string $option_name Option name.
	 */
	public function archive_on_option_update( $old_value, $value, string $option_name ) {
		$slugs = array_diff( (array) $value, (array) $old_value );

		if ( empty( $slugs ) ) {
			return;
		}

		$type = 'satispress_plugins' === $option_name ? 'plugin' : 'theme';
		$this->archive_packages( $slugs, $type );
	}

	/**
	 * Archive updates as they become available.
	 *
	 * @since 0.3.0
	 *
	 * @param object $value Update transient value.
	 * @return object
	 */
	public function archive_updates( $value ) {
		if ( empty( $value->response ) ) {
			return $value;
		}

		// The $id will be a theme slug or the plugin file.
		foreach ( $value->response as $id => $update_data ) {
			// Bail if a URL isn't available.
			if ( empty( $update_data->package ) ) {
				continue;
			}

			// Plugin data is stored as an object. Coerce to an array.
			$update_data = (array) $update_data;

			$type = isset( $update_data['plugin'] ) ? 'plugin' : 'theme';
			$slug = $update_data[ $type ];

			$args = compact( 'slug', 'type' );

			// Bail if the package isn't whitelisted.
			if ( ! $this->whitelisted_packages->contains( $args ) ) {
				continue;
			}

			try {
				$package = $this->packages->first_where( $args );

				$release = new Release(
					$package,
					$update_data['new_version'],
					$update_data['package']
				);

				$this->release_manager->archive( $release );
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( ExceptionInterface $e ) {
				// Continue processing updates.
			}
		}

		return $value;
	}

	/**
	 * Archive a list of packages.
	 *
	 * @since 0.3.0
	 *
	 * @param array  $slugs Array of package slugs.
	 * @param string $type  Type of packages.
	 */
	protected function archive_packages( array $slugs, string $type ) {
		foreach ( $slugs as $slug ) {
			$this->archive_package( $slug, $type );
		}
	}

	/**
	 * Archive a package.
	 *
	 * @since 0.3.0
	 *
	 * @param string $slug Packge slug.
	 * @param string $type Type of package.
	 */
	protected function archive_package( string $slug, string $type ) {
		try {
			$package = $this->packages->first_where( compact( 'slug', 'type' ) );

			if ( $package instanceof InstalledPackage && $package->is_installed() ) {
				$this->release_manager->archive( $package->get_installed_release() );
			}

		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( ExceptionInterface $e ) {
			// noop.
		}
	}
}
