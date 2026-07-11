<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Unit\Storage;

use Brain\Monkey\Functions;
use SatisPress\Exception\FileNotFound;
use SatisPress\Storage\CachedStorage;
use SatisPress\Storage\Storage;
use SatisPress\Test\Unit\TestCase;

class CachedStorageTest extends TestCase {
	const CACHE_TTL = 3600;

	public function test_checksum_is_computed_and_cached_on_cache_miss() {
		$inner = $this->createMock( Storage::class );
		$inner
			->method( 'exists' )
			->willReturn( true );
		$inner
			->expects( $this->once() )
			->method( 'checksum' )
			->with( 'sha1', 'basic/basic-1.0.zip' )
			->willReturn( 'abc123' );

		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'set_transient' )
			->once()
			->with( \Mockery::type( 'string' ), [ 'sha1' => 'abc123' ], self::CACHE_TTL );

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->assertSame( 'abc123', $storage->checksum( 'sha1', 'basic/basic-1.0.zip' ) );
	}

	public function test_cached_checksum_skips_inner_storage() {
		$inner = $this->createMock( Storage::class );
		$inner
			->method( 'exists' )
			->willReturn( true );
		$inner
			->expects( $this->never() )
			->method( 'checksum' );

		Functions\when( 'get_transient' )->justReturn( [ 'sha1' => 'abc123' ] );

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->assertSame( 'abc123', $storage->checksum( 'sha1', 'basic/basic-1.0.zip' ) );
	}

	public function test_checksums_for_multiple_algorithms_are_cached_together() {
		$inner = $this->createMock( Storage::class );
		$inner
			->method( 'exists' )
			->willReturn( true );
		$inner
			->expects( $this->once() )
			->method( 'checksum' )
			->with( 'md5', 'basic/basic-1.0.zip' )
			->willReturn( 'def456' );

		Functions\when( 'get_transient' )->justReturn( [ 'sha1' => 'abc123' ] );
		Functions\expect( 'set_transient' )
			->once()
			->with(
				\Mockery::type( 'string' ),
				[
					'sha1' => 'abc123',
					'md5'  => 'def456',
				],
				self::CACHE_TTL
			);

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->assertSame( 'def456', $storage->checksum( 'md5', 'basic/basic-1.0.zip' ) );
	}

	public function test_checksum_for_missing_file_invalidates_cache() {
		$inner = $this->createMock( Storage::class );
		$inner
			->method( 'exists' )
			->willReturn( false );
		$inner
			->expects( $this->once() )
			->method( 'checksum' )
			->willThrowException( FileNotFound::forInvalidChecksum( 'basic/basic-1.0.zip' ) );

		Functions\expect( 'delete_transient' )
			->once()
			->with( CachedStorage::TRANSIENT_PREFIX . md5( 'basic/basic-1.0.zip' ) );

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->expectException( FileNotFound::class );
		$storage->checksum( 'sha1', 'basic/basic-1.0.zip' );
	}

	public function test_move_invalidates_cached_checksums_for_destination() {
		$inner = $this->createMock( Storage::class );
		$inner
			->expects( $this->once() )
			->method( 'move' )
			->with( '/tmp/source.zip', 'basic/basic-1.0.zip' )
			->willReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( CachedStorage::TRANSIENT_PREFIX . md5( 'basic/basic-1.0.zip' ) );

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->assertTrue( $storage->move( '/tmp/source.zip', 'basic/basic-1.0.zip' ) );
	}

	public function test_delete_invalidates_cached_checksums() {
		$inner = $this->createMock( Storage::class );
		$inner
			->expects( $this->once() )
			->method( 'delete' )
			->with( 'basic/basic-1.0.zip' )
			->willReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( CachedStorage::TRANSIENT_PREFIX . md5( 'basic/basic-1.0.zip' ) );

		$storage = new CachedStorage( $inner, self::CACHE_TTL );

		$this->assertTrue( $storage->delete( 'basic/basic-1.0.zip' ) );
	}
}
