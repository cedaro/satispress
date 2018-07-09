<?php
/**
 * Underscore.js templates.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

?>

<script type="text/html" id="tmpl-satispress-api-key-table">
	<table class="satispress-api-key-table widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'satispress' ); ?></th>
				<th><?php esc_html_e( 'User', 'satispress' ); ?></th>
				<th><?php esc_html_e( 'API Key', 'satispress' ); ?></th>
				<th><?php esc_html_e( 'Last Used', 'satispress' ); ?></th>
				<th><?php esc_html_e( 'Created', 'satispress' ); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="6">
					<?php esc_html_e( 'Add an API Key to access the SatisPress repository.', 'satispress' ); ?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6" class="satispress-create-api-key-form">
					<label>
						<span class="screen-reader-text"><?php esc_html_e( 'API Key Name', 'satispress' ); ?></span>
						<input type="text" id="satispress-create-api-key-name" placeholder="<?php esc_attr_e( 'Name', 'satispress' ); ?>" class="regular-text">
					</label>
					<button class="button"><?php esc_html_e( 'Create API Key', 'satispress' ); ?></button>
					<span class="satispress-create-api-key-feedback"></span>
				</td>
			</tr>
		</tfoot>
	</table>
</script>

<script type="text/html" id="tmpl-satispress-api-key-table-row">
	<th scope="row">{{ data.name }}</th>
	<th scope="row">{{ data.user }}</th>
	<td class="column-token">
		<input type="text" class="regular-text" value="{{ data.token }}" onclick="this.select();" readonly>
	</td>
	<td class="column-last-used">{{ data.last_used }}</td>
	<td class="column-created">{{ data.created }}</td>
	<td class="column-actions">
		<div class="satispress-dropdown-group">
			<button type="button" class="satispress-dropdown-toggle">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
					<path d="M5 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm12-2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-7 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
					<title><?php esc_html_e( 'Toggle dropdown', 'satispress' ); ?></title>
				</svg>
			</button>

			<div class="satispress-dropdown-group-items right">
				<ul>
					<li><button class="button-link button-link-delete js-revoke"><?php esc_html_e( 'Revoke', 'satispress' ); ?></button></li>
				</ul>
			</div>
		</div>
	</td>
</script>
