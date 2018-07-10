<?php
/**
 * API Key.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication\ApiKey;

use ArrayAccess;
use DateTime;
use DateTimeZone;
use WP_User;

/**
 * API Key class.
 *
 * @since 0.3.0
 */
final class ApiKey implements ArrayAccess {
	/**
	 * API key length.
	 *
	 * @var integer
	 */
	const TOKEN_LENGTH = 32;

	/**
	 * API key data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * API key token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * User associated with the API key.
	 *
	 * @var WP_User
	 */
	protected $user;

	/**
	 * Initialize an API key.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user  WordPress user.
	 * @param string  $token API key token.
	 * @param array   $data  Additional data associated with the key.
	 */
	public function __construct( WP_User $user, string $token, array $data = [] ) {
		$this->user  = $user;
		$this->token = $token;
		$this->data  = $data;
	}

	/**
	 * Retrieve the data associated with the API key.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * Retrieve and format a date field.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name   Field name.
	 * @param string $format Optional. Date format.
	 * @return mixed
	 */
	public function get_date( string $name, string $format = null ) {
		if ( empty( $this->data[ $name ] ) ) {
			return '';
		}

		return $this->format_date( $this->data[ $name ], $format );
	}

	/**
	 * Retrieve the API Key name.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->data['name'] ?? '';
	}

	/**
	 * Retrieve the API Key token.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_token(): string {
		return $this->token;
	}

	/**
	 * Retrieve the user associated with the API Key.
	 *
	 * @since 0.3.0
	 *
	 * @return WP_User
	 */
	public function get_user(): WP_User {
		return $this->user;
	}

	/**
	 * Convert the API Key to an array.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function to_array(): array {
		return [
			'name'      => $this['name'] ?? '',
			'user'      => $this->get_user()->ID,
			'token'     => $this->get_token(),
			'last_used' => $this->get_date( 'last_used' ),
			'created'   => $this->get_date( 'created' ),
		];
	}

	/**
	 * Format a date.
	 *
	 * @since 0.3.0
	 *
	 * @param int    $timestamp Unix timestamp.
	 * @param string $format    Optional. Date format.
	 * @return string
	 */
	protected function format_date( int $timestamp, string $format = null ) {
		$format      = $format ?: get_option( 'date_format' );
		$timezone_id = get_option( 'timezone_string' );
		$datetime    = new DateTime();

		// Handle manual offsets, like "UTC+2".
		if ( empty( $timezone_id ) ) {
			$offset = (int) get_option( 'gmt_offset', 0 );
			if ( 0 <= $offset ) {
				$formatted_offset = '+' . (string) $offset;
			} else {
				$formatted_offset = (string) $offset;
			}
			$timezone_id = str_replace(
				[ '.25', '.5', '.75' ],
				[ ':15', ':30', ':45' ],
				$formatted_offset
			);
		}

		$datetime->setTimezone( new DateTimeZone( $timezone_id ) );
		$datetime->setTimestamp( $timestamp );

		return $datetime->format( $format );
	}

	/**
	 * Whether a field exists.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Field name.
	 * @return bool
	 */
	public function offsetExists( $name ) {
		return isset( $this->data[ $name ] );
	}

	/**
	 * Retrieve a field value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	public function offsetGet( $name ) {
		$method = "get_{$name}";

		if ( method_exists( $this, $method ) ) {
			return call_user_func( [ $this, $method ] );
		}

		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return null;
	}

	/**
	 * Set a field value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Field name.
	 * @param array  $value Field value.
	 */
	public function offsetSet( $name, $value ) {
		if ( ! $this->is_protected_field( $name ) ) {
			$this->data[ $name ] = $value;
		}
	}

	/**
	 * Remove a field.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Field name.
	 */
	public function offsetUnset( $name ) {
		if ( ! $this->is_protected_field( $name ) ) {
			unset( $this->data[ $name ] );
		}
	}

	/**
	 * Whether a field is protected.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Field name.
	 * @return boolean
	 */
	protected function is_protected_field( $name ) {
		$protected = [ 'created', 'created_by' ];
		return in_array( $name, $protected, true );
	}
}
