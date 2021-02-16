<?php
/**
 * API Keys REST controller.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

declare ( strict_types = 1 );

namespace SatisPress\REST;

use SatisPress\Authentication\ApiKey\ApiKey;
use SatisPress\Authentication\ApiKey\ApiKeyRepository;
use SatisPress\Authentication\ApiKey\Factory;
use SatisPress\Capabilities;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * API Keys REST controller class.
 *
 * @since 1.0.0
 */
class ApiKeysController extends WP_REST_Controller {
	/**
	 * API Key token pattern.
	 *
	 * @var string
	 */
	const TOKEN_PATTERN = '[A-Za-z0-9]{32}';

	/**
	 * API Key factory.
	 *
	 * @var Factory
	 */
	protected $factory;

	/**
	 * API Key repository.
	 *
	 * @var ApiKeyRepository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $namespace  The namespace for this controller's route.
	 * @param string           $rest_base  The base of this controller's route.
	 * @param Factory          $factory    API Key factory.
	 * @param ApiKeyRepository $repository API key repository.
	 */
	public function __construct(
		string $namespace,
		string $rest_base,
		Factory $factory,
		ApiKeyRepository $repository
	) {
		$this->namespace  = $namespace;
		$this->rest_base  = $rest_base;
		$this->factory    = $factory;
		$this->repository = $repository;
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'show_in_index'       => false,
					'args'                => $this->get_collection_params(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'show_in_index'       => false,
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<token>' . self::TOKEN_PATTERN . ')',
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'show_in_index'       => false,
				],
				'args'   => [
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					'token'   => [
						'type'              => 'string',
						'pattern'           => self::TOKEN_PATTERN,
						'required'          => true,
						'sanitize_callback' => function ( $value ) {
							return preg_replace( '/[^A-Za-z0-9]+/', '', $value );
						},
					],
					'user'    => [
						'description' => esc_html__( 'The ID for the user associated with the API Key.', 'satispress' ),
						'type'        => 'integer',
						'context'     => [ 'view', 'edit' ],
						'required'    => true,
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Check if a given request has access to view the resource.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( Capabilities::MANAGE_OPTIONS ) ) {
			return new WP_Error(
				'rest_cannot_read',
				esc_html__( 'Sorry, you are not allowed to view API keys.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! current_user_can( 'edit_user', $request['user'] ) ) {
			return new WP_Error(
				'rest_cannot_read',
				esc_html__( 'Sorry, you are not allowed to view API keys for this user.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieve a collection of API Keys.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = [];

		$user     = get_user_by( 'id', $request['user'] );
		$api_keys = $this->repository->find_for_user( $user );

		foreach ( $api_keys as $api_key ) {
			$data    = $this->prepare_item_for_response( $api_key, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Check if a given request has access to create a resource.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( Capabilities::MANAGE_OPTIONS ) ) {
			return new WP_Error(
				'rest_cannot_create',
				esc_html__( 'Sorry, you are not allowed to create API keys.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! current_user_can( 'edit_user', $request['user'] ) ) {
			return new WP_Error(
				'rest_cannot_read',
				esc_html__( 'Sorry, you are not allowed to create API keys for this user.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Create a resource.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$user = get_user_by( 'id', $request['user'] );

		$api_key = $this->factory->create(
			$user,
			[
				'name'       => sanitize_text_field( $request['name'] ),
				'created_by' => get_current_user_id(),
			]
		);

		$this->repository->save( $api_key );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $api_key, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Check if a given request has access to delete a resource.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$api_key = $this->repository->find_by_token( $request['token'] );
		if ( null === $api_key ) {
			return new WP_Error(
				'rest_resource_invalid_id',
				esc_html__( 'Invalid API Key token.', 'satispress' ),
				[ 'status' => 404 ]
			);
		}

		if ( ! current_user_can( Capabilities::MANAGE_OPTIONS ) ) {
			return new WP_Error(
				'rest_cannot_delete',
				esc_html__( 'Sorry, you are not allowed to delete API keys.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! current_user_can( 'edit_user', $request['user'] ) ) {
			return new WP_Error(
				'rest_cannot_read',
				esc_html__( 'Sorry, you are not allowed to delete API Keys for this user.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Delete a resource.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$api_key = $this->repository->find_by_token( $request['token'] );
		$this->repository->revoke( $api_key );

		$request->set_param( 'context', 'edit' );
		$previous = $this->prepare_item_for_response( $api_key, $request );

		$response = new WP_REST_Response();
		$response->set_data(
			[
				'deleted'  => true,
				'previous' => $previous->get_data(),
			]
		);

		return $response;
	}

	/**
	 * Retrieve the query parameters for collections of API Keys.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
		];
	}

	/**
	 * Prepare a single package output for response.
	 *
	 * @since 1.0.0
	 *
	 * @param ApiKey          $api_key API Key instance.
	 * @param WP_REST_Request $request Request instance.
	 * @return WP_REST_Response Response instance.
	 */
	public function prepare_item_for_response( $api_key, $request ) {
		$data = $api_key->to_array();
		$data = $this->filter_response_by_context( $data, $request['context'] );

		return rest_ensure_response( $data );
	}

	/**
	 * Checks that the "token" parameter is a valid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token API Key token.
	 * @return bool
	 */
	protected function validate_token_param( $token ) {
		if ( is_string( $token ) && ! preg_match( '/' . self::TOKEN_PATTERN . '/', $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the package schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'apikey',
			'type'       => 'object',
			'properties' => [
				'created'    => [
					'description' => esc_html__( 'The date the API key was created.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'last_used'  => [
					'description' => esc_html__( 'The date the API key was last used.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'name'       => [
					'description' => esc_html__( 'A descriptive name for the API key.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit', 'embed' ],
					'required'    => true,
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
				'token'      => [
					'description' => esc_html__( 'The API Key token.', 'satispress' ),
					'type'        => 'string',
					'pattern'     => self::TOKEN_PATTERN,
					'context'     => [ 'view', 'edit', 'embed' ],
					'readonly'    => true,
				],
				'user'       => [
					'description' => esc_html__( 'The ID for the user associated with the API Key.', 'satispress' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit', 'embed' ],
					'required'    => true,
				],
				'user_login' => [
					'description' => esc_html__( 'The username for the user associated with the API key.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
			],
		];
	}
}
