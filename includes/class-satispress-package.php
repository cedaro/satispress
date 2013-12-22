<?php
/**
 * Package class.
 *
 * @package SatisPress
 * @since 0.2.0
 */
class SatisPress_Package {
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
	 * @todo Consider persisting additional data in a package.json in each cache
	 *       directory.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_package_definition() {
		$versions = array();

		$template = array(
			'name'                    => $this->get_package_name(),
			'version'                 => wp_strip_all_tags( $this->get_version() ),
			'version_normalized'      => $this->get_version_normalized(),
			'dist'                    => array(
				'type'                => 'zip',
				'url'                 => esc_url_raw( $this->get_archive_url() ),
			),
			'require'                 => array(
				'composer/installers' => '~1.0',
			),
			'type'                    => $this->get_type(),
			'authors'                 => array(
				array(
					'name'            => wp_strip_all_tags( $this->get_author() ),
					'homepage'        => esc_url_raw( $this->get_author_uri() ),
				),
			),
			'description'             => wp_strip_all_tags( $this->get_description() ),
			'homepage'                => esc_url_raw( $this->get_homepage() ),
		);

		// Add the most current version.
		$versions[ $this->get_version() ] = $template;

		// Aside from the version number, cached version package info will match the current info.
		$cached_versions = $this->get_cached_versions();
		if ( ! empty( $cached_versions ) ) {
			foreach ( $cached_versions as $version ) {
				// Update the template.
				$template['version'] = $version;
				$template['version_normalized'] = SatisPress_Version_Parser::normalize( $version );
				$template['dist']['url'] = esc_url_raw( $this->get_archive_url( $version ) );

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
	public function get_archive_url( $version = '' ) {
		$url = '';

		if ( empty( $version ) ) {
			$version = $this->get_version();
		}

		$permalink = get_option( 'permalink_structure' );
		if ( empty( $permalink ) ) {
			$url = add_query_arg(
				array(
					'satispress'         => $this->get_slug(),
					'satispress_version' => $version,
				),
				home_url( 'index.php' )
			);
		} else {
			$url = home_url( sprintf( '/satispress/%s/%s', $this->get_slug(), $version ) );
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
		$versions = array();

		$slug = $this->get_slug();
		$current_version = $this->get_version();

		$base_path = SatisPress::instance()->cache_path();
		$package_cache_path = $base_path . $slug . '/';
		if ( ! file_exists( $package_cache_path ) ) {
			return array();
		}

		$files = new DirectoryIterator( $package_cache_path );
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' == $file || '..' == $file ) {
					continue;
				}

				$version = str_replace( $slug . '-', '', basename( $file, '.zip' ) );
				if ( $version != $current_version ) {
					$versions[] = $version;
				}
			}
		}

		return $versions;
	}

	/**
	 *
	 */
	public function get_version_normalized() {
		return SatisPress_Version_Parser::normalize( $this->get_version() );
	}

	/**
	 * Zip a package if it doesn't exist in the cache.
	 *
	 * @since 0.2.0
	 *
	 * @param string $version Optional. Version number. Defaults to the current version.
	 * @return string Full path to the archive.
	 */
	public function archive( $version = '' ) {
		$version = empty( $version ) ? $this->get_version() : $version;
		$version_normalized = SatisPress_Version_Parser::normalize( $version );

		$slug = $this->get_slug();
		$base_path = SatisPress::instance()->cache_path();
		$filename = $base_path . $slug . '/' . $slug . '-' . $version . '.zip';

		// Only create the zip if the requested version matches the current version of the plugin.
		if ( $version_normalized == $this->get_version_normalized() && ! file_exists( $filename ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			wp_mkdir_p( dirname( $filename ) );

			$zip = new PclZip( $filename );
			$zip->create( $this->get_path(), PCLZIP_OPT_REMOVE_PATH, $this->get_package_root() );
		}

		if ( ! file_exists( $filename ) ) {
			return false;
		}

		return $filename;
	}
}
