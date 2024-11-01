<?php
/**
 * Platform button configurations
 *
 * The template wrapper for the platform button configurations.
 *
 * @package ShareThisReactionButtons
 */

?>
<div data-enabled="<?php echo esc_attr( $enabled ); ?>" class="inline-reaction-platform platform-config-wrapper">
	<?php if ( 'Disabled' === $enabled ) : ?>
		<button class="enable-tool" data-button="inline-reaction">Enable Tool</button>
	<?php endif; ?>

	<div class="sharethis-selected-reactions">
		<div id="inline-reaction-8" class="sharethis-inline-reaction-buttons"></div>
	</div>

	<div>
		<p class="st-preview-message">
			⇧ <?php echo esc_html__( 'Preview: click and drag to reorder' ); ?> ⇧
		</p>

		<h3><?php echo esc_html__( 'Select your reactions', 'sharethis-reaction-buttons' ); ?></h3>

		<span><?php echo esc_html__( 'click a reaction to add or remove it from your preview.', 'sharethis-reaction-buttons' ); ?></span>
	</div>
	<div class="reaction-buttons">
		<?php foreach ( $reactions as $reaction_name => $image ) : ?>
			<img class="reaction-button" data-reaction="<?php echo esc_attr( $reaction_name ); ?>" data-selected="true" alt="<?php echo esc_attr( $reaction_name ); ?>" src="<?php echo esc_attr( $image ); ?>">
		<?php endforeach; ?>
	</div>

	<hr>

	<div class="button-alignment">
		<h3>Alignment</h3>

		<div class="alignment-button" data-alignment="left" data-selected="false">
			<div class="top">
				<div class="box"></div>
				<div class="box"></div>
				<div class="box"></div>
			</div>
			<div class="bottom">Left</div>
		</div>

		<div class="alignment-button" data-alignment="center" data-selected="true">
			<div class="top">
				<div class="box"></div>
				<div class="box"></div>
				<div class="box"></div>
			</div>
			<div class="bottom">Center</div>
		</div>

		<div class="alignment-button" data-alignment="right" data-selected="false">
			<div class="top">
				<div class="box"></div>
				<div class="box"></div>
				<div class="box"></div>
			</div><div class="bottom">Right</div>
		</div>
	</div>
	<div class="language-config">
		<h3 class="center"><?php echo esc_html__( 'Languages', 'sharethis-reaction-buttons' ); ?></h3>

		<span class="select-field">
			<select id="st-language">
				<?php foreach ( $languages as $language_name => $code ) : ?>
					<option class="language-option" value="<?php echo esc_attr( $code ); ?>">
						<?php echo esc_html( $language_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</span>
	</div>
	<?php if ( 'Enabled' === $enabled ) : ?>
		<button class="disable-tool" data-button="inline-reaction">Disable Tool</button>
	<?php endif; ?>
</div>
