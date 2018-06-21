<?php
/**
 * Authentication interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress\Authentication\Basic;

use SatisPress\Htaccess;

/**
 * SatisPress authentication interface.
 *
 * @since 0.2.0
 */
class Settings extends \SatisPress\Authentication\Settings {
	/**
	 * Handler for .htaccess files.
	 *
	 * @since 0.3.0
	 *
	 * @var Htaccess
	 */
	protected $htaccess_handler;

	/**
	 * Constructor
	 *
	 * @since 0.3.0
	 *
	 * @param Htaccess $htaccess_handler Handler for .htaccess files.
	 */
	public function __construct( Htaccess $htaccess_handler ) {
		$this->htaccess_handler = $htaccess_handler;
	}

	/**
	 * Load the screen.
	 *
	 * @since 0.2.0
	 */
	public function load() {
		add_filter( 'update_option_satispress', [ $this, 'maybe_setup' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
		add_action( 'admin_notices', [ $this, 'htaccess_notice' ] );
		add_action( 'satispress_sanitize_settings', [ $this, 'sanitize_settings' ] );
	}

	/**
	 * Update .htaccess rules when the setting is changed.
	 *
	 * Creates an .htaccess file in the cache directory with a 'Deny from all' rule to prevent direct access.
	 *
	 * @since 0.2.0
	 *
	 * @param array $old_value Current settings values.
	 * @param array $value Saved settings.
	 */
	public function maybe_setup( $old_value, $value ) {
		if ( ! isset( $value['enable_basic_authentication'] ) ) {
			return;
		}

		$rules = [];
		if ( 'yes' === $value['enable_basic_authentication'] ) {
			$rules[] = 'Deny from all';
		}

		$this->htaccess_handler->add_rules( $rules );
		$this->htaccess_handler->save();
	}

	/**
	 * Authenticate requests for SatisPress packages.
	 *
	 * @since 0.3.0
	 */
	public function add_settings() {
		add_settings_field(
			'enable_basic_authentication',
			__( 'Authentication', 'satispress' ),
			[ $this, 'render_field_basic_authentication' ],
			'satispress',
			'security'
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
		if ( ! isset( $value['enable_basic_authentication'] ) ) {
			$value['enable_basic_authentication'] = 'no';
		} else {
			$value['enable_basic_authentication'] = 'yes';
		}

		return $value;
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
		if ( ! $this->htaccess_handler->is_writable() ) {
			printf(
				'<p class="satispress-field-error">%s</p>',
				esc_html__( '.htaccess file isn\'t writable.', 'satispress' )
			);
		}
	}

	/**
	 * Display a notice if Basic Authentication is enabled and .htaccess doesn't exist.
	 *
	 * @since 0.2.0
	 */
	public function htaccess_notice() {
		$value = $this->get_setting( 'enable_basic_authentication', 'no' );

		if ( 'yes' === $value && ! $this->htaccess_handler->file_exists() ) {
			?>
			<div class="error">
				<p>
					<?php
					echo sprintf(
						/* translators: %s: <code>.htaccess</code> */
						esc_html__( 'Warning: %s doesn\'t exist. Your SatisPress packages are public.', 'satispress' ),
						'<code>.htaccess</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
