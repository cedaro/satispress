<?php
/**
 * Settings screen provider.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Screen;

use Cedaro\WP\Plugin\AbstractHookProvider;
use function SatisPress\get_packages_permalink;
use SatisPress\PackageManager;

/**
 * Settings screen provider class.
 *
 * @since 0.2.0
 */
class Settings extends AbstractHookProvider {
	/**
	 * Package manager.
	 *
	 * @var PackageManager
	 */
	protected $package_manager;

	/**
	 * Initialise SatisPress object.
	 *
	 * @param PackageManager $package_manager Package manager.
	 */
	public function __construct( PackageManager $package_manager ) {
		$this->package_manager = $package_manager;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'add_sections' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Add the settings menu item.
	 *
	 * @since 0.2.0
	 */
	public function add_menu_item() {
		$screen_hook = add_options_page(
			__( 'SatisPress', 'satispress' ),
			__( 'SatisPress', 'satispress' ),
			'manage_options',
			'satispress',
			[ $this, 'render_screen' ]
		);

		add_action( 'load-' . $screen_hook, [ $this, 'setup_screen' ] );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 0.2.0
	 * @todo Add help tabs.
	 */
	public function setup_screen() {
		$screen = get_current_screen();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.2.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'satispress-admin' );
		wp_enqueue_style( 'satispress-admin' );
	}

	/**
	 * Register settings.
	 *
	 * @since 0.2.0
	 */
	public function register_settings() {
		register_setting( 'satispress', 'satispress', [ $this, 'sanitize_settings' ] );
		register_setting( 'satispress', 'satispress_themes', [ $this, 'sanitize_theme_settings' ] );
	}

	/**
	 * Add settings sections.
	 *
	 * @since 0.2.0
	 */
	public function add_sections() {
		add_settings_section(
			'default',
			__( 'General', 'satispress' ),
			'__return_null',
			'satispress'
		);

		add_settings_section(
			'security',
			__( 'Security', 'satispress' ),
			[ $this, 'render_section_security_description' ],
			'satispress'
		);

		add_settings_section(
			'themes',
			__( 'Themes', 'satispress' ),
			[ $this, 'render_section_themes_description' ],
			'satispress'
		);
	}

	/**
	 * Register individual settings.
	 *
	 * @since 0.2.0
	 */
	public function add_settings() {
		add_settings_field(
			'vendor',
			'<label for="satispress-vendor">' . __( 'Vendor', 'satispress' ) . '</label>',
			[ $this, 'render_field_vendor' ],
			'satispress',
			'default'
		);

		add_settings_field(
			'themes',
			__( 'Themes', 'satispress' ),
			[ $this, 'render_field_themes' ],
			'satispress',
			'themes'
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @since 0.2.0
	 *
	 * @param array $value Settings values.
	 * @return array Sanitized and filtered settings values.
	 */
	public function sanitize_settings( array $value ): array {
		if ( ! empty( $value['vendor'] ) ) {
			$value['vendor'] = sanitize_text_field( $value['vendor'] );
		}

		return (array) apply_filters( 'satispress_sanitize_settings', $value );
	}

	/**
	 * Sanitize list of themes.
	 *
	 * @since 0.2.0
	 *
	 * @param mixed $value Setting value.
	 * @return array
	 */
	public function sanitize_theme_settings( $value ): array {
		return array_filter( array_unique( (array) $value ) );
	}

	/**
	 * Display the screen.
	 *
	 * @since 0.2.0
	 */
	public function render_screen() {
		$permalink = get_packages_permalink();
		$packages  = $this->package_manager->get_packages();
		include $this->plugin->get_path( 'views/screen-settings.php' );
	}

	/**
	 * Display the security section description.
	 *
	 * @since 0.2.0
	 */
	public function render_section_security_description() {
		esc_html_e( 'Packges are secured using HTTP Basic Authentication by default. Valid credentials are a WP username and password.', 'satispress' );
	}

	/**
	 * Display the themes section description.
	 *
	 * @since 0.2.0
	 */
	public function render_section_themes_description() {
		esc_html_e( 'Choose themes to make available in your SatisPress repository.', 'satispress' );
	}

	/**
	 * Display a field for defining the vendor.
	 *
	 * @since 0.2.0
	 */
	public function render_field_vendor() {
		$value = $this->get_setting( 'vendor', '' );
		?>
		<p>
			<input type="text" name="satispress[vendor]" id="satispress-vendor" value="<?php echo esc_attr( $value ); ?>"><br />
			<span class="description">Default is <code>satispress</code></span>
		</p>
		<?php
	}

	/**
	 * Display the themes list field.
	 *
	 * @since 0.2.0
	 */
	public function render_field_themes() {
		$value = get_option( 'satispress_themes', [] );

		$themes = wp_get_themes();
		foreach ( $themes as $slug => $theme ) {
			printf(
				'<label><input type="checkbox" name="satispress_themes[]" value="%1$s"%2$s> %3$s</label><br />',
				esc_attr( $slug ),
				checked( in_array( $slug, $value, true ), true, false ),
				esc_html( $theme->get( 'Name' ) )
			);
		}
	}

	/**
	 * Retrieve a setting.
	 *
	 * @since 0.2.0
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $default Optional. Default setting value.
	 * @return mixed
	 */
	protected function get_setting( string $key, $default = null ) {
		$option = get_option( 'satispress' );

		return isset( $option[ $key ] ) ? $option[ $key ] : $default;
	}

	/**
	 * Retrieve the contents of a view.
	 *
	 * @since 0.2.0
	 *
	 * @param string $file View filename.
	 * @return string View output.
	 */
	protected function get_view( string $file ): string {
		ob_start();
		include $this->plugin->get_path( 'views/' . $file );

		return ob_get_clean();
	}
}
