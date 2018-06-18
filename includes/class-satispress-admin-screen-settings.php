<?php
/**
 * SatisPress_Admin_Screen_Settings class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

/**
 * Settings screen.
 *
 * @since 0.2.0
 */
class SatisPress_Admin_Screen_Settings {
	/**
	 * Base patch for packages.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	public $base_path = '';

	/**
	 * Load the screen.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'add_sections' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
		add_action( 'admin_notices', [ $this, 'htaccess_notice' ] );
	}

	/**
	 * Set the base path for packages.
	 *
	 * @since 0.2.0
	 *
	 * @param string $path Base path for packages.
	 */
	public function set_base_path( $path ) {
		$this->base_path = $path;
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
			__( 'Vendor', 'satispress' ),
			[ $this, 'render_field_vendor' ],
			'satispress',
			'default'
		);

		add_settings_field(
			'enable_basic_authentication',
			__( 'Authentication', 'satispress' ),
			[ $this, 'render_field_basic_authentication' ],
			'satispress',
			'security'
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
	 */
	public function sanitize_settings( array $value ) {
		if ( ! empty( $value['vendor'] ) ) {
			$value['vendor'] = sanitize_text_field( $value['vendor'] );
		}

		if ( ! isset( $value['enable_basic_authentication'] ) ) {
			$value['enable_basic_authentication'] = 'no';
		} else {
			$value['enable_basic_authentication'] = 'yes';
		}

		return $value;
	}

	/**
	 * Sanitize list of themes.
	 *
	 * @since 0.2.0
	 *
	 * @param mixed $value Setting value.
	 * @return array
	 */
	public function sanitize_theme_settings( $value ) {
		return array_filter( array_unique( (array) $value ) );
	}

	/**
	 * Display the screen.
	 *
	 * @since 0.2.0
	 */
	public function render_screen() {
		$permalink = satispress_get_packages_permalink();
		$packages  = SatisPress::instance()->get_packages();
		include SATISPRESS_DIR . 'views/screen-settings.php';
	}

	/**
	 * Display the security section description.
	 *
	 * @since 0.2.0
	 */
	public function render_section_security_description() {
		esc_html_e( 'Your packages are public by default. At a minimum, you can secure them using HTTP Basic Authentication. Valid credentials are a WP username and password.', 'satispress' );
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
			<input type="text" name="satispress[vendor]" id="satispress-vendor" value="<?php echo esc_attr( $value ); ?>"><br>
			<span class="description">Default is <code>satispress</code></span>
		</p>
		<?php
	}

	/**
	 * Display the basic authentication settings field.
	 *
	 * @since 0.2.0
	 */
	public function render_field_basic_authentication() {
		$value = $this->get_setting( 'enable_basic_authentication', 'no' );
		?>
		<p class="satispress-togglable-field">
			<label>
				<input type="checkbox" name="satispress[enable_basic_authentication]" id="satispress-enable-basic-authentication" value="yes" <?php checked( $value, 'yes' ); ?>>
				<?php esc_html_e( 'Enable HTTP Basic Authentication?', 'satispress' ); ?>
			</label>
		</p>
		<?php
		$htaccess = new SatisPress_Htaccess( $this->base_path );
		if ( ! $htaccess->is_writable() ) {
			printf(
				'<p class="satispress-field-error">%s</p>',
				esc_html__( '.htaccess file isn\'t writable.', 'satispress' )
			);
		}
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
				'<label><input type="checkbox" name="satispress_themes[]" value="%1$s" %2$s> %3$s</label><br>',
				esc_attr( $slug ),
				checked( in_array( $slug, $value, true ), true, false ),
				esc_html( $theme->get( 'Name' ) )
			);
		}
	}

	/**
	 * Display a notice if Basic Authentication is enabled and .htaccess doesn't exist.
	 *
	 * @since 0.2.0
	 */
	public function htaccess_notice() {
		$value         = $this->get_setting( 'enable_basic_authentication', 'no' );
		$htaccess_file = $this->base_path . '.htaccess';

		if ( 'yes' === $value && ! file_exists( $htaccess_file ) ) {
			?>
			<div class="error">
				<p>
					<?php esc_html_e( "Warning: .htaccess doesn't exist. Your SatisPress packages are public.", 'satispress' ); ?>
				</p>
			</div>
			<?php
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
	protected function get_setting( $key, $default = false ) {
		$option = get_option( 'satispress' );
		return isset( $option[ $key ] ) ? $option[ $key ] : false;
	}

	/**
	 * Retrieve the contents of a view.
	 *
	 * @since 0.2.0
	 *
	 * @param string $file View filename.
	 * @return string
	 */
	protected function get_view( $file ) {
		ob_start();
		include SATISPRESS_DIR . 'views/' . $file;

		return ob_get_clean();
	}
}
