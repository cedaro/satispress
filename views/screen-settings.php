<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<strong class="nav-tab-wrapper-title"><?php _e( 'SatisPress', 'satispress' ); ?></strong>
		<a href="#satispress-settings" class="nav-tab nav-tab-active"><?php _e( 'Settings', 'satispress' ); ?></a>
		<a href="#satispress-packages" class="nav-tab"><?php _e( 'Packages', 'satispress' ); ?></a>
	</h2>

	<div id="satispress-settings" class="satispress-tab-panel is-active">
		<p>
			<?php _e( 'Your SatisPress repository is available at:', 'satispress' ); ?>
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $permalink ); ?></a>
		</p>
		<p>
			<?php _e( 'Add it to the <code>repositories</code> list in your composer.json:', 'satispress' ); ?>
		</p>

<pre class="satispress-repository-snippet"><code>{
	"repositories": [
		{
			"type": "composer",
			"url": "<?php echo satispress_get_packages_permalink( array( 'base' => true ) ); ?>"
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
