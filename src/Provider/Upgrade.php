<?php
/**
 * Upgrade routines.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use Psr\Log\LoggerInterface;
use SatisPress\Exception\SatispressException;
use SatisPress\Capabilities;
use SatisPress\Htaccess;
use SatisPress\ReleaseManager;
use SatisPress\Repository\PackageRepository;
use SatisPress\Storage\Local;
use SatisPress\Storage\Storage;

use const SatisPress\VERSION;

/**
 * Class for upgrade routines.
 *
 * @since 0.3.0
 */
class Upgrade extends AbstractHookProvider {
	/**
	 * Version option name.
	 *
	 * @var string
	 */
	const VERSION_OPTION_NAME = 'satispress_version';

	/**
	 * Htaccess handler.
	 *
	 * @var Htaccess
	 */
	protected $htaccess;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

	/**
	 * Storage service.
	 *
	 * @var Storage
	 */
	protected $storage;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $repository      Package repository.
	 * @param ReleaseManager    $release_manager Release manager.
	 * @param Storage           $storage         Storage service.
	 * @param Htaccess          $htaccess        Htaccess handler.
	 * @param LoggerInterface   $logger          Logger.
	 */
	public function __construct(
		PackageRepository $repository,
		ReleaseManager $release_manager,
		Storage $storage,
		Htaccess $htaccess,
		LoggerInterface $logger
	) {
		$this->htaccess        = $htaccess;
		$this->repository      = $repository;
		$this->release_manager = $release_manager;
		$this->storage         = $storage;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'admin_init', [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Upgrade when the database version is outdated.
	 *
	 * @since 0.3.0
	 */
	public function maybe_upgrade() {
		$saved_version = get_option( self::VERSION_OPTION_NAME, '0' );

		if ( version_compare( $saved_version, '0.3.0-dev1', '<' ) ) {
			Capabilities::register();
			$this->setup_storage();
			$this->cache_packages();
		}

		if ( version_compare( $saved_version, VERSION, '<' ) ) {
			update_option( self::VERSION_OPTION_NAME, VERSION );
		}
	}

	/**
	 * Cache existing packages.
	 *
	 * If any packages are already whitelisted before upgrading to 0.3.0, cache
	 * them so checksums can be generated for packages.json.
	 *
	 * @since 0.3.0
	 */
	protected function cache_packages() {
		foreach ( $this->repository->all() as $package ) {
			if ( ! $package->is_installed() ) {
				continue;
			}

			try {
				$this->release_manager->archive( $package->get_installed_release() );
				$this->release_manager->archive( $package->get_latest_release() );
			} catch ( SatispressException $e ) {
				$this->logger->error( 'Error archiving {package} during upgrade.', [
					'exception' => $e,
					'package'   => $package->get_name(),
				] );
			}
		}
	}

	/**
	 * Set up the local storage provider.
	 *
	 * Creates the cache path if it doesn't exist and adds an .htaccess file to
	 * prevent HTTP access on Apache.
	 *
	 * @since 0.3.0
	 */
	protected function setup_storage() {
		if ( ! $this->storage instanceof Local ) {
			return;
		}

		$base_directory = $this->storage->get_base_directory();

		$this->rename_existing_directory( $base_directory );
		$this->move_packages_into_subdirectory( $base_directory );

		if ( ! wp_mkdir_p( $base_directory ) ) {
			return;
		}

		$this->htaccess->add_rules( [ 'deny from all' ] );
		$this->htaccess->save();
	}

	/**
	 * Rename the old cache directory.
	 *
	 * The new directory contains a random suffix.
	 *
	 * @since 0.3.0
	 *
	 * @param string $directory New directory.
	 */
	protected function rename_existing_directory( $directory ) {
		$upload_config = wp_upload_dir();
		$old_path      = path_join( $upload_config['basedir'], 'satispress' );

		if ( ! file_exists( $old_path ) ) {
			return;
		}

		wp_mkdir_p( dirname( $directory ) );
		rename( $old_path, $directory );
	}

	/**
	 * Move packages into a subdirectory of the working directory.
	 *
	 * Prior to 0.3.0-dev1, packages were stored directly in the working
	 * directory. Upgrade::rename_existing_directory() should handle most cases,
	 * but this provides support for anyone that was running 0.3.0-dev.
	 *
	 * @since 0.3.0
	 *
	 * @param string $directory New directory.
	 */
	protected function move_packages_into_subdirectory( $directory ) {
		if ( file_exists( $directory ) ) {
			return;
		}

		$old_path = dirname( $directory );

		// If a /satispress-XXXX working directory exists, but the /packages
		// subdirectory doesn't, rename it to /satispress-XXXX/packages.
		if ( file_exists( $old_path ) && 'satispress' === strtok( basename( $old_path ), '-' ) ) {
			$tmpfname = dirname( $old_path ) . '/satispress-packages';
			rename( $old_path, $tmpfname );
			wp_mkdir_p( dirname( $directory ) );
			rename( $tmpfname, $directory );
		}
	}
}
