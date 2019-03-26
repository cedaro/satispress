<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\PackageType;

use SatisPress\Exception\PackageNotInstalled;
use SatisPress\Package;
use SatisPress\PackageType\BasePackage;
use SatisPress\PackageType\PackageBuilder;
use SatisPress\ReleaseManager;
use SatisPress\Test\Unit\TestCase;

class PackageTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->package = new class extends BasePackage {
			public function __set( $name, $value ) {
				$this->$name = $value;
			}
		};
	}

	public function test_implements_package_interface() {
		$this->assertInstanceOf( Package::class, $this->package );
	}

	public function test_author() {
		$expected = 'Cedaro';
		$this->package->author = $expected;

		$this->assertSame( $expected, $this->package->get_author() );
	}

	public function test_author_url() {
		$expected = 'https://www.cedaro.com/';
		$this->package->author_url = $expected;

		$this->assertSame( $expected, $this->package->get_author_url() );
	}

	public function test_description() {
		$expected = 'A package description.';
		$this->package->description = $expected;

		$this->assertSame( $expected, $this->package->get_description() );
	}

	public function test_directory() {
		$expected = __DIR__ . '/';
		$this->package->directory = $expected;

		$this->assertSame( $expected, $this->package->get_directory() );
	}

	public function test_homepage() {
		$expected = 'https://www.cedaro.com/';
		$this->package->homepage = $expected;

		$this->assertSame( $expected, $this->package->get_homepage() );
	}

	public function test_is_installed() {
		$this->assertFalse( $this->package->is_installed() );

		$this->package->is_installed = true;
		$this->assertTrue( $this->package->is_installed() );
	}

	public function test_installed_version() {
		$expected = '1.0.0';
		$this->package->is_installed = true;
		$this->package->installed_version = $expected;

		$this->assertSame( $expected, $this->package->get_installed_version() );
	}

	public function test_get_installed_version_throws_exception_when_plugin_not_installed() {
		$this->expectException( PackageNotInstalled::class );

		$this->package->installed_version = '1.0.0';
		$this->package->get_installed_version();
	}

	public function test_name() {
		$expected = 'SatisPress';
		$this->package->name = $expected;

		$this->assertSame( $expected, $this->package->get_name() );
	}

	public function test_slug() {
		$expected = 'satispress';
		$this->package->slug = $expected;

		$this->assertSame( $expected, $this->package->get_slug() );
	}

	public function test_type() {
		$expected = 'plugin';
		$this->package->type = $expected;

		$this->assertSame( $expected, $this->package->get_type() );
	}
}
