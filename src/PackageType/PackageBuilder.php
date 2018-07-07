<?php
/**
 * Package builder.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use ReflectionClass;
use SatisPress\Package;
use SatisPress\Release;

/**
 * Package builder class.
 *
 * @since 0.3.0
 */
class PackageBuilder {
	/**
	 * Reflection class instance.
	 *
	 * @var ReflectionClass
	 */
	protected $class;

	/**
	 * Package instance.
	 *
	 * @var Package
	 */
	protected $package;

	/**
	 * Package releases.
	 *
	 * @var Release[]
	 */
	protected $releases = [];

	/**
	 * Create a package builder.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package instance to build.
	 */
	public function __construct( Package $package ) {
		$this->package = $package;
		$this->class   = new ReflectionClass( $package );
	}

	/**
	 * Finalize the package build.
	 *
	 * @since 0.3.0
	 *
	 * @return Package
	 */
	public function build(): Package {
		$this->set( 'releases', $this->releases );
		return $this->package;
	}

	/**
	 * Set the author.
	 *
	 * @since 0.3.0
	 *
	 * @param string $author Author.
	 * @return $this
	 */
	public function set_author( string $author ): self {
		$this->author = $author;
		return $this->set( 'author', $author );
	}

	/**
	 * Set the author URL.
	 *
	 * @since 0.3.0
	 *
	 * @param string $author_url Author URL.
	 * @return $this
	 */
	public function set_author_url( string $author_url ): self {
		return $this->set( 'author_url', $author_url );
	}

	/**
	 * Set the description.
	 *
	 * @since 0.3.0
	 *
	 * @param string $description Description.
	 * @return $this
	 */
	public function set_description( string $description ): self {
		return $this->set( 'description', $description );
	}

	/**
	 * Set the homepage URL.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url URL.
	 * @return $this
	 */
	public function set_homepage( string $url ): self {
		return $this->set( 'homepage', $url );
	}

	/**
	 * Set the name.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Package name.
	 * @return $this
	 */
	public function set_name( string $name ): self {
		return $this->set( 'name', $name );
	}

	/**
	 * Set the slug.
	 *
	 * @since 0.3.0
	 *
	 * @param string $slug Slug.
	 * @return $this
	 */
	public function set_slug( string $slug ): self {
		return $this->set( 'slug', $slug );
	}

	/**
	 * Set the type.
	 *
	 * @since 0.3.0
	 *
	 * @param string $type Package type.
	 * @return $this
	 */
	public function set_type( string $type ): self {
		return $this->set( 'type', $type );
	}

	/**
	 * Add a release.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version    Version.
	 * @param string $source_url Optional. Release source URL.
	 * @return $this
	 */
	public function add_release( string $version, string $source_url = '' ): self {
		$this->releases[ $version ] = new Release( $this->package, $version, $source_url );
		return $this;
	}

	/**
	 * Set properties from an existing package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package.
	 * @return $this
	 */
	public function with_package( Package $package ): self {
		$this
			->set_author( $package->get_author() )
			->set_author_url( $package->get_author_url() )
			->set_description( $package->get_description() )
			->set_name( $package->get_name() )
			->set_homepage( $package->get_homepage() )
			->set_slug( $package->get_slug() )
			->set_type( $package->get_type() );

		foreach ( $package->get_releases() as $release ) {
			$this->add_release( $release->get_version(), $release->get_source_url() );
		}

		return $this;
	}

	/**
	 * Set a property on the package instance.
	 *
	 * Uses the reflection API to assign values to protected properties of the
	 * package instance to make the returned instance immutable.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 */
	protected function set( $name, $value ) {
		$property = $this->class->getProperty( $name );
		$property->setAccessible( true );
		$property->setValue( $this->package, $value );
		return $this;
	}
}
