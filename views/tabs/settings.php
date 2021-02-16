<?php
/**
 * Views: Settings tab content.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

?>

<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
	<?php settings_fields( 'satispress' ); ?>
	<?php do_settings_sections( 'satispress' ); ?>
	<?php submit_button(); ?>
</form>
