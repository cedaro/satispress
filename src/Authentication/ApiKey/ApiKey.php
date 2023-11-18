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
	 * @var int
	 */
	public const TOKEN_LENGTH = 32;

	/**
	 * API key data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * User associated with the API key.
	 *
	 * @var WP_User
	 */
	private $user;

	/**
	 * Initialize an API key.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user  WordPress user.
	 * @param string  $token API key token.
	 * @param array   $data  Optional. Additional data associated with the key.
	 */
	public function __construct( WP_User $user, /**
  * API key token.
  */
 private readonly string $token, array $data = null ) {
		$this->user  = $user;
		$this->data  = $data ?? [];
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
			'created'    => $this->get_date( 'created' ),
			'last_used'  => $this->get_date( 'last_used' ),
			'name'       => $this->get_name(),
			'token'      => $this->get_token(),
			'user'       => $this->get_user()->ID,
			'user_login' => $this->get_user()->user_login,
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
	private function format_date( int $timestamp, string $format = null ): string {
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
	public function offsetExists( mixed $name ): bool {
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
	public function offsetGet( mixed $name ): mixed {
		$method = "get_{$name}";

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		return $this->data[ $name ] ?? null;
	}

	/**
	 * Set a field value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Field name.
	 * @param array  $value Field value.
	 */
	public function offsetSet( mixed $name, $value ): void {
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
	public function offsetUnset( mixed $name ): void {
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
	 * @return bool
	 */
	private function is_protected_field( $name ): bool {
		$protected = [ 'created', 'created_by' ];
		return \in_array( $name, $protected, true );
	}
}
