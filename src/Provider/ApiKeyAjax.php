<?php
/**
 * API Key AJAX handlers.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Authentication\ApiKey\Factory;
use SatisPress\Authentication\ApiKey\ApiKeyRepository;
use WP_User;

/**
 * API Key AJAX hook provider class.
 *
 * @since 0.3.0
 */
class ApiKeyAjax extends AbstractHookProvider {
	/**
	 * API Key factory.
	 *
	 * @var Factory
	 */
	protected $factory;

	/**
	 * API Key repository.
	 *
	 * @var Repository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @param Factory          $factory    API Key factory.
	 * @param ApiKeyRepository $repository API Key repository.
	 */
	public function __construct( Factory $factory, ApiKeyRepository $repository ) {
		$this->factory    = $factory;
		$this->repository = $repository;
	}

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

		$user = get_user_by( 'id', $user_id );

		$api_key = $this->factory->create( $user, [
			'name'       => sanitize_text_field( $_POST['name'] ),
			'created_by' => get_current_user_id(),
		] );

		$this->repository->save( $api_key );

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
		$api_key = $this->repository->find_by_token( $token );

		if (
			empty( $api_key )
			|| ! current_user_can( 'edit_user', $api_key->get_user()->ID )
		) {
			wp_send_json_error( [
				'message' => '',
			] );
		}

		$this->repository->revoke( $api_key );

		wp_send_json_success();
	}
}
