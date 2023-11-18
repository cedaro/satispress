<?php
/**
 * JSON response body.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP\ResponseBody;

/**
 * JSON response body class.
 *
 * @since 0.3.0
 */
class JsonBody implements ResponseBody {
	/**
	 * Create a JSON response body.
	 *
	 * @since 0.3.0
	 *
	 * @param mixed $data Response data.
	 */
	public function __construct(
     /**
      * Message data.
      */
     protected mixed $data
 )
 {
 }

	/**
	 * Emit the data as a JSON-serialized string.
	 *
	 * @since 0.3.0
	 */
	public function emit() {
		echo wp_json_encode( $this->data );
	}
}
