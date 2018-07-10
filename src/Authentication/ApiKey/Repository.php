<?php
/**
 * API Key repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication\ApiKey;

use WP_User;
use WP_User_Query;

/**
 * API Key repository class.
 *
 * @since 0.3.0
 */
class Repository implements ApiKeyRepository {
	/**
	 * Prefix for user meta keys.
	 *
	 * @var string
	 */
	const META_PREFIX = 'satispress_api_key.';

	/**
	 * API Key factory.
	 *
	 * @var Factory
	 */
	protected $factory;

	/**
	 * Create the API Key repository.
	 *
	 * @since 0.3.0
	 *
	 * @param Factory $factory API Key factory.
	 */
	public function __construct( Factory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * Find an API Key by its token value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $token API Key token.
	 * @return ApiKey|null
	 */
	public function find_by_token( string $token ) {
		$meta_key = static::get_meta_key( $token );

		$query = new WP_User_Query( [
			'number'      => 1,
			'count_total' => false,
			'meta_query'  => [
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

		return $this->factory->create( $user, $data, $token );
	}

	/**
	 * Retrieve all API keys for a given user.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user WordPress user.
	 * @return ApiKey[] List of API keys.
	 */
	public function find_for_user( WP_User $user ): array {
		$meta = get_user_meta( $user->ID );
		$keys = [];

		foreach ( $meta as $meta_key => $values ) {
			if ( 0 !== strpos( $meta_key, static::META_PREFIX ) ) {
				continue;
			}

			$token  = substr( $meta_key, \strlen( static::META_PREFIX ) );
			$data   = maybe_unserialize( $values[0] );
			$keys[] = $this->factory->create( $user, $data, $token );
		}

		return $keys;
	}

	/**
	 * Revoke an API Key.
	 *
	 * @since 0.3.0
	 *
	 * @param ApiKey $api_key API Key.
	 */
	public function revoke( ApiKey $api_key ) {
		delete_user_meta(
			$api_key->get_user()->ID,
			static::get_meta_key( $api_key->get_token() )
		);
	}

	/**
	 * Save an API Key.
	 *
	 * @since 0.3.0
	 *
	 * @param ApiKey $api_key API Key.
	 * @return ApiKey API Key.
	 */
	public function save( ApiKey $api_key ): ApiKey {
		update_user_meta(
			$api_key->get_user()->ID,
			static::get_meta_key( $api_key->get_token() ),
			$api_key->get_data()
		);

		return $api_key;
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
}
