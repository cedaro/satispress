<?php
/**
 * Composer package builder.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use SatisPress\Package;

/**
 * Composer package builder class.
 *
 * @since 0.3.0
 */
final class ComposerBuilder extends PackageBuilder {
	/**
	 * Composer package type map.
	 *
	 * @var array
	 */
	const WORDPRESS_TYPES = [
		'dropin'   => 'wordpress-dropin',
		'muplugin' => 'wordpress-muplugin',
		'plugin'   => 'wordpress-plugin',
		'theme'    => 'wordpress-theme',
	];

	/**
	 * Set properties from an existing package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package.
	 * @return $this
	 */
	public function with_package( Package $package ): PackageBuilder {
		parent::with_package( $package );

		$vendor = apply_filters( 'satispress_vendor', 'satispress' );
		$this->set_name( $vendor . '/' . $package->get_slug() );

		if ( isset( self::WORDPRESS_TYPES[ $package->get_type() ] ) ) {
			$this->set_type( self::WORDPRESS_TYPES[ $package->get_type() ] );
		}

		return $this;
	}
}
