<?php
/**
 * Packages page view.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.2.0
 */

if ( ! empty( $packages ) ) :
	foreach ( $packages as $package ) :
		?>
		<table class="satispress-package widefat">
			<thead>
			<tr>
				<th colspan="2"><?php echo esc_html( $package->get_package_name() ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $description = $package->get_description() ) : ?>
				<tr>
					<td colspan="2"><?php echo esc_html( wp_strip_all_tags( $description ) ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $homepage = $package->get_homepage() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Homepage', 'satispress' ); ?></th>
					<td><a href="<?php echo esc_url( $homepage ); ?>"><?php echo esc_html( $homepage ); ?></a></td>
				</tr>
			<?php endif; ?>

			<tr>
				<th><?php esc_html_e( 'Authors', 'satispress' ); ?></th>
				<td><a href="<?php echo esc_url( $package->get_author_uri() ); ?>"><?php echo esc_html( $package->get_author() ); ?></a></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Releases', 'satispress' ); ?></th>
				<td>
					<?php $version = $package->get_version(); ?>
					<strong><a href="<?php echo esc_url( $package->get_archive_url( $version ) ); ?>"><?php echo esc_html( $version ); ?></a></strong><?php
					$versions = $package->get_cached_versions();
					if ( ! empty( $versions ) ) {
						$versions = array_map( function( $version ) use ( $package ) {
							return '<a href="' . esc_url( $package->get_archive_url( $version ) ) . '">' . esc_html( $version ) . '</a>';
						}, $versions);

						echo wp_kses_post( ', ' . implode( ', ', $versions ) );
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
endif;
