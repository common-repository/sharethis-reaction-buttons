<?php
/**
 * Reaction Button Settings Template
 *
 * The template wrapper for the reaction buttons settings page.
 *
 * @package ShareThisReactionButtons
 */

?>
<hr class="wp-header-end" style="display:none;">
<div class="wrap sharethis-wrap">
	<form action="options.php" method="post">
		<?php
		settings_fields( $this->menu_slug . '-reaction-buttons' );
		do_settings_sections( $this->menu_slug . '-reaction-buttons' );
		submit_button( esc_html__( 'Update', 'sharethis-reaction-buttons' ) );
		?>
	</form>
</div>
