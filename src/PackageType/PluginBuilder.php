<?php
/**
 * Plugin builder.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use function SatisPress\is_plugin_file;
use SatisPress\Package;

/**
 * Plugin builder class.
 *
 * @since 0.3.0
 */
final class PluginBuilder extends InstalledPackageBuilder {
	/**
	 * Set the plugin basename.
	 *
	 * @param string $basename Relative path from the main plugin directory.
	 * @return PluginBuilder
	 */
	public function set_basename( string $basename ): self {
		return $this->set( 'basename', $basename );
	}

	/**
	 * Create a plugin from source.
	 *
	 * @since 0.3.0
	 *
	 * @param string $plugin_file Relative path to the main plugin file.
	 * @param array  $plugin_data Optional. Array of plugin data.
	 * @return PluginBuilder
	 */
	public function from_source( string $plugin_file, array $plugin_data = [] ): self {
		$slug = $this->get_slug_from_plugin_file( $plugin_file );

		// Account for single-file plugins.
		$directory = '.' === \dirname( $plugin_file ) ? '' : \dirname( $plugin_file );

		if ( empty( $plugin_data ) ) {
			$plugin_data = get_plugin_data( path_join( WP_PLUGIN_DIR, $plugin_file ) );
		}

		return $this
			->set_author( $plugin_data['AuthorName'] )
			->set_author_url( $plugin_data['AuthorURI'] )
			->set_basename( $plugin_file )
			->set_description( $plugin_data['Description'] )
			->set_directory( path_join( WP_PLUGIN_DIR, $directory ) )
			->set_name( $plugin_data['Name'] )
			->set_homepage( $plugin_data['PluginURI'] )
			->set_installed( true )
			->set_installed_version( $plugin_data['Version'] )
			->set_slug( $slug )
			->set_type( 'plugin' )
			->add_cached_releases();
	}

	/**
	 * Set properties from an existing package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package.
	 * @return $this
	 */
	public function with_package( Package $package ): PackageBuilder {
		parent::with_package( $package );

		if ( $package instanceof Plugin ) {
			$this->set_basename( $package->get_basename() );
		}

		return $this;
	}

	/**
	 * Retrieve a plugin slug.
	 *
	 * @since 0.3.0
	 *
	 * @param string $plugin_file Plugin slug or relative path to the main plugin
	 *                            file from the plugins directory.
	 * @return string
	 */
	protected function get_slug_from_plugin_file( $plugin_file ): string {
		if ( ! is_plugin_file( $plugin_file ) ) {
			return $plugin_file;
		}

		$slug = \dirname( $plugin_file );

		// Account for single file plugins.
		$slug = '.' === $slug ? basename( $plugin_file, '.php' ) : $slug;

		return $slug;
	}
}
