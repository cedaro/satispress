<?php
/**
 * Theme class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress\PackageType;

use SatisPress\Package;

/**
 * Theme package class.
 *
 * @since 0.2.0
 */
class Theme extends Package {
	/**
	 * Base path where packages are cached.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	protected $archive_path;

	/**
	 * Theme slug.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	protected $slug;

	/**
	 * Theme data.
	 *
	 * Data cached from wp_get_theme(). Includes theme headers.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $theme;

	/**
	 * Constructor method.
	 *
	 * @since 0.2.0
	 *
	 * @param string $theme_directory Name of the theme directory.
	 * @param string $archive_path    Base path where packages are cached.
	 */
	public function __construct( $theme_directory, $archive_path ) {
		$this->slug         = $theme_directory;
		$this->theme        = wp_get_theme( $theme_directory );
		$this->archive_path = trailingslashit( $archive_path );
	}

	/**
	 * Whether the theme is installed.
	 *
	 * @since 0.2.3
	 *
	 * @return boolean
	 */
	public function is_installed() {
		return $this->theme->exists();
	}

	/**
	 * Retrieve the theme author.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author() {
		return $this->theme->get( 'Author' );
	}

	/**
	 * Retrieve the theme author's URL.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author_uri() {
		return $this->theme->get( 'AuthorURI' );
	}

	/**
	 * Retrieve the theme description.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->theme->get( 'Description' );
	}

	/**
	 * Retrieve the theme homepage.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_homepage() {
		return $this->theme->get( 'ThemeURI' );
	}

	/**
	 * Retrieve the theme name.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->theme->get( 'Name' );
	}

	/**
	 * Retrieve the root directory for the package type.
	 *
	 * This is the directory path that will be stripped when the theme is zipped.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_package_root() {
		return get_theme_root( $this->get_slug() );
	}

	/**
	 * Retrieve the path to the theme.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->theme->get_stylesheet_directory();
	}

	/**
	 * Retrieve the theme slug.
	 *
	 * Created from the name of the theme directory or the theme file if it
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
		return 'wordpress-theme';
	}

	/**
	 * Retrieve the theme version.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->theme->get( 'Version' );
	}
}
