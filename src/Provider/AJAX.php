<?php
/**
 * AJAX handlers.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Authentication\ApiKey;
use WP_User;

/**
 * AJAX hook provider class.
 *
 * @since 0.3.0
 */
class Ajax extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_satispress_create_api_key', [ $this, 'create_api_key' ] );
		add_action( 'wp_ajax_satispress_delete_api_key', [ $this, 'delete_api_key' ] );
	}

	/**
	 * Create an API Key.
	 *
	 * @since 0.3.0
	 */
	public function create_api_key() {
		if ( ! isset( $_POST['name'], $_POST['nonce'], $_POST['user'] ) ) {
			wp_send_json_error( [
				'message' => '',
			] );
		}

		check_ajax_referer( 'create-api-key', 'nonce' );

		$user_id = absint( $_POST['user'] );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error( [
				'message' => '',
			] );
		}

		$user    = get_user_by( 'id', $user_id );
		$api_key = ApiKey::create( $user, [
			'name'       => sanitize_text_field( $_POST['name'] ),
			'created_by' => get_current_user_id(),
		] );

		wp_send_json_success( $api_key->to_array() );
	}

	/**
	 * Delete an API Key.
	 *
	 * @since 0.3.0
	 */
	public function delete_api_key() {
		if ( ! isset( $_POST['nonce'], $_POST['token'] ) ) {
			wp_send_json_error( [
				'message' => '',
			] );
		}

		check_ajax_referer( 'delete-api-key', 'nonce' );

		$token   = sanitize_text_field( $_POST['token'] );
		$api_key = ApiKey::find_by_token( $token );

		if (
			empty( $api_key )
			|| ! current_user_can( 'edit_user', $api_key->get_user()->ID )
		) {
			wp_send_json_error( [
				'message' => '',
			] );
		}

		$api_key->revoke();

		wp_send_json_success();
	}
}
