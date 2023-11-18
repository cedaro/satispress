<?php
/**
 * Envato Market plugin integration.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.7.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Integration;

use Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Envato Market plugin integration provider class.
 *
 * @since 0.7.0
 */
class EnvatoMarket extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.7.0
	 */
	public function register_hooks() {
		add_filter( 'satispress_package_download_url', [ $this, 'filter_package_download_url' ] );
	}

	/**
	 * Filter the download URL for package updates from Envato.
	 *
	 * The Envato Market plugin inserts a placeholder URL in the plugin and
	 * theme update transients until just before a package is downloaded. It
	 * then requests the download URL from a remote API and swaps it out.
	 *
	 * @since 0.7.0
	 *
	 * @link https://github.com/envato/wp-envato-market
	 * @see Envato_Market_Admin::maybe_deferred_download()
	 *
	 * @param string $download_url Download URL.
	 * @return string
	 */
	public function filter_package_download_url( string $download_url ): string {
		if ( str_contains( $download_url, 'envato-market' ) && false !== strrpos( $download_url, 'deferred_download' ) ) {
			parse_str( (string) wp_parse_url( $download_url, PHP_URL_QUERY ), $vars );

			// Don't send a URL if the actual download URL can't be determined.
			$download_url = '';

			if ( $vars['item_id'] ) {
				$args         = $this->get_bearer_args( $vars['item_id'] );
				$download_url = envato_market()->api()->download( $vars['item_id'], $args );
			}
		}

		return is_string($download_url) ? $download_url : '';
	}

	/**
	 * Retrieves the bearer arguments for a request with a single use API Token.
	 *
	 * @since 0.7.0
	 *
	 * @link https://build.envato.com/api/#market_0_getBuyerDownload
	 * @see Envato_Market_Admin::set_bearer_args()
	 *
	 * @param string $id Item id.
	 * @return array
	 */
	protected function get_bearer_args( string $id ): array {
		$token = '';
		$items = envato_market()->get_option( 'items', [] );

		foreach ( $items as $item ) {
			if ( (int) $item['id'] === (int) $id ) {
				$token = $item['token'];
				break;
			}
		}

		if ( empty( $token ) ) {
			return [];
		}

		return [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
			],
		];
	}
}
