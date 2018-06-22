<?php
/**
 * Authentication settings abstract class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication;

/**
 * Authentication settings abstract class.
 *
 * @since 0.3.0
 */
abstract class Settings {
	/**
	 * Authenticate requests for SatisPress packages.
	 *
	 * @since 0.3.0
	 */
	abstract public function add_settings();

	/**
	 * Retrieve a setting.
	 *
	 * @since 0.2.0
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $default Optional. Default setting value.
	 * @return mixed
	 */
	protected function get_setting( string $key, $default = null ) {
		$option = get_option( 'satispress' );

		return isset( $option[ $key ] ) ? $option[ $key ] : $default;
	}
}
