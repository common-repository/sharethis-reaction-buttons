<?php
/**
 * Meta Box Template
 *
 * The template wrapper for post/page meta box.
 *
 * @package ShareThisReactionButtons
 */

?>
<div id="sharethis-reaction-meta-box">
	<div id="inline-reaction" class="button-setting-wrap">
		<div class="button-check-wrap">
			<input class="top" type="checkbox" id="sharethis-reaction-top-post" <?php echo checked( 'true', $this->is_box_checked( '_top' ) ); ?>>

			<label for="sharethis-top-post">
				<?php
				// translators: The post type.
				printf( esc_html__( 'Include at top of %1$s content', 'sharethis-reaction-buttons' ), esc_html( $post_type ) );
				?>
			</label>
		</div>
		<div class="button-check-wrap">
			<input class="bottom" type="checkbox" id="sharethis-reaction-bottom-post" <?php echo checked( 'true', $this->is_box_checked( '_bottom' ) ); ?>>

			<label for="sharethis-bottom-post">
				<?php
				// translators: The post type.
				printf( esc_html__( 'Include at bottom of %1$s content', 'sharethis-reaction-buttons' ), esc_html( $post_type ) );
				?>
			</label>
		</div>
		<input type="text" class="sharethis-shortcode" readonly value="[sharethis-reaction-buttons]">

		<span class="under-message"><?php esc_html_e( 'Reaction button shortcode.', 'sharethis-reaction-buttons' ); ?></span>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=sharethis-share-buttons' ) ); ?>">
		<?php esc_html_e( 'Update your default settings', 'sharethis-reaction-buttons' ); ?>
	</a>
</div>
