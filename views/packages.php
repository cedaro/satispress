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

foreach ( $repository->all() as $package ) :
	?>
	<table class="satispress-package widefat">
		<thead>
		<tr>
			<th colspan="2"><?php echo esc_html( $package->get_package_name() ); ?></th>
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
						$is_installed = $release->get_version() === $release->get_package()->get_installed_version();

						return sprintf(
							$is_installed ? '<a href="%1$s"><strong>%2$s</strong></a>' : '<a href="%1$s">%2$s</a>',
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
