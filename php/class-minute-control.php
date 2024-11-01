<?php
/**
 * Minute Control.
 *
 * @package ShareThisReactionButtons
 */

namespace ShareThisReactionButtons;

/**
 * Minute Control Class
 *
 * @package ShareThisReactionButtons
 */
class Minute_Control {

	/**
	 * Plugin instance.
	 *
	 * @var object
	 */
	public $plugin;

	/**
	 * Class constructor.
	 *
	 * @param object $plugin Plugin class.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register the new share buttons metabox.
	 *
	 * @action add_meta_boxes
	 */
	public function reaction_buttons_metabox() {
		// Get all post types available.
		$post_types = array( 'post', 'page' );

		// Add the Share Buttons meta box to editor pages.
		add_meta_box( 'sharethis_reaction_buttons', esc_html__( 'Reaction Buttons', 'sharethis-reaction-buttons' ), array( $this, 'reaction_buttons_custom_box' ), $post_types, 'side', 'high' );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @action admin_enqueue_scripts
	 * @param string $hook The page hook name.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;

		// Enqueue the assets on editor pages.
		if ( true === in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_style( "{$this->plugin->assets_prefix}-meta-box" );
			wp_enqueue_script( "{$this->plugin->assets_prefix}-meta-box" );
			wp_add_inline_script(
				"{$this->plugin->assets_prefix}-meta-box",
				sprintf(
					'MinuteControl.boot( %s );',
					wp_json_encode(
						array(
							'postid' => $post->ID,
							'nonce'  => wp_create_nonce( $this->plugin->meta_prefix ),
						)
					)
				)
			);
		}
	}

	/**
	 * Call back function for the share buttons metabox.
	 */
	public function reaction_buttons_custom_box() {
		global $post_type;

		switch ( $post_type ) {
			case 'post':
				$iptype = 'post_';
				$sptype = 'posts';
				break;
			case 'page':
				$iptype = 'page_';
				$sptype = 'pages';
				break;
			default:
				$iptype = 'post_';
				$sptype = 'posts';
				break;
		}

		// Get all needed options for meta boxes.
		$options = get_option( 'sharethis_inline-reaction_settings' );
		$enable  = get_option( 'sharethis_inline-reactions' );

		// Include the meta box template.
		include_once "{$this->plugin->dir_path}/templates/minute-control/meta-box.php";
	}

	/**
	 * AJAX Call back function to add a post / page to ommit / show list.
	 *
	 * @action wp_ajax_reaction_update_list
	 */
	public function reaction_update_list() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		if ( ! isset( $_POST['checked'], $_POST['placement'], $_POST['postid'] ) || '' === $_POST['checked'] ) { // WPCS: input var okay.
			wp_send_json_error( 'Add to list failed.' );
		}

		// Set and sanitize post values.
		$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		$type = sanitize_text_field( wp_unslash( $type ) );

		$checked = filter_input( INPUT_POST, 'checked', FILTER_SANITIZE_STRING );
		$checked = sanitize_text_field( wp_unslash( $checked ) );

		$placement = filter_input( INPUT_POST, 'placement', FILTER_SANITIZE_STRING );
		$placement = sanitize_text_field( wp_unslash( $placement ) );

