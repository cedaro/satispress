<?php
if ( ! empty( $packages ) ) :
	foreach ( $packages as $package ) :
		?>
		<table class="satispress-package widefat">
			<thead>
				<tr>
					<th colspan="2"><?php echo $package->get_package_name(); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $description = $package->get_description() ) : ?>
					<tr>
						<td colspan="2"><?php echo wp_strip_all_tags( $description ); ?></td>
					</tr>
				<?php endif; ?>

				<?php if ( $homepage = $package->get_homepage() ) : ?>
					<tr>
						<th><?php _e( 'Homepage', 'satispress' ); ?></th>
						<td><a href="<?php echo esc_url( $homepage ); ?>"><?php echo esc_html( $homepage ); ?></a></td>
					</tr>
				<?php endif; ?>

				<tr>
					<th><?php _e( 'Authors', 'satispress' ); ?></th>
					<td><a href="<?php echo esc_url( $package->get_author_uri() ); ?>"><?php echo esc_html( $package->get_author() ); ?></a></td>
				</tr>
				<tr>
					<th><?php _e( 'Releases', 'satispress' ); ?></th>
					<td>
						<?php $version = $package->get_version(); ?>
						<a href="<?php echo $package->get_archive_url( $version ); ?>"><?php echo $version; ?></a><?php
						$versions = $package->get_cached_versions();
						if ( ! empty( $versions ) ) {
							$versions = array_map( function($version) use ($package) {
								return '<a href="' . $package->get_archive_url( $version ) . '">' . $version . '</a>';
							}, $versions);

							echo ', ' . implode(', ', $versions );
						}
						?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Package Type', 'satispress' ); ?></th>
					<td><?php echo esc_html( $package->get_type() ); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
	endforeach;
endif;
