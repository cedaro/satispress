<?php
/**
 * Route interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Route;

use SatisPress\HTTP\Request;

/**
 * Route interface.
 *
 * @package SatisPress
 * @since 0.3.0
 */
interface RouteInterface {
	/**
	 * Handle a request.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request HTTP request.
	 */
	public function handle_request( Request $request );
}
