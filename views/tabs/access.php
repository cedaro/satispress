<?php
/**
 * Views: Access tab content.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.8.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

?>
<p>
	<?php esc_html_e( 'API keys are used to access your SatisPress repository and download packages. Your personal API keys appear below or you can create keys for other users by editing their accounts.', 'satispress' ); ?>
</p>

<p>
	<?php
	/* translators: %s: <code>satispress</code> */
	printf( esc_html__( 'The password for all API keys is %s.', 'satispress' ), '<code>satispress</code>' );
	?>
</p>

<div id="satispress-api-key-manager"></div>

<p>
	<a href="https://github.com/cedaro/satispress/blob/develop/docs/security.md" target="_blank" rel="noopener noreferer"><em><?php esc_html_e( 'Read more about securing your SatisPress repository.', 'satispress' ); ?></em></a>
</p>