		$postid = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );
		$postid = sanitize_text_field( wp_unslash( $postid ) );

		$onoff     = 'true' === $checked ? 'on' : 'off';
		$opposite  = 'on' === $onoff ? 'off' : 'on';
		$placement = false === empty( $placement ) ? '_' . $placement : '';
		$postid    = intval( $postid );

		// Create remaining variables needed for list placement.
		$post_info = get_post( $postid );
		$post_type = $post_info->post_type;
		$option    = 'sharethis_inline-reaction_' . $post_type . $placement . '_' . $onoff;
		$oppose    = 'sharethis_inline-reaction_' . $post_type . $placement . '_' . $opposite;
		$title     = $post_info->post_title;

		// Get current list and opposing list options.
		$current_list   = get_option( $option );
		$current_oppose = get_option( $oppose );
		$current_list   = isset( $current_list ) && null !== $current_list && false !== $current_list ? $current_list : '';
		$current_oppose = isset( $current_oppose ) && null !== $current_oppose && false !== $current_oppose ? $current_oppose : '';

		// Add post id and title to current list.
		if ( is_array( $current_list ) && array() !== $current_list ) {
			$current_list[ $title ] = (int) $postid;
		} else {
			$current_list = array(
				$title => (int) $postid,
			);
		}

		// Remove item from opposing list.
		if (
			true === is_array( $current_oppose )
			&& array() !== $current_oppose
			&& true === in_array( $postid, array_map( 'intval', $current_oppose ), true )
		) {
			unset( $current_oppose[ $title ] );
			delete_option( $oppose );
		}

		// Update both list options.
		update_option( $option, $current_list );
		update_option( $oppose, $current_oppose );
	}

	/**
	 * Helper function to determine whether to check box or not.
	 *
	 * @param string $placement The position of the button in question.
	 */
	private function is_box_checked( $placement = '' ) {
		global $post, $post_type;

		$options = array(
			'true'  => 'sharethis_inline-reaction_' . $post_type . $placement . '_on',
			'false' => 'sharethis_inline-reaction_' . $post_type . $placement . '_off',
		);

		$default_option = get_option( 'sharethis_inline-reaction_settings' );
		$default_option = isset( $default_option ) && null !== $default_option && false !== $default_option ? $default_option : '';

		$default = $default_option[ "sharethis_inline-reaction_{$post_type}{$placement}" ];

		foreach ( $options as $answer => $option ) {
			$current_list  = get_option( $option );
			$current_list  = isset( $current_list ) && null !== $current_list && false !== $current_list ? $current_list : '';
			$answer_minute = (
				is_array( $current_list )
				&&
				in_array( (int) $post->ID, array_map( 'intval', $current_list ), true )
			);

			if ( $answer_minute ) {
				return $answer;
			}
		}

		return $default;
	}

	/**
	 * Register the inline share button shortcode
	 *
	 * @shortcode sharethis-reaction-buttons
	 *
	 * @return string
	 */
	public function reaction_shortcode() {
		global $post;

		$data_url = '';

		if ( is_archive() || is_front_page() || is_tag() ) {
			$data_url = esc_attr( 'data-url=' . get_permalink( $post->ID ) );
		}

		// Build container.
		return '<div class="sharethis-inline-reaction-buttons" ' . $data_url . '></div>';
	}

	/**
	 * Set inline container based on plugin config.
	 *
	 * @param string $content The post's content.
	 *
	 * @filter the_content
	 *
	 * @return string
	 */
	public function set_reaction_content( $content ) {
		global $post;

		// Get inline settings.
		$reaction_settings = get_option( 'sharethis_inline-reaction_settings', true );

		$excerpt = null !== $reaction_settings && false !== $reaction_settings && 'true' === $reaction_settings['sharethis_excerpt'] ? true : false;

		if ( $excerpt && is_archive() || $excerpt && is_home() ) {
			return $content . $this->get_reaction_container( $reaction_settings, 'sharethis_excerpt', $post );
		} elseif ( ( is_home() || is_archive() ) && ! $excerpt ) {
			return $content;
		}

		if ( null !== $reaction_settings && false !== $reaction_settings && is_array( $reaction_settings ) ) {
			foreach ( $reaction_settings as $type => $value ) {
				$position  = $this->get_position( $type, $value );
				$container = $this->get_reaction_container( $reaction_settings, $type );

				if ( '' !== $position ) {
					switch ( $position ) {
						case 'top':
							$content = $container . $content;
							break;
						case 'bottom':
							$content = $content . $container;
							break;
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Helper function to determine the inline button container.
	 *
	 * @param array  $settings The current inline settings.
	 * @param string $type The type of button setting.
	 * @param object $post The current post object.
	 *
	 * @return string
	 */
	private function get_reaction_container( $settings, $type, $post = '' ) {
		$data_url = 'sharethis_excerpt' === $type && '' !== $post ? esc_attr( 'data-url=' . get_permalink( $post->ID ) ) : '';
		$margin_t = isset( $settings[ "{$type}_margin_top" ] ) ? $settings[ "{$type}_margin_top" ] . 'px' : '';
		$margin_b = isset( $settings[ "{$type}_margin_bottom" ] ) ? $settings[ "{$type}_margin_bottom" ] . 'px' : '';
		$margin   = '';

		if ( ! in_array( '', array( $margin_t, $margin_b ), true ) ) {
			$margin = 'margin-top: ' . $margin_t . '; margin-bottom: ' . $margin_b . ';';
		}

		return '<div style="' . esc_attr( $margin ) . '" class="sharethis-inline-reaction-buttons" ' . $data_url . '></div>';
	}

	/**
	 * Set reaction container based on plugin config.
	 *
	 * @param string $excerpt The excerpt of the post.
	 *
	 * @filter get_the_excerpt
	 *
	 * @return string
	 */
	public function set_inline_excerpt( $excerpt ) {
		global $post;

		if ( is_admin() ) {
			return;
		}

		// Get inline settings.
		$reaction_settings = get_option( 'sharethis_reaction_settings' );

		$container = $this->get_reaction_container( $reaction_settings, 'sharethis_reaction_excerpt', $post );

		if ( null === $reaction_settings || false === $reaction_settings || ! is_array( $reaction_settings ) ) {
			return $excerpt;
		}

		$excerpt = isset( $reaction_settings['sharethis_reaction_excerpt'] ) && 'true' === $reaction_settings['sharethis_reaction_excerpt'] ? $excerpt . $container : $excerpt;

		return $excerpt;
	}


	/**
	 * Determine the position of the reaction buttons.
	 *
	 * @param string $type The button type.
	 * @param string $value The value of the button.
	 *
	 * @return string
	 */
	private function get_position( $type, $value ) {
		global $post;

		if ( false === isset( $post->ID ) ) {
			return '';
		}

		$page_option_on  = get_option( $type . '_on' );
		$page_option_off = get_option( $type . '_off' );
		$page_option_on  = is_array( $page_option_on ) ? array_values( $page_option_on ) : array();
		$page_option_off = is_array( $page_option_off ) ? array_values( $page_option_off ) : array();

		$type_array = explode( '_', $type );

		$position = '';

		$show = (
			'true' === $value
			&&
			! in_array( (int) $post->ID, $page_option_off, true )
			||
			in_array( (int) $post->ID, $page_option_on, true ) );

		if ( in_array( 'top', $type_array, true ) && in_array( $post->post_type, $type_array, true ) ) {
			$position = 'top';
		} elseif ( in_array( 'bottom', explode( '_', $type ), true ) && in_array( $post->post_type, $type_array, true ) ) {
			$position = 'bottom';
		}

		if ( $show ) {
			return $position;
		}

		return '';
	}

	/**
	 * Enqueue the custom gutenberg block script.
	 *
	 * @action enqueue_block_editor_assets
	 */
	public function enqueue_custom_blocks() {
		wp_enqueue_script( "{$this->plugin->assets_prefix}-blocks", "{$this->plugin->dir_url}js/blocks.js", array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-components' ), time(), true );
	}

	/**
	 * Register new block category for share buttons.
	 *
	 * @param array    $categories The current block categories.
	 * @param \WP_Post $post       The post object.
	 *
	 * @filter block_categories_all 999
	 */
	public function st_block_category( $categories, $post ) {
		if ( ! is_plugin_active( 'sharethis-share-buttons/sharethis-share-buttons.php' ) && ! is_plugin_active( 'sharethis-follow-buttons/sharethis-follow-buttons.php' ) ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'st-blocks',
						'title' => __( 'ShareThis Blocks', 'sharethis-reaction-buttons' ),
					),
				)
			);
		}

		return $categories;
	}
}
