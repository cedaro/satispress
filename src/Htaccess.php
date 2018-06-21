<?php
/**
 * Htaccess class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress;

/**
 * Interact with the .htaccess file.
 *
 * @since 0.2.0
 */
class Htaccess {
	/**
	 * The directory path where .htaccess is located.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	protected $path = '';

	/**
	 * .htaccess rules.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Constructor method.
	 *
	 * @since 0.2.0
	 *
	 * @param string $path Optional. Directory path where .htaccess is located. Default is empty string.
	 */
	public function __construct( $path = null ) {
		if ( null === $path ) {
			$path = '';
		}

		$this->path = $path;
	}

	/**
	 * Add rules to .htaccess.
	 *
	 * @since 0.2.0
	 *
	 * @param array $rules List of rules to add.
	 */
	public function add_rules( $rules ) {
		$this->rules = array_merge( $this->rules, (array) $rules );
	}

	/**
	 * Retrieve the full path to the .htaccess file itself.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_file() {
		return $this->path . '.htaccess';
	}

	/**
	 * Retrieve the rules in the .htaccess file.
	 *
	 * Only contains the rules between the #SatisPress delimiters.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function get_rules() {
		return apply_filters( 'satispress_htaccess_rules', $this->rules );
	}

	/**
	 * Determine if the .htaccess file exists.
	 *
	 * @since 0.3.0
	 *
	 * @return bool True if hte file exists, false otherwise.
	 */
	public function file_exists() {
		return file_exists( $this->get_file() );
	}

	/**
	 * Determine if the .htaccess file is writable.
	 *
	 * @since 0.2.0
	 *
	 * @return bool
	 */
	public function is_writable() {
		$file = $this->get_file();
		return ( ! $this->file_exists() && is_writable( $this->path ) ) || is_writable( $file );
	}

	/**
	 * Save rules to the .htaccess file.
	 *
	 * @since 0.2.0
	 */
	public function save() {
		$rules = $this->get_rules();
		insert_with_markers( $this->get_file(), 'SatisPress', $rules );
	}
}
