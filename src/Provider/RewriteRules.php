<?php
 /**
  * Register rewrite rules.
  *
  * @package SatisPress
  * @license GPL-2.0-or-later
  * @since 0.3.0
  */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Class to register rewrite rules.
 *
 * @since 0.3.0
 */
class RewriteRules extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_filter( 'query_vars',                              array( $this, 'register_query_vars' ) );
		add_action( 'init',                                    array( $this, 'register_rewrite_rules' ) );
		add_action( 'generate_rewrite_rules',                  array( $this, 'register_external_rewrite_rules' ) );
		register_activation_hook( $this->plugin->get_file(),   array( $this, 'activate' ) );
		register_deactivation_hook( $this->plugin->get_file(), array( $this, 'deactivate' ) );
		add_action( 'wp_loaded',                               array( $this, 'maybe_flush_rewrite_rules' ) );
	}

	/**
	 * Register query variables.
	 *
	 * @since 0.3.0
	 *
	 * @param array $vars List of query variables.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'satispress';
		$vars[] = 'satispress_version';
		return $vars;
	}

	/**
	 * Register rewrite rules.
	 *
	 * @since 0.3.0
	 */
	public function register_rewrite_rules() {
		add_rewrite_rule(
			'satispress/([^/]+)(/([^/]+))?/?$',
			'index.php?satispress=$matches[1]&satispress_version=$matches[3]',
			'top'
		);
	}

	/**
	 * Register external rewrite rules.
	 *
	 * This added to .htaccess on Apache servers to account for cases where
	 * WordPress doesn't handle the .json file extension.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Rewrite $wp_rewrite WP rewrite API.
	 */
	public function register_external_rewrite_rules( $wp_rewrite ) {
		$wp_rewrite->add_external_rule(
			'satispress/packages.json$',
			'index.php?satispress=packages.json'
		);
	}

	/**
	 * Activation routine.
	 *
	 * @since 0.3.0
	 */
	public function activate() {
		update_option( 'satispress_flush_rewrite_rules', 'yes' );
	}

	/**
	 * Deactivation routine.
	 *
	 * Deleting the rewrite rules option should force WordPress to regenerate
	 * them next time they're needed.
	 *
	 * @since 0.3.0
	 */
	public function deactivate() {
		delete_option( 'rewrite_rules' );
		delete_option( 'satispress_flush_rewrite_rules' );
	}

	/**
	 * Flush the rewrite rules if needed.
	 *
	 * @since 0.3.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( is_network_admin() || 'no' === get_option( 'satispress_flush_rewrite_rules' ) ) {
			return;
		}

		update_option( 'satispress_flush_rewrite_rules', 'no' );
		flush_rewrite_rules();
	}
}
