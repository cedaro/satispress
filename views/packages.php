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
				<td><a href="<?php echo esc_url( $homepage ); ?>"><?php echo esc_html( $homepage ); ?></a></td>
			</tr>
		<?php endif; ?>

		<tr>
			<th><?php esc_html_e( 'Authors', 'satispress' ); ?></th>
			<td><a href="<?php echo esc_url( $package->get_author_url() ); ?>"><?php echo esc_html( $package->get_author() ); ?></a></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Releases', 'satispress' ); ?></th>
			<td>
				<?php
				if ( $package->has_releases() ) {
					$versions = array_map( function( $release ) {
						return sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url( $release->get_download_url() ),
							esc_html( $release->get_version() )
						);
					}, $package->get_releases() );

					echo wp_kses_post( implode( ', ', array_filter( $versions ) ) );
				}
				?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Package Type', 'satispress' ); ?></th>
			<td><?php echo esc_html( $package->get_type() ); ?></td>
		</tr>
		</tbody>
	</table>
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
			<a href="https://github.com/blazersix/satispress/blob/develop/docs/Whitelisting.md"><em><?php esc_html_e( 'Read more about whitelisting plugins and themes.', 'satispress' ); ?></em></a>
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
