<?php
/**
 * Enable Button Template
 *
 * The template wrapper for the enable button settings.
 *
 * @package ShareThisReactionButtons
 */

$option_value = get_option( 'sharethis_inline-reaction', true );
?>
<div id="inline-reaction" class="enable-buttons">
	<label class="share-on">
		<input type="radio" id="sharethis_inline-reaction_on" name="sharethis_inline-reaction" value="true" <?php echo esc_attr( checked( 'true', $option_value, false ) ); ?>>
		<div class="label-text"><?php esc_html_e( 'On', 'sharethis-reaction-buttons' ); ?></div>
	</label>
	<label class="share-off">
		<input type="radio" id="sharethis_inline-reaction_off" name="sharethis_inline-reaction" value="false" <?php echo esc_attr( checked( 'false', $option_value, false ) ); ?>>
		<div class="label-text"><?php esc_html_e( 'Off', 'sharethis-reaction-buttons' ); ?></div>
	</label>
</div>
