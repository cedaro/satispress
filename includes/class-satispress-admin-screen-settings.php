<?php

class SatisPress_Admin_Screen_Settings {
	public function load() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
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
			array( $this, 'render_screen' )
		);

		#add_action( 'load-' . $screen_hook, array( $this, 'setup_screen' ) );

		self::register_settings();
		self::add_sections();
		self::add_settings();
	}

	/**
	 * Register the settings option.
	 *
	 * @since 0.2.0
	 */
	public function register_settings() {
		register_setting( 'satispress', 'satispress', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Add settings sections.
	 *
	 * @since 0.2.0
	 */
	public function add_sections() {
		add_settings_section(
			'default',
			'', //__( '', 'satispress' ),
			'__return_null',
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
			'enable_basic_authentication',
			__( 'Authentication', 'satispress' ),
			array( $this, 'render_field_basic_authentication' ),
			'satispress',
			'default'
		);
	}

	/**
	 * Sanitize options.
	 *
	 * @since 0.2.0
	 */
	public function sanitize_settings( $value ) {
		if ( ! isset( $value['enable_basic_authentication' ] ) ) {
			$value['enable_basic_authentication'] = 'no';
		}

		return $value;
	}

	/**
	 * Display the screen.
	 *
	 * @since 0.2.0
	 */
	public function render_screen() {
		?>
		<div class="wrap">
			<h2><?php _e( 'SatisPress', 'satispress' ); ?></h2>

			<form action="options.php" method="post">
				<?php settings_fields( 'satispress' ); ?>
				<?php do_settings_sections( 'satispress' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display the mode settings field.
	 *
	 * @since 0.2.0
	 */
	public function render_field_basic_authentication() {
		$options = get_option( 'satispress' );
		$value = ( isset( $options['enable_basic_authentication'] ) && 'yes' === $options['enable_basic_authentication'] ) ? 'yes' : 'no';
		?>
		<p class="satispress-togglable-field">
			<label>
				<input type="checkbox" name="satispress[enable_basic_authentication]" id="satispress-enable-basic-authentication" value="yes" <?php checked( $value, 'yes' ); ?>>
				<?php _e( 'Enable basic authentication?', 'satispress' ); ?>
			</label>
		</p>
		<?php
	}
}
