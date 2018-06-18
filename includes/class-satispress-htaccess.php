<?php
/**
 * SatisPress_Htaccess class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

/**
 * Interact with the .htaccess file.
 *
 * @since 0.2.0
 */
class SatisPress_Htaccess {
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
	protected $rules = array();

	/**
	 * Constructor method.
	 *
	 * @since 0.2.0
	 *
	 * @param string $path Directory path where .htaccess is located.
	 */
	public function __construct( $path = '' ) {
		$this->set_path( $path );
	}

	/**
	 * Set the directory path where .htaccess is located.
	 *
	 * @since 0.2.0
	 *
	 * @param string $path Directory path where .htaccess is located.
	 */
	public function set_path( $path ) {
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
	 * Determine if the .htaccess file is writable.
	 *
	 * @since 0.2.0
	 *
	 * @return bool
	 */
	public function is_writable() {
		$file = $this->get_file();
		return ( ! file_exists( $file ) && is_writable( $this->path ) ) || is_writable( $file );
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
