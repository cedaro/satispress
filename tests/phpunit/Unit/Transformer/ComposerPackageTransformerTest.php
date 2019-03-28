<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\Transformer;

use Brain\Monkey\Functions;
use SatisPress\PackageFactory;
use SatisPress\ReleaseManager;
use SatisPress\Transformer\ComposerPackageTransformer;
use SatisPress\Test\Unit\TestCase;

class ComposerPackageTransformerTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$manager = $this->getMockBuilder( ReleaseManager::class )
			->disableOriginalConstructor()
			->getMock();

		$factory = new PackageFactory( $manager );

		$this->package = $factory->create( 'plugin' )
			->set_slug( 'AcmeCode' )
			->build();

		$this->transformer = new ComposerPackageTransformer( $factory );
	}

	public function test_package_name_is_lowercased() {
		$package = $this->transformer->transform( $this->package );
		$this->assertSame( 'satispress/acmecode', $package->get_name() );
	}
}
