<?php
/**
 * SatisPress request handler.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use Psr\Container\ContainerInterface;
use SatisPress\HTTP\Request;
use SatisPress\HTTP\Response;
use SatisPress\Route\Route;
use WP;
use WP_REST_Server;

/**
 * Request handler class.
 *
 * @since 0.3.0
 */
class RequestHandler extends AbstractHookProvider {
	/**
	 * Route controllers.
	 *
	 * @var ContainerInterface
	 */
	protected $controllers;

	/**
	 * Server request.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param Request            $request     Request instance.
	 * @param ContainerInterface $controllers Route controllers.
	 */
	public function __construct( Request $request, ContainerInterface $controllers ) {
		$this->request     = $request;
		$this->controllers = $controllers;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'parse_request', [ $this, 'dispatch' ] );
	}

	/**
	 * Dispatch the request to an endpoint.
	 *
	 * @since 0.3.0
	 *
	 * @param WP $wp Main WP instance.
	 * @throws \Exception If an exception is caught and debug mode is enabled.
	 */
	public function dispatch( WP $wp ) {
		if ( empty( $wp->query_vars['satispress_route'] ) ) {
			return;
		}

		$route = $wp->query_vars['satispress_route'];
		$this->request->set_route( $route );

		if ( ! empty( $wp->query_vars['satispress_params'] ) ) {
			$this->request->set_url_params( $wp->query_vars['satispress_params'] );
		}

		try {
			$response = $this->check_authentication();

			if ( null === $response ) {
				$controller = $this->get_route_controller( $route );
				$response   = $controller->handle( $this->request );
			}
		} catch ( \Exception $e ) {
			if ( $this->is_debug_mode() ) {
				throw $e;
			}

			$response = Response::from_exception( $e );
		}

		$this->send_headers( $response->get_headers() );
		status_header( $response->get_status() );
		$response->get_body()->emit();
		exit;
	}

	/**
	 * Check authentication.
	 *
	 * Calls the WP_REST_Server authentication method to leverage authentication
	 * handlers built for the REST API.
	 *
	 * @since 0.3.0
	 *
	 * @see WP_REST_Server::check_authentication()
	 *
	 * @return null|Response
	 */
	protected function check_authentication() {
		$server = new WP_REST_Server();
		$result = $server->check_authentication();

		if ( ! is_wp_error( $result ) ) {
			return null;
		}

		return Response::from_rest_authentication_error( $result );
	}

	/**
	 * Send an HTTP header.
	 *
	 * @since 0.3.0
	 *
	 * @see WP_REST_Server::send_header()
	 *
	 * @param string      $name  Header name.
	 * @param string|bool $value Header value.
	 */
	protected function send_header( string $name, $value ) {
		/*
		 * Sanitize as per RFC2616 (Section 4.2):
		 *
		 * Any LWS that occurs between field-content MAY be replaced with a
		 * single SP before interpreting the field value or forwarding the
		 * message downstream.
		 */
		$value = preg_replace( '/\s+/', ' ', $value );
		header( sprintf( '%s: %s', $name, $value ) );
	}

	/**
	 * Send HTTP headers.
	 *
	 * @since 0.3.0
	 *
	 * @param array $headers HTTP headers.
	 */
	protected function send_headers( array $headers ) {
		foreach ( $headers as $name => $value ) {
			$this->send_header( $name, $value );
		}
	}

	/**
	 * Retrieve the controller for a route.
	 *
	 * @since 0.3.0
	 *
	 * @param string $route Route identifier.
	 * @return Route
	 */
	protected function get_route_controller( string $route ): Route {
		return $this->controllers->get( $route );
	}

	/**
	 * Whether debug mode is enabled.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	protected function is_debug_mode(): bool {
		return \defined( 'WP_DEBUG' ) && true === WP_DEBUG;
	}
}
