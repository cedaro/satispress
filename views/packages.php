<?php
/**
 * Views: Packages page
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

foreach ( $packages as $package ) :
	?>
	<table class="satispress-package widefat">
		<thead>
		<tr>
			<th colspan="2"><?php echo esc_html( $package->get_name() ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$description = $package->get_description();
		if ( $description ) :
			?>
			<tr>
				<td colspan="2"><?php echo esc_html( wp_strip_all_tags( $description ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php
		$homepage = $package->get_homepage();
		if ( $homepage ) :
			?>
			<tr>
				<th><?php esc_html_e( 'Homepage', 'satispress' ); ?></th>
				<td><a href="<?php echo esc_url( $homepage ); ?>" target="_blank" rel="noopener noreferer"><?php echo esc_html( $homepage ); ?></a></td>
			</tr>
		<?php endif; ?>

		<tr>
			<th><?php esc_html_e( 'Authors', 'satispress' ); ?></th>
			<td><a href="<?php echo esc_url( $package->get_author_url() ); ?>" target="_blank" rel="noopener noreferer"><?php echo esc_html( $package->get_author() ); ?></a></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Releases', 'satispress' ); ?></th>
			<td class="satispress-releases">
				<?php
				if ( $package->has_releases() ) {
					$versions = array_map( function( $release ) use ( $package ) {
						return sprintf(
							'<a href="%1$s" data-version="%2$s" class="button satispress-release">%3$s</a>',
							esc_url( $release->get_download_url() ),
							esc_attr( $release->get_version() ),
							esc_html( $release->get_version() )
						);
					}, $package->get_releases() );

					echo wp_kses(
						implode( ' ', array_filter( $versions ) ),
						[
							'a' => [
								'class'        => true,
								'data-version' => true,
								'href'         => true,
							],
						]
					);
				}
				?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Package Type', 'satispress' ); ?></th>
			<td><code><?php echo esc_html( $package->get_type() ); ?></code></td>
		</tr>
		</tbody>
	</table>

	<script type="text/html" id="tmpl-satispress-release-actions">
		<table>
			<tr>
				<td><?php esc_html_e( 'Download URL', 'satispress' ); ?></td>
				<td><input type="text" value="{{ data.download_url }}" class="regular-text" readonly></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Require', 'satispress' ); ?></td>
				<td><input type="text" value='"{{ data.name }}": "{{ data.version }}"' class="regular-text" readonly></td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="{{ data.download_url }}" class="button button-primary">
						<?php
						/* translators: %s: version number */
						printf( esc_html__( 'Download %s', 'satispress' ), '{{ data.version }}' );
						?>
					</a>
				</td>
			</tr>
		</table>
	</script>
	<?php
endforeach;

if ( empty( $packages ) ) {
	$allowed_tags = [
		'a'  => [
			'href' => true,
		],
		'em' => [],
	];
	?>
	<div class="satispress-card">
		<h3><?php esc_html_e( 'Whitelisting Packages', 'satispress' ); ?></h3>
		<p>
			<?php esc_html_e( 'Plugins and themes need to be whitelisted to make them available as Composer packages.', 'satispress' ); ?>
		</p>
		<p>
			<a href="https://github.com/blazersix/satispress/blob/develop/docs/Whitelisting.md" target="_blank" rel="noopener noreferer"><em><?php esc_html_e( 'Read more about whitelisting plugins and themes.', 'satispress' ); ?></em></a>
		</p>

		<h4><?php esc_html_e( 'Plugins', 'satispress' ); ?></h4>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: Plugins screen URL */
					__( 'Plugins can be whitelisted by visiting the <a href="%s"><em>Plugins &rarr; Installed Plugins</em></a> screen and toggling the checkbox for each plugin in the "SatisPress" column.', 'satispress' ),
					esc_url( self_admin_url( 'plugins.php' ) )
				),
				$allowed_tags
			);
			?>
		</p>

		<h4><?php esc_html_e( 'Themes', 'satispress' ); ?></h4>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: SatisPress settings screen URL */
					__( 'Themes can be toggled on the <a href="%s"><em>Settings &rarr; SatisPress</em></a> screen.', 'satispress' ),
					esc_url( self_admin_url( 'options-general.php?page=satispress#satispress-settings' ) )
				),
				$allowed_tags
			);
			?>
		</p>
	</div>
	<?php
}
