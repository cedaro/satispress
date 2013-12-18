<?php
/**
 * Package class.
 *
 * @package SatisPress
 * @since 0.2.0
 */
class SatisPress_Package {
	/**
	 * Plugin object.
	 *
	 * @access protected
	 * @var SatisPress_Plugin
	 */
	protected $plugin;

	/**
	 * Constructor method.
	 *
	 * @since 0.2.0
	 *
	 * @param SatisPress_Plugin $plugin A plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Retrieve the package name.
	 *
	 * Includes the vendor prefix.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_name() {
		$vendor = apply_filters( 'satispress_vendor', 'satispress' );

		return $vendor . '/' . $this->plugin->get_slug();
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
			'name'                    => $this->get_name(),
			'version'                 => wp_strip_all_tags( $this->plugin->get_version() ),
			'version_normalized'      => $this->plugin->get_version( 'normalized' ),
			'dist'                    => array(
				'type'                => 'zip',
				'url'                 => esc_url_raw( $this->get_archive_url() ),
			),
			'require'                 => array(
				'composer/installers' => '~1.0',
			),
			'type'                    => 'wordpress-plugin',
			'authors'                 => array(
				array(
					'name'            => wp_strip_all_tags( $this->plugin->get_author() ),
					'homepage'        => esc_url_raw( $this->plugin->get_author_uri() ),
				),
			),
			'description'             => wp_strip_all_tags( $this->plugin->get_description() ),
			'homepage'                => esc_url_raw( $this->plugin->get_plugin_uri() ),
		);

		// Add the most current version.
		$versions[ $this->plugin->get_version() ] = $template;

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
			$version = $this->plugin->get_version();
		}

		$permalink = get_option( 'permalink_structure' );
		if ( empty( $permalink ) ) {
			$url = add_query_arg(
				array(
					'satispress'         => $this->plugin->get_slug(),
					'satispress_version' => $version,
				),
				home_url( 'index.php' )
			);
		} else {
			$url = home_url( sprintf( '/satispress/%s/%s', $this->plugin->get_slug(), $version ) );
		}

		return $url;
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
		$version = empty( $version ) ? $this->plugin->get_version() : $version;
		$version_normalized = SatisPress_Version_Parser::normalize( $version );

		$slug = $this->plugin->get_slug();
		$base_path = SatisPress::instance()->cache_path();
		$filename = $base_path . $slug . '/' . $slug . '-' . $version . '.zip';

		// Only create the zip if the requested version matches the current version of the plugin.
		if ( $version_normalized == $this->plugin->get_version( 'normalized' ) && ! file_exists( $filename ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			wp_mkdir_p( dirname( $filename ) );

			$zip = new PclZip( $filename );
			$zip->create( $this->plugin->get_path(), PCLZIP_OPT_REMOVE_PATH, WP_PLUGIN_DIR );
		}

		if ( ! file_exists( $filename ) ) {
			return false;
		}

		return $filename;
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

		$slug = $this->plugin->get_slug();
		$current_version = $this->plugin->get_version();

		$base_path = SatisPress::instance()->cache_path();
		$plugin_cache_path = $base_path . $slug . '/';
		if ( ! file_exists( $plugin_cache_path ) ) {
			return array();
		}

		$files = new DirectoryIterator( $plugin_cache_path );
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
}
