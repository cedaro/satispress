<?php
/**
 * Theme class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use SatisPress\Package;
use SatisPress\VersionParser;

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
	 * @param string        $theme_directory Name of the theme directory.
	 * @param string        $archive_path    Base path where packages are cached.
	 * @param VersionParser $version_parser  Version parser.
	 */
	public function __construct( string $theme_directory, string $archive_path, VersionParser $version_parser ) {
		$this->slug           = $theme_directory;
		$this->theme          = wp_get_theme( $theme_directory );
		$this->archive_path   = trailingslashit( $archive_path );
		$this->version_parser = $version_parser;
	}

	/**
	 * Whether the theme is installed.
	 *
	 * @since 0.2.3
	 *
	 * @return boolean
	 */
	public function is_installed(): bool {
		return $this->theme->exists();
	}

	/**
	 * Retrieve the theme author.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author(): string {
		return $this->theme->get( 'Author' );
	}

	/**
	 * Retrieve the theme author's URL.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_author_uri(): string {
		return $this->theme->get( 'AuthorURI' );
	}

	/**
	 * Retrieve the theme description.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->theme->get( 'Description' );
	}

	/**
	 * Retrieve the theme homepage.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_homepage(): string {
		return $this->theme->get( 'ThemeURI' );
	}

	/**
	 * Retrieve the theme name.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_name(): string {
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
	public function get_package_root(): string {
		return get_theme_root( $this->get_slug() );
	}

	/**
	 * Retrieve the path to the theme.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_path(): string {
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
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Retrieve the type of Composer package.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'wordpress-theme';
	}

	/**
	 * Retrieve the theme version.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->theme->get( 'Version' );
	}
}
