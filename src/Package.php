<?php
/**
 * Package abstract class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Abstract Composer package class.
 *
 * Extended by child classes like Plugin and Theme.
 *
 * @since 0.2.0
 */
abstract class Package {
	/**
	 * Version parser.
	 *
	 * @var VersionParser
	 */
	protected $version_parser;

	/**
	 * Retrieve the package name.
	 *
	 * Includes the vendor prefix.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_package_name() {
		$vendor = apply_filters( 'satispress_vendor', 'satispress' );

		return $vendor . '/' . $this->get_slug();
	}

	/**
	 * Retrieve an array structure representing the definition of a package in
	 * packages.json.
	 *
	 * Looks up older, cached versions of packages in the SatisPress cache
	 * directory. The version numbers and archive URL are the only parts of the
	 * definition that will differ, even if additional data changes between
	 * versions.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function get_package_definition() {
		$versions = [];

		$template = [
			'name'               => $this->get_package_name(),
			'version'            => wp_strip_all_tags( $this->get_version() ),
			'version_normalized' => $this->get_version_normalized(),
			'dist'               => [
				'type'   => 'zip',
				'url'    => esc_url_raw( $this->get_archive_url() ),
				'shasum' => sha1_file( $this->archive() ),
			],
			'require'            => [
				'composer/installers' => '~1.0',
			],
			'type'               => $this->get_type(),
			'authors'            => [
				[
					'name'     => wp_strip_all_tags( $this->get_author() ),
					'homepage' => esc_url_raw( $this->get_author_uri() ),
				],
			],
			'description'        => wp_strip_all_tags( $this->get_description() ),
			'homepage'           => esc_url_raw( $this->get_homepage() ),
		];

		// Add the most current version.
		$versions[ $this->get_version() ] = $template;

		// Aside from the version number, cached version package info will match the current info.
		$cached_versions = $this->get_cached_versions();
		if ( ! empty( $cached_versions ) ) {
			foreach ( $cached_versions as $version ) {
				// Update the template.
				$template['version']            = $version;
				$template['version_normalized'] = $this->version_parser->normalize( $version );
				$template['dist']['url']        = esc_url_raw( $this->get_archive_url( $version ) );
				$template['dist']['shasum']     = sha1_file( $this->archive( $version ) );

				$versions[ $version ] = $template;
			}
		}

		return $versions;
	}

	/**
	 * Retrieve the package's archive URL for a specified version.
	 *
	 * @since 0.2.0
	 *
	 * @param string $version Optional. Package version. Defaults to the current version.
	 * @return string
	 */
	public function get_archive_url( $version = null ) {
		if ( null === $version ) {
			$version = $this->get_version();
		}

		$url = home_url( sprintf( '/satispress/%s/%s', $this->get_slug(), $version ) );

		if ( empty( get_option( 'permalink_structure' ) ) ) {
			$url = add_query_arg(
				[
					'satispress'         => $this->get_slug(),
					'satispress_version' => $version,
				],
				home_url( 'index.php' )
			);
		}

		return apply_filters( 'satispress_package_url', $url, $this, $version );
	}

	/**
	 * Retrieve a list of cached version numbers for the package.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function get_cached_versions() {
		$versions = [];

		$slug            = $this->get_slug();
		$current_version = $this->get_version();

		$package_cache_path = $this->archive_path . $slug . '/';
		if ( ! file_exists( $package_cache_path ) ) {
			return [];
		}

		$files = new \DirectoryIterator( $package_cache_path );
		if ( null !== $files ) {
			foreach ( $files as $file ) {
				if ( $file->isDot() ) {
					continue;
				}

				$version = str_replace( $slug . '-', '', basename( $file, '.zip' ) );
				if ( $version !== $current_version ) {
					$versions[] = $version;
				}
			}
		}

		return $versions;
	}

	/**
	 * Retrieve the normalized version number.
	 *
	 * @since 0.2.0
	 *
	 * @return string Normalized version number.
	 */
	public function get_version_normalized() {
		return $this->version_parser->normalize( $this->get_version() );
	}

	/**
	 * Zip a package if it doesn't exist in the cache.
	 *
	 * @since 0.2.0
	 *
	 * @param string $version Optional. Version number. Defaults to the current version.
	 * @return string Full path to the archive.
	 */
	public function archive( $version = null ) {
		if ( null === $version ) {
			$version = '';
		}

		$version            = empty( $version ) ? $this->get_version() : $version;
		$version_normalized = $this->version_parser->normalize( $version );

		$slug     = $this->get_slug();
		$filename = $this->archive_path . $slug . '/' . $slug . '-' . $version . '.zip';

		// Only create the zip if the requested version matches the current version of the plugin.
		if ( $version_normalized === $this->get_version_normalized() && ! file_exists( $filename ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

			wp_mkdir_p( dirname( $filename ) );

			$zip = new \PclZip( $filename );
			$zip->create( $this->get_path(), PCLZIP_OPT_REMOVE_PATH, $this->get_package_root() );
		}

		if ( ! file_exists( $filename ) ) {
			return false;
		}

		return $filename;
	}

	/**
	 * Whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	abstract public function is_installed();

	/**
	 * Retrieve the package author.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_author();

	/**
	 * Retrieve the package author's URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_author_uri();

	/**
	 * Retrieve the package description.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_description();

	/**
	 * Retrieve the package homepage.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_homepage();

	/**
	 * Retrieve the package name.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Retrieve the root directory for the package type.
	 *
	 * This is the directory path that will be stripped when the package is zipped.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_package_root();

	/**
	 * Retrieve the path to the package.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_path();

	/**
	 * Retrieve the package slug.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	abstract public function get_slug();

	/**
	 * Retrieve the type of Composer package.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Retrieve the package version.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_version();
}
