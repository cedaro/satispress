<?php
/**
 * API key.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication;

use function SatisPress\generate_random_string;
use ArrayAccess;
use DateTime;
use DateTimeZone;
use WP_User;
use WP_User_Query;

/**
 * API key class.
 *
 * @since 0.3.0
 */
final class ApiKey implements ArrayAccess {
	/**
	 * Prefix for user meta keys.
	 *
	 * @var string
	 */
	const META_PREFIX = 'satispress_api_key.';

	/**
	 * API key length.
	 *
	 * @var integer
	 */
	const TOKEN_LENGTH = 32;

	/**
	 * String to prepend to API keys.
	 *
	 * @var string
	 */
	const TOKEN_PREFIX = '';

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
	 * Revoke the API key.
	 *
	 * @since 0.3.0
	 */
	public function revoke() {
		delete_user_meta(
			$this->get_user()->ID,
			$this->get_meta_key( $this->token )
		);
	}

	/**
	 * Save the API Key.
	 *
	 * @since 0.3.0
	 *
	 * @return $this
	 */
	public function save(): self {
		update_user_meta(
			$this->get_user()->ID,
			static::get_meta_key( $this->token ),
			$this->data
		);

		return $this;
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
	 * Create and save a new API key for a user.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user WordPress user.
	 * @param array   $data Optional. Additional data associated with the API key.
	 * @return ApiKey
	 */
	public static function create( WP_User $user, array $data = [] ): ApiKey {
		if ( ! isset( $data['created'] ) ) {
			$data['created'] = time();
		}

		$token = static::generate_token();

		add_user_meta(
			$user->ID,
			static::get_meta_key( $token ),
			$data
		);

		return new static( $user, $token, $data );
	}

	/**
	 * Find an API key using its token.
	 *
	 * @since 0.3.0
	 *
	 * @param string $token API key token.
	 * @return ApiKey|null
	 */
	public static function find_by_token( string $token ) {
		$meta_key = static::get_meta_key( $token );

		$query = new WP_User_Query( [
			'number'      => 1,
			'count_total' => false,
			'meta_query'  => [ // WPCS: slow query OK.
				[
					'key'     => $meta_key,
					'compare' => 'EXISTS',
				],
			],
		] );

		$users = $query->get_results();
		if ( empty( $users ) ) {
			return null;
		}

		$user = $users[0];
		$data = get_user_meta( $user->ID, wp_slash( $meta_key ), true );

		return new static( $user, $token, $data );
	}

	/**
	 * Retrieve all API keys for a given user.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user WordPress user.
	 * @return static[] List of API keys.
	 */
	public static function find_for_user( WP_User $user ) {
		$meta = get_user_meta( $user->ID );
		$keys = [];

		foreach ( $meta as $meta_key => $values ) {
			if ( 0 !== strpos( $meta_key, static::META_PREFIX ) ) {
				continue;
			}

			$token  = substr( $meta_key, strlen( static::META_PREFIX ) );
			$data   = maybe_unserialize( $values[0] );
			$keys[] = new static( $user, $token, $data );
		}

		return $keys;
	}

	/**
	 * Generate an API key token.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function generate_token(): string {
		return static::TOKEN_PREFIX . generate_random_string( static::TOKEN_LENGTH - strlen( static::TOKEN_PREFIX ), false );
	}

	/**
	 * Retrieve the meta key for saving API key data.
	 *
	 * @since 0.3.0
	 *
	 * @param string $token API key token.
	 * @return string
	 */
	protected static function get_meta_key( string $token ): string {
		return static::META_PREFIX . $token;
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
