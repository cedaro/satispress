<?php
/**
 * API Key factory.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication\ApiKey;

use function SatisPress\generate_random_string;
use WP_User;
use WP_User_Query;

/**
 * API Key factory class.
 *
 * @since 0.3.0
 */
final class Factory {
	/**
	 * Create a new API key for a user.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_User $user  WordPress user.
	 * @param array   $data  Optional. Additional data associated with the API key.
	 * @param string  $token Optional. API Key token.
	 * @return ApiKey
	 */
	public function create( WP_User $user, array $data = [], string $token = null ): ApiKey {
		if ( ! isset( $data['created'] ) ) {
			$data['created'] = time();
		}

		if ( empty( $token ) ) {
			$token = self::generate_token();
		}

		return new ApiKey( $user, $token, $data );
	}

	/**
	 * Generate an API key token.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	protected static function generate_token(): string {
		return generate_random_string( ApiKey::TOKEN_LENGTH, false );
	}
}
