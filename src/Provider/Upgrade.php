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

use const SatisPress\VERSION;
use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Storage\Local;

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

		if ( version_compare( $saved_version, VERSION, '<' ) ) {
			$this->setup_storage();
		}

		if ( version_compare( $saved_version, VERSION, '<' ) ) {
			update_option( self::VERSION_OPTION_NAME, VERSION );
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
	public function setup_storage() {
		$container     = $this->plugin->get_container();
		$cache_path    = $container->get( 'cache.path' );
		$storage       = $container->get( 'storage' );
		$htaccess      = $container->get( 'htaccess.handler' );
		$upload_config = wp_upload_dir();

		if ( ! $storage instanceof Local ) {
			return;
		}

		// Rename the old /satispress directory if it exists.
		// The new directory contains a random suffix.
		$old_path = path_join( $upload_config['basedir'], 'satispress' );
		if ( file_exists( $old_path ) ) {
			rename( $old_path, $storage->get_base_directory() );
		}

		if ( ! wp_mkdir_p( $storage->get_base_directory() ) ) {
			return;
		}

		$htaccess->add_rules( [ 'deny from all' ] );
		$htaccess->save();
	}
}
