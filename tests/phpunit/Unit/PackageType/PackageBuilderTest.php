<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\PackageType;

use SatisPress\Package;
use SatisPress\PackageType\BasePackage;
use SatisPress\PackageType\PackageBuilder;
use SatisPress\ReleaseManager;
use SatisPress\Test\Unit\TestCase;

class PackageBuilderTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$manager = $this->getMockBuilder( ReleaseManager::class )
			->disableOriginalConstructor()
			->getMock();

		$package = new class extends BasePackage {
			public function __get( $name ) {
				return $this->$name;
			}
		};

		$this->builder = new PackageBuilder( $package, $manager );
	}

	public function test_implements_package_interface() {
		$package = $this->builder->build();

		$this->assertInstanceOf( Package::class, $package );
	}

	public function test_author() {
		$expected = 'Cedaro';
		$package  = $this->builder->set_author( $expected )->build();

		$this->assertSame( $expected, $package->author );
	}

	public function test_author_url() {
		$expected = 'https://www.cedaro.com/';
		$package  = $this->builder->set_author_url( $expected )->build();

		$this->assertSame( $expected, $package->author_url );
	}

	public function test_description() {
		$expected = 'A package description.';
		$package  = $this->builder->set_description( $expected )->build();

		$this->assertSame( $expected, $package->description );
	}

	public function test_directory() {
		$expected = 'directory';
		$package  = $this->builder->set_directory( $expected )->build();

		$this->assertSame( $expected . '/', $package->directory );
	}

	public function test_homepage() {
		$expected = 'https://www.cedaro.com/';
		$package  = $this->builder->set_homepage( $expected )->build();

		$this->assertSame( $expected, $package->homepage );
	}

	public function test_is_installed() {
		$package = $this->builder->build();
		$this->assertFalse( $package->is_installed );

		$package = $this->builder->set_installed( true )->build();
		$this->assertTrue( $package->is_installed );
	}

	public function test_installed_version() {
		$expected = '1.0.0';
		$package  = $this->builder->set_installed( true )->set_installed_version( $expected )->build();

		$this->assertSame( $expected, $package->installed_version );
	}

	public function test_name() {
		$expected = 'SatisPress';
		$package  = $this->builder->set_name( $expected )->build();

		$this->assertSame( $expected, $package->name );
	}

	public function test_slug() {
		$expected = 'satispress';
		$package  = $this->builder->set_slug( $expected )->build();

		$this->assertSame( $expected, $package->slug );
	}

	public function test_type() {
		$expected = 'plugin';
		$package  = $this->builder->set_type( $expected )->build();

		$this->assertSame( $expected, $package->type );
	}
}
