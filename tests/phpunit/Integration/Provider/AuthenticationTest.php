<?php
declare ( strict_types = 1 );

namespace SatisPress\Test\Integration\Provider\Authentication;

use Pimple\ServiceIterator;
use SatisPress\Capabilities as Caps;
use SatisPress\Exception\AuthenticationException;
use SatisPress\HTTP\Request;
use SatisPress\Provider\Authentication;
use SatisPress\Test\Integration\TestCase;
use WP_Error;
use WPDieException;

use function Patchwork\{always, redefine, restore};
use function SatisPress\get_packages_permalink;
use function SatisPress\plugin;

class AuthenticationTest extends TestCase {
	protected static $api_key;
	protected static $user_id;
	protected static $redefine_handle;

	public static function wpSetUpBeforeClass( $factory ) {
		$container = plugin()->get_container();
		$user      = $factory->user->create_and_get();
		$api_key   = $container->get( 'api_key.factory' )->create( $user );

		self::$user_id         = $user->ID;
		self::$api_key         = $api_key->get_token();
		self::$redefine_handle = redefine( 'header', always( null ) );

		$container->get( 'api_key.repository' )->save( $api_key );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$user_id );
		restore( self::$redefine_handle );
	}

	public function setUp(): void {
		parent::setUp();

		$GLOBALS['current_user'] = null;

		$this->provider = plugin()->get_container()->get( 'hooks.authentication' );
		add_filter( 'determine_current_user', [ $this->provider, 'determine_current_user' ] );
	}

	public function tearDown(): void {
		$this->set_request_headers();
		$this->reset_auth_status();
		remove_filter( 'determine_current_user', [ $this->provider, 'determine_current_user' ] );
	}

	public function test_authentication_succeeds_with_valid_credentials() {
		$this->set_request_headers( [
			'Authorization' => 'Basic ' . base64_encode( self::$api_key . ':satispress' ),
			'PHP_AUTH_USER' => self::$api_key,
			'PHP_AUTH_PW'   => 'satispress',
		] );

		$user = wp_get_current_user();
		$this->assertSame( self::$user_id, $user->ID );
	}

	public function test_authentication_returns_already_authenticated_user() {
		wp_set_current_user( self::$user_id );
		$user = wp_get_current_user();
		$this->assertSame( self::$user_id, $user->ID );
	}

	public function test_authentication_fails_with_invalid_scheme() {
		$this->set_request_headers( [
			'Authorization' => 'Bearer ' . base64_encode( self::$api_key . ':satispress' ),
			'PHP_AUTH_USER' => self::$api_key,
			'PHP_AUTH_PW'   => 'satispress',
		] );

		$user = wp_get_current_user();
		$this->assertSame( 0, $user->ID );

		$this->expectException( WPDieException::class );
		$this->go_to( get_packages_permalink() );
	}

	public function test_authentication_fails_with_invalid_realm() {
		$this->set_request_headers( [
			'Authorization' => 'Basic ' . base64_encode( self::$api_key . ':' ),
			'PHP_AUTH_USER' => self::$api_key,
			'PHP_AUTH_PW'   => '', // The Basic Auth password field is used for the realm.
		] );

		$user = wp_get_current_user();
		$this->assertSame( 0, $user->ID );

		$this->expectException( WPDieException::class );
		$this->go_to( get_packages_permalink() );
	}

	public function test_authentication_fails_with_missing_key() {
		$this->set_request_headers( [
			'Authorization' => 'Basic ' . base64_encode( ':satispress' ),
			'PHP_AUTH_PW'   => 'satispress',
		] );

		$user = wp_get_current_user();
		$this->assertSame( 0, $user->ID );

		$this->expectException( WPDieException::class );
		$this->go_to( get_packages_permalink() );
	}

	public function test_authentication_fails_with_invalid_key() {
		$this->set_request_headers( [
			'Authorization' => 'Basic ' . base64_encode( 'abcdef:satispress' ),
			'PHP_AUTH_USER' => 'abcdef',
			'PHP_AUTH_PW'   => 'satispress',
		] );

		$user = wp_get_current_user();
		$this->assertSame( 0, $user->ID );

		$this->expectException( WPDieException::class );
		$this->go_to( get_packages_permalink() );
	}

	public function test_get_errors_returns_non_null_value() {
		$error = new WP_Error();
		$result = $this->provider->get_authentication_errors( $error );
		$this->assertSame( $error, $result );
	}

	public function test_get_errors_returns_early_if_user_logged_in() {
		wp_set_current_user( self::$user_id );
		$result = $this->provider->get_authentication_errors( null );
		$this->assertNull( $result );
	}

	public function test_public_access_when_authentication_is_disabled() {
		$container = plugin()->get_container();
		$servers   = new ServiceIterator( $container, [] );
		$provider  = new Authentication( $servers, new Request() );
		add_filter( 'user_has_cap', [ $provider, 'maybe_allow_public_access' ] );

		$this->assertTrue( current_user_can( Caps::DOWNLOAD_PACKAGES ) );
		$this->assertTrue( current_user_can( Caps::VIEW_PACKAGES ) );
	}

	protected function set_request_headers( $headers = [] ) {
		$request = new Request();
		$request->set_headers( $headers, true );

		$class = new \ReflectionClass( $this->provider );
		$property = $class->getProperty( 'request' );
		$property->setAccessible( true );
		$property->setValue( $this->provider, $request );
	}

	protected function reset_auth_status( $auth_status = null ) {
		$class = new \ReflectionClass( $this->provider );

		$property = $class->getProperty( 'auth_status' );
		$property->setAccessible( true );
		$property->setValue( $this->provider, $auth_status );

		$property = $class->getProperty( 'should_attempt' );
		$property->setAccessible( true );
		$property->setValue( $this->provider, true );
	}
}
