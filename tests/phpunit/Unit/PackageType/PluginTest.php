<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\PackageType;

use Brain\Monkey\Functions;
use Psr\Log\NullLogger;
use SatisPress\Archiver;
use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\PluginBuilder;
use SatisPress\Release;
use SatisPress\ReleaseManager;
use SatisPress\Storage\Local as LocalStorage;
use SatisPress\Test\Unit\TestCase;

class PluginTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		Functions\when( 'get_plugin_data' )->justReturn( $this->get_plugin_data() );
		Functions\when( 'get_site_transient' )->justReturn( new \stdClass() );

		$archiver = new Archiver( new NullLogger() );
		$storage  = new LocalStorage( SATISPRESS_TESTS_DIR . '/Fixture/wp-content/uploads/satispress/packages' );
		$manager  = new ReleaseManager( $storage, $archiver );
		$package  = new Plugin();

		$this->builder = new PluginBuilder( $package, $manager );
	}

	public function test_get_plugin_from_source() {
		$package = $this->builder
			->from_source( 'basic/basic.php' )
			->build();

		$this->assertInstanceOf( Plugin::class, $package );

		$this->assertSame( 'Basic, Inc.', $package->get_author() );
		$this->assertSame( 'https://example.com/', $package->get_author_url() );
		$this->assertSame( 'basic/basic.php', $package->get_basename() );
		$this->assertSame( WP_PLUGIN_DIR . '/basic/', $package->get_directory() );
		$this->assertSame( 'https://example.com/plugin/basic/', $package->get_homepage() );
		$this->assertSame( 'Basic Plugin', $package->get_name() );
		$this->assertSame( '1.3.1', $package->get_installed_version() );
		$this->assertSame( 'basic', $package->get_slug() );
		$this->assertSame( 'plugin', $package->get_type() );
		$this->assertTrue( $package->is_installed() );
	}

	public function test_is_single_file_plugin() {
		$package = $this->builder->from_source( 'basic/basic.php' )->build();
		$this->assertFalse( $package->is_single_file() );

		$package = $this->builder->from_source( 'hello.php' )->build();
		$this->assertTrue( $package->is_single_file() );
	}

	public function test_get_files_for_single_file_plugin() {
		$package = $this->builder->from_source( 'hello.php' )->build();
		$this->assertSame( 1, count( $package->get_files() ) );
	}

	protected function get_plugin_data() {
		return [
			'AuthorName'  => 'Basic, Inc.',
			'AuthorURI'   => 'https://example.com/',
			'PluginURI'   => 'https://example.com/plugin/basic/',
			'Name'        => 'Basic Plugin',
			'Description' => '',
			'Version'     => '1.3.1',
		];
	}
}
