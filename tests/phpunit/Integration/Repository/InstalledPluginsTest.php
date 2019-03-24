<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Integration\Repository;

use SatisPress\PackageFactory;
use SatisPress\PackageType\Plugin;
use SatisPress\ReleaseManager;
use SatisPress\Repository\InstalledPlugins;
use SatisPress\Test\Integration\TestCase;

use function SatisPress\plugin;

class InstalledPluginsTest extends TestCase {
	public function test_get_plugin_from_source() {
		$repository = plugin()->get_container()['repository.plugins'];
		$package    = $repository->first_where( [ 'slug' => 'basic/basic.php' ] );

		$this->assertInstanceOf( Plugin::class, $package );
	}
}
