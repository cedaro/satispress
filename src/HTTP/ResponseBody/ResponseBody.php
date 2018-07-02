<?php
/**
 * Response body interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP\ResponseBody;

/**
 * Response body interface.
 *
 * @since 0.3.0
 */
interface ResponseBody {
	/**
	 * Emit the response body.
	 */
	public function emit();
}
