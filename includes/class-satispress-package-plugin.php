<?php
/**
 * Plugin package class.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.2.0
 */
class SatisPress_Package_Plugin extends SatisPress_Package {
	/**
	 * Base path where packages are cached.
	 *
	 * @since 0.2.0
	 * @type string
	 */
	protected $archive_path;

	/**
	 * Plugin basename.
	 *
	 * Relative path from the plugins directory to the main plugin file.
	 *
	 * @since 0.2.0
	 * @type string
	 */
	protected $basename;

	/**
	 * Plugin data.
	 *
	 * Data cached from get_plugin_data(). Includes plugin headers.
	 *
	 * @since 0.2.0
	 * @type array
	 */
	protected $data;

	/**
	 * Plugin slug.
	 *
	 * Created from the plugin's directory name or the name of the main file if
	 * it doesn't reside in a directory. May not match the slug in the
	 * wordpress.org repository.
	 *
	 * @since 0.2.0
	 * @type string
	 */
	protected $slug;

	/**
	 * Constructor method.
	 *
	 * @since 0.2.0
	 *
	 * @param string $basename Plugin basename (relative path from the plugins directory).
	 */
	public function __construct( $basename, $archive_path ) {
		$this->basename = $basename;
		$slug = dirname( $basename );
		$slug = ( '.' == $slug ) ? basename( $basename, '.php' ) : $slug;
		$this->slug = sanitize_title_with_dashes( $slug );
		$this->archive_path = trailingslashit( $archive_path );
	}

	/**
	 * Whether the plugin exists.
	 *
	 * @since 0.2.3
	 *
	 * @return boolean
	 */
	public function is_installed() {
		return file_exists( $this->get_file() );
	}

	/**
	 * Retrieve the plugin author.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author() {
		return $this->get_data( 'Author' );
	}

	/**
	 * Retrieve the plugin author's URL.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author_uri() {
		return $this->get_data( 'AuthorURI' );
	}

	/**
	 * Retrieve the plugin description.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->get_data( 'Description' );
	}

	/**
	 * Retrieve the plugin basename.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_basename() {
		return $this->basename;
	}

	/**
	 * Retrieve the full path to the main plugin file.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_file() {
		return WP_PLUGIN_DIR . '/' . $this->basename;
	}

	/**
	 * Retrieve the plugin homepage.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_homepage() {
		return $this->get_data( 'PluginURI' );
	}

	/**
	 * Retrieve the plugin name.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_data( 'Name' );
	}

	/**
	 * Retrieve the root directory for the package type.
	 *
	 * This is the directory path that will be stripped when the plugin is zipped.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_package_root() {
		return WP_PLUGIN_DIR;
	}

	/**
	 * Retrieve the path to the plugin.
	 *
	 * Will contain a path to a plugin directory, but if the plugin is a single
	 * file in the root of the plugins directory (WP_PLUGIN_DIR), it will be the
	 * full path, including the plugin file.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_path() {
		$plugin_file = $this->get_file();

		return ( '.' == dirname( $plugin_file ) ) ? $plugin_file : dirname( $plugin_file );
	}

	/**
	 * Retrieve the plugin slug.
	 *
	 * Created from the name of the plugin directory or the plugin file if it
	 * doesn't reside in a directory.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Retrieve the type of Composer package.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_type() {

        $type = $this->get_data( 'Type' );

		if ( $type == 'mu-plugin') {
			return "wordpress-muplugin";
		} else {
			return "wordpress-plugin";
		}

	}

	/**
	 * Retrieve the plugin version.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->get_data( 'Version' );
	}

	/**
	 * Retrieve data about the plugin.
	 *
	 * The data comes from the plugin headers.
	 * Possible values: Name, PluginURI, Description, Author, 'AuthorURI, Version
	 *
	 * @since 0.2.0
	 *
	 * @param string $prop The property to look up.
	 * @return string
	 */
	protected function get_data( $prop ) {
		if ( empty( $this->data ) ) {
			$this->data = $this->get_plugin_data( $this->get_file(), false, false );
		}

		return ( isset( $this->data[ $prop ] ) ) ? $this->data[ $prop ] : '';
	}


	/**
	 * Get the plugin details
	 *
	 * This function is a copy of the original WordPress core function, with "Plugin Type"
	 * Added for distinction wordpress-plugin and wordpress-muplugin
	 *
	 * @since 0.2.2
	 *
	 * @param	string		$plugin_file	Path to the plugin file.
	 * @param	boolean		$markup		 	If the returned data should have HTML markup applied.
	 * @param	boolean		$translate		If the returned data should be translated.
	 *
	 * @return  boolean		$markup
	 */
	protected function get_plugin_data( $plugin_file, $markup = true, $translate = true ) {

	    $default_headers = array(
			'Name' => 'Plugin Name',
	        'PluginURI' => 'Plugin URI',
	        'Version' => 'Version',
	        'Description' => 'Description',
	        'Author' => 'Author',
	        'AuthorURI' => 'Author URI',
	        'TextDomain' => 'Text Domain',
	        'DomainPath' => 'Domain Path',
			'Type' => 'Plugin Type',
	        'Network' => 'Network',
	        // Site Wide Only is deprecated in favor of Network.
	        '_sitewide' => 'Site Wide Only',
	    );

	    $plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

	    // Site Wide Only is the old header for Network
	    if ( ! $plugin_data['Network'] && $plugin_data['_sitewide'] ) {
	        /* translators: 1: Site Wide Only: true, 2: Network: true */
	        _deprecated_argument( __FUNCTION__, '3.0.0', sprintf( __( 'The %1$s plugin header is deprecated. Use %2$s instead.' ), '<code>Site Wide Only: true</code>', '<code>Network: true</code>' ) );
	        $plugin_data['Network'] = $plugin_data['_sitewide'];
	    }
	    $plugin_data['Network'] = ( 'true' == strtolower( $plugin_data['Network'] ) );
	    unset( $plugin_data['_sitewide'] );

	    // If no text domain is defined fall back to the plugin slug.
	    if ( ! $plugin_data['TextDomain'] ) {
	        $plugin_slug = dirname( plugin_basename( $plugin_file ) );
	        if ( '.' !== $plugin_slug && false === strpos( $plugin_slug, '/' ) ) {
	            $plugin_data['TextDomain'] = $plugin_slug;
	        }
	    }

	    if ( $markup || $translate ) {
	        $plugin_data = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
	    } else {
	        $plugin_data['Title']      = $plugin_data['Name'];
	        $plugin_data['AuthorName'] = $plugin_data['Author'];
	    }

	    return $plugin_data;
	}


}
