<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\PackageType;

use Psr\Log\NullLogger;
use SatisPress\Archiver;
use SatisPress\Exception\PackageNotInstalled;
use SatisPress\Package;
use SatisPress\PackageType\BasePackage;
use SatisPress\PackageType\PackageBuilder;
use SatisPress\ReleaseManager;
use SatisPress\Storage\Local as LocalStorage;
use SatisPress\Test\Unit\TestCase;

class PackageTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$archiver = new Archiver( new NullLogger() );
		$storage  = new LocalStorage( SATISPRESS_TESTS_DIR . '/Fixture/wp-content/uploads/satispress/packages' );
		$manager  = new ReleaseManager( $storage, $archiver );
		$package  = new BasePackage();

		$this->builder = new PackageBuilder( $package, $manager );
	}

	public function test_implements_package_interface() {
		$package = $this->builder->build();

		$this->assertInstanceOf( Package::class, $package );
	}

	public function test_author() {
		$expected = 'Cedaro';
		$package  = $this->builder->set_author( $expected )->build();

		$this->assertSame( $expected, $package->get_author() );
	}

	public function test_author_url() {
		$expected = 'https://www.cedaro.com/';
		$package  = $this->builder->set_author_url( $expected )->build();

		$this->assertSame( $expected, $package->get_author_url() );
	}

	public function test_description() {
		$expected = 'A package description.';
		$package  = $this->builder->set_description( $expected )->build();

		$this->assertSame( $expected, $package->get_description() );
	}

	public function test_directory() {
		$expected = __DIR__;
		$package  = $this->builder->set_directory( $expected )->build();

		$this->assertSame( $expected . '/', $package->get_directory() );
	}

	public function test_homepage() {
		$expected = 'https://www.cedaro.com/';
		$package  = $this->builder->set_homepage( $expected )->build();

		$this->assertSame( $expected, $package->get_homepage() );
	}

	public function test_is_installed() {
		$package = $this->builder->build();
		$this->assertFalse( $package->is_installed() );

		$package = $this->builder->set_installed( true )->build();
		$this->assertTrue( $package->is_installed() );
	}

	public function test_installed_version() {
		$expected = '1.0.0';
		$package  = $this->builder->set_installed( true )->set_installed_version( $expected )->build();

		$this->assertSame( $expected, $package->get_installed_version() );
	}

	public function test_get_installed_version_throws_exception_when_plugin_not_installed() {
		$this->expectException( PackageNotInstalled::class );

		$expected = '1.0.0';
		$package  = $this->builder->set_installed_version( $expected )->build();
		$package->get_installed_version();
	}

	public function test_name() {
		$expected = 'SatisPress';
		$package  = $this->builder->set_name( $expected )->build();

		$this->assertSame( $expected, $package->get_name() );
	}

	public function test_slug() {
		$expected = 'satispress';
		$package  = $this->builder->set_slug( $expected )->build();

		$this->assertSame( $expected, $package->get_slug() );
	}

	public function test_type() {
		$expected = 'plugin';
		$package  = $this->builder->set_type( $expected )->build();

		$this->assertSame( $expected, $package->get_type() );
	}
}
