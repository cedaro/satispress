<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Integration\Validator;

use SatisPress\Exception\InvalidPackageArtifact;
use SatisPress\Release;
use SatisPress\Test\Unit\TestCase;
use SatisPress\Validator\HiddenDirectoryValidator;

class HiddenDirectoryValidatorTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->directory = SATISPRESS_TESTS_DIR . '/Fixture/wp-content/uploads/satispress/packages/validate';

		$this->release = $this->getMockBuilder( Release::class )
			->disableOriginalConstructor()
			->getMock();

		$this->validator = new HiddenDirectoryValidator();
	}

	public function test_artifact_is_valid_zip() {
		$filename = $this->directory . '/valid-zip.zip';
		$result = $this->validator->validate( $filename, $this->release );
		$this->assertTrue( $result );
	}

	public function test_validator_throws_exception_for_invalid_artifact() {
		$this->expectException( InvalidPackageArtifact::class );
		$filename = $this->directory . '/invalid-osx-zip.zip';
		$this->validator->validate( $filename, $this->release );
	}
}
