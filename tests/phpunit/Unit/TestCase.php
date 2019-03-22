<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase {
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
