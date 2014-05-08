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
		return 'wordpress-plugin';
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
			$this->data = get_plugin_data( $this->get_file(), false, false );
		}

		return ( isset( $this->data[ $prop ] ) ) ? $this->data[ $prop ] : '';
	}
}
