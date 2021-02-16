<?php
/**
 * Views: Composer tab content.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

?>

<p>
	<?php esc_html_e( 'Your SatisPress repository is available at:', 'satispress' ); ?>
	<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $permalink ); ?></a>
</p>
<p>
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Need to update global variable.
	$allowed_html = [ 'code' => [] ];
	printf(
		/* translators: 1: <code>repositories</code>, 2: <code>composer.json</code> */
		esc_html__( 'Add it to the %1$s list in your %2$s:', 'satispress' ),
		'<code>repositories</code>',
		'<code>composer.json</code>'
	);
	?>
</p>

<pre class="satispress-composer-snippet"><code>{
	"repositories": {
		"satispress": {
			"type": "composer",
			"url": "<?php echo esc_url( get_packages_permalink( [ 'base' => true ] ) ); ?>"
		}
	}
}</code></pre>

<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Need to update global variable.
$allowed_html = [ 'code' => [] ];
printf(
	/* translators: 1: <code>config</code> */
	esc_html__( 'Or run the %1$s command:', 'satispress' ),
	'<code>config</code>'
);
?>

<p>
	<input
		type="text"
		class="satispress-cli-field large-text"
		readonly
		value="composer config repositories.satispress composer <?php echo esc_url( get_packages_permalink( [ 'base' => true ] ) ); ?>"
		onclick="this.select();"
	>
</p>
