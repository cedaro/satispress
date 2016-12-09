<?php
/**
 * Settings page view.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.2.0
 */

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="#satispress-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'Settings', 'satispress' ); ?></a>
		<a href="#satispress-packages" class="nav-tab"><?php esc_html_e( 'Packages', 'satispress' ); ?></a>
	</h2>

	<div id="satispress-settings" class="satispress-tab-panel is-active">
		<p>
			<?php esc_html_e( 'Your SatisPress repository is available at:', 'satispress' ); ?>
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $permalink ); ?></a>
		</p>
		<p>
			<?php
			$allowed_html = array( 'code' => array() );
			printf(
				/* translators: 1: <code>repositories</code>, 2: <code>composer.json</code> */
				esc_html__( 'Add it to the %1$s list in your %2$s:', 'satispress' ),
				'<code>repositories</code>',
				'<code>composer.json</code>'
			);
			?>
		</p>

		<pre class="satispress-repository-snippet"><code>{
	"repositories": [
		{
			"type": "composer",
			"url": "<?php echo esc_url( satispress_get_packages_permalink( array( 'base' => true ) ) ); ?>"
		}
	]
}</code></pre>

		<form action="options.php" method="post">
			<?php settings_fields( 'satispress' ); ?>
			<?php do_settings_sections( 'satispress' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>

	<div id="satispress-packages" class="satispress-tab-panel">
		<?php include( SATISPRESS_DIR . 'views/packages.php' ); ?>
	</div>
</div>
