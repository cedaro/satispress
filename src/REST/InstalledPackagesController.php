<?php
/**
 * Installed packages REST controller.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

declare ( strict_types = 1 );

namespace SatisPress\REST;

use SatisPress\Capabilities;
use SatisPress\Package;
use SatisPress\Repository\PackageRepository;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Installed packages REST controller class.
 *
 * @since 1.0.0
 */
class InstalledPackagesController extends WP_REST_Controller {
	/**
	 * Package slug pattern.
	 *
	 * @var string
	 */
	final public const SLUG_PATTERN = '[^.\/]+(?:\/[^.\/]+)?';

	/**
	 * Package repository.
	 *
	 * @var PackageRepository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string            $namespace  The namespace for this controller's route.
	 * @param string            $rest_base  The base of this controller's route.
	 * @param PackageRepository $repository Package repository.
	 */
	public function __construct(
		string $namespace,
		string $rest_base,
		PackageRepository $repository
	) {
		$this->namespace  = $namespace;
		$this->rest_base  = $rest_base;
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
				esc_html__( 'Sorry, you are not allowed to view installed packages.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieve a collection of packages.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = [];

		foreach ( $this->repository->all() as $slug => $package ) {
			$data    = $this->prepare_item_for_response( $package, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Retrieve the query parameters for collections of packages.
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
	 * @param Package         $package Package instance.
	 * @param WP_REST_Request $request Request instance.
	 * @return WP_REST_Response Response instance.
	 */
	public function prepare_item_for_response( $package, $request ) {
		$data = [
			'slug'        => $package->get_slug(),
			'name'        => $package->get_name(),
			'description' => $package->get_description(),
			'homepage'    => $package->get_homepage(),
			'author'      => $package->get_author(),
			'author_url'  => esc_url( $package->get_author_url() ),
			'type'        => $package->get_type(),
		];

		$data = $this->filter_response_by_context( $data, $request['context'] );

		return rest_ensure_response( $data );
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
			'title'      => 'package',
			'type'       => 'object',
			'properties' => [
				'author'      => [
					'description' => esc_html__( 'The name of the package author.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'author_url'  => [
					'description' => esc_html__( 'The package author URL.', 'satispress' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'description' => [
					'description' => esc_html__( 'The package description.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'embed' ],
					'readonly'    => true,
				],
				'homepage'    => [
					'description' => esc_html__( 'The package URL.', 'satispress' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'name'        => [
					'description' => esc_html__( 'The name of the package.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'embed' ],
					'readonly'    => true,
				],
				'slug'        => [
					'description' => esc_html__( 'The package slug.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'embed' ],
					'readonly'    => true,
				],
				'type'        => [
					'description' => esc_html__( 'Type of package.', 'satispress' ),
					'type'        => 'string',
					'enum'        => [ 'plugin', 'theme' ],
					'context'     => [ 'view', 'embed' ],
					'readonly'    => true,
				],
			],
		];
	}
}
