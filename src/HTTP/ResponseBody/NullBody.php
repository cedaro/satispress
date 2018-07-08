<?php
/**
 * Null response body.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP\ResponseBody;

/**
 * Null response body class.
 *
 * @since 0.3.0
 */
class NullBody implements ResponseBody {
	/**
	 * Emit the body.
	 *
	 * @since 0.3.0
	 */
	public function emit() {
		// Silence.
	}
}
