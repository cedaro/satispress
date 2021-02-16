<?php
/**
 * Packages REST controller.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

declare ( strict_types = 1 );

namespace SatisPress\REST;

use SatisPress\Capabilities;
use SatisPress\Exception\FileNotFound;
use SatisPress\Package;
use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\Theme;
use SatisPress\Repository\PackageRepository;
use SatisPress\Transformer\PackageTransformer;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Packages REST controller class.
 *
 * @since 1.0.0
 */
class PackagesController extends WP_REST_Controller {
	/**
	 * Package slug pattern.
	 *
	 * @var string
	 */
	const SLUG_PATTERN = '[^.\/]+(?:\/[^.\/]+)?';

	/**
	 * Composer package transformer.
	 *
	 * @var PackageTransformer
	 */
	protected $composer_transformer;

	/**
	 * Installed packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $installed_packages;

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
	 * @param string             $namespace            The namespace for this controller's route.
	 * @param string             $rest_base            The base of this controller's route.
	 * @param PackageRepository  $repository           Package repository.
	 * @param PackageRepository  $installed_packages   Installed packages repository.
	 * @param PackageTransformer $composer_transformer Package transformer.
	 */
	public function __construct(
		string $namespace,
		string $rest_base,
		PackageRepository $repository,
		PackageRepository $installed_packages,
		PackageTransformer $composer_transformer
	) {
		$this->namespace            = $namespace;
		$this->rest_base            = $rest_base;
		$this->repository           = $repository;
		$this->installed_packages   = $installed_packages;
		$this->composer_transformer = $composer_transformer;
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
			'/' . $this->rest_base . '/(?P<slug>' . self::SLUG_PATTERN . ')',
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'show_in_index'       => false,
				],
				'args'   => [
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					'slug'    => [
						'description' => esc_html( 'The package slug.', 'satispress' ),
						'type'        => 'string',
						'pattern'     => self::SLUG_PATTERN,
					],
					'type'    => [
						'description' => esc_html__( 'Type of package.', 'satispress' ),
						'type'        => 'string',
						'enum'        => [ 'plugin', 'theme' ],
						'context'     => [ 'view', 'edit' ],
						'required'    => true,
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Check if a given request has access to view the resources.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( Capabilities::VIEW_PACKAGES ) ) {
			return new WP_Error(
				'rest_cannot_read',
				esc_html__( 'Sorry, you are not allowed to view packages.', 'satispress' ),
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

		$repository = $this->repository->with_filter(
			function( $package ) use ( $request ) {
				return in_array( $package->get_type(), $request['type'], true );
			}
		);

		foreach ( $repository->all() as $slug => $package ) {
			$data    = $this->prepare_item_for_response( $package, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Check if a given request has access to create a resource.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( Capabilities::MANAGE_OPTIONS ) ) {
			return new WP_Error(
				'rest_cannot_create',
				esc_html__( 'Sorry, you are not allowed to add packages to the repository.', 'satispress' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		$args = wp_array_slice_assoc( $request, [ 'slug', 'type' ] );
		if ( ! $this->installed_packages->contains( $args ) ) {
			return new WP_Error(
				'rest_resource_invalid_id',
				esc_html__( 'Invalid package.', 'satispress' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	/**
	 * Add a package to the repository.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$args    = wp_array_slice_assoc( $request, [ 'slug', 'type' ] );
		$package = $this->installed_packages->first_where( $args );

		if ( $package instanceof Plugin ) {
			$plugins   = (array) get_option( 'satispress_plugins', [] );
			$plugins[] = $package->get_basename();
			$plugins   = array_filter( array_unique( $plugins ) );
			sort( $plugins );

			update_option( 'satispress_plugins', $plugins );
		} elseif ( $package instanceof Theme ) {
			$themes   = (array) get_option( 'satispress_themes', [] );
			$themes[] = $package->get_slug();
			$themes   = array_filter( array_unique( $themes ) );
			sort( $themes );

			update_option( 'satispress_themes', $themes );
		}

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $package, $request );
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
		if ( ! current_user_can( Capabilities::MANAGE_OPTIONS ) ) {
			return new WP_Error(
				'rest_cannot_delete',
				esc_html__( 'Sorry, you are not allowed to delete this package.', 'satispress' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$args = wp_array_slice_assoc( $request, [ 'slug', 'type' ] );
		if ( ! $this->repository->contains( $args ) ) {
			return new WP_Error(
				'rest_resource_invalid_id',
				esc_html__( 'Invalid package.', 'satispress' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	/**
	 * Delete a package from the repository.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$args = wp_array_slice_assoc( $request, [ 'slug', 'type' ] );

		$package = $this->repository->first_where( $args );

		if ( $package instanceof Plugin ) {
			$plugins = (array) get_option( 'satispress_plugins', [] );
			$plugins = array_diff( $plugins, [ $package->get_basename() ] );
			update_option( 'satispress_plugins', $plugins );
		} elseif ( $package instanceof Theme ) {
			$themes = (array) get_option( 'satispress_themes', [] );
			$themes = array_diff( $themes, [ $package->get_slug() ] );
			update_option( 'satispress_themes', $themes );
		}

		$request->set_param( 'context', 'edit' );
		$previous = $this->prepare_item_for_response( $package, $request );

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
	 * Retrieve the query parameters for collections of packages.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
		];

		$params['type'] = [
			'description'       => esc_html__( 'Limit results to packages of one or more types.', 'satispress' ),
			'type'              => 'array',
			'items'             => [
				'type' => 'string',
			],
			'default'           => [ 'plugin', 'theme' ],
			'sanitize_callback' => 'wp_parse_slug_list',
		];

		return $params;
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
		$composer = $this->composer_transformer->transform( $package );

		if ( $package instanceof Plugin ) {
			$id = substr( $package->get_basename(), 0, - 4 );
		} elseif ( $package instanceof Theme ) {
			$id = $package->get_slug();
		}

		$data = [
			'id'          => $id,
			'slug'        => $package->get_slug(),
			'name'        => $package->get_name(),
			'description' => $package->get_description(),
			'homepage'    => $package->get_homepage(),
			'author'      => $package->get_author(),
			'author_url'  => esc_url( $package->get_author_url() ),
			'type'        => $package->get_type(),
		];

		$data['composer'] = [
			'name' => $composer->get_name(),
			'type' => $composer->get_type(),
		];

		$data['releases'] = $this->prepare_releases_for_response( $package, $request );

		$data = $this->filter_response_by_context( $data, $request['context'] );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepare package releases for response.
	 *
	 * @param Package         $package Package instance.
	 * @param WP_REST_Request $request WP request instance.
	 * @return array
	 */
	protected function prepare_releases_for_response( Package $package, WP_REST_Request $request ) {
		$releases = [];

		foreach ( $package->get_releases() as $release ) {
			// Skip if the current user can't view this release.
			if ( ! current_user_can( Capabilities::VIEW_PACKAGE, $package, $release ) ) {
				continue;
			}

			$version = $release->get_version();

			try {
				$releases[] = [
					'url'     => $release->get_download_url(),
					'version' => $version,
				];
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( FileNotFound $e ) {
				// Skip if the release artifact is missing.
			}
		}

		return array_values( $releases );
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
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'author_url'  => [
					'description' => esc_html__( 'The package author URL.', 'satispress' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'composer'    => [
					'description' => esc_html__( 'Package data formatted for Composer.', 'satispress' ),
					'type'        => 'object',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
					'properties'  => [
						'name' => [
							'description' => __( 'Composer package name.', 'satispress' ),
							'type'        => 'string',
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
						],
						'type' => [
							'description' => __( 'Composer package type.', 'satispress' ),
							'type'        => 'string',
							'enum'        => [ 'wordpress-plugin', 'wordpress-theme' ],
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
						],
					],
				],
				'description' => [
					'description' => esc_html__( 'The package description.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit', 'embed' ],
					'readonly'    => true,
				],
				'homepage'    => [
					'description' => esc_html__( 'The package URL.', 'satispress' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'name'        => [
					'description' => esc_html__( 'The name of the package.', 'satispress' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit', 'embed' ],
					'readonly'    => true,
				],
				'releases'    => [
					'description' => esc_html__( 'A list of package releases.', 'satispress' ),
					'type'        => 'array',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
					'items'       => [
						'type'       => 'object',
						'readonly'   => true,
						'properties' => [
							'url'     => [
								'description' => esc_html__( 'A URL to download the release.', 'satispress' ),
								'type'        => 'string',
								'format'      => 'uri',
								'readonly'    => true,
							],
							'version' => [
								'description' => esc_html__( 'The release version.', 'satispress' ),
								'type'        => 'string',
								'readonly'    => true,
							],
						],
					],
				],
				'slug'        => [
					'description' => esc_html__( 'The package slug.', 'satispress' ),
					'type'        => 'string',
					'pattern'     => self::SLUG_PATTERN,
					'context'     => [ 'view', 'edit', 'embed' ],
					'required'    => true,
				],
				'type'        => [
					'description' => esc_html__( 'Type of package.', 'satispress' ),
					'type'        => 'string',
					'enum'        => [ 'plugin', 'theme' ],
					'context'     => [ 'view', 'edit', 'embed' ],
					'required'    => true,
				],
			],
		];
	}
}
