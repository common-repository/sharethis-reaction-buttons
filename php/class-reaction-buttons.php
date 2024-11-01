<?php
/**
 * Reaction Buttons.
 *
 * @package ShareThisReactionButtons
 */

namespace ShareThisReactionButtons;

/**
 * Reaction Buttons Class
 *
 * @package ShareThisReactionButtons
 */
class Reaction_Buttons {

	/**
	 * Plugin instance.
	 *
	 * @var object
	 */
	public $plugin;

	/**
	 * Button Widget instance.
	 *
	 * @var object
	 */
	public $button_widget;

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	public $menu_slug;

	/**
	 * Menu hook suffix.
	 *
	 * @var string
	 */
	private $hook_suffix;

	/**
	 * Holds the settings sections.
	 *
	 * @var string
	 */
	public $setting_sections;

	/**
	 * Holds the settings fields.
	 *
	 * @var string
	 */
	public $setting_fields;

	/**
	 * Holds the reaction settings fields.
	 *
	 * @var string
	 */
	public $reaction_setting_fields;

	/**
	 * Languages available for sharing in.
	 *
	 * @var array
	 */
	public $languages;

	/**
	 * Class constructor.
	 *
	 * @param object $plugin Plugin class.
	 * @param object $button_widget Button Widget class.
	 */
	public function __construct( $plugin, $button_widget ) {
		$this->button_widget = $button_widget;
		$this->plugin        = $plugin;
		$this->menu_slug     = 'sharethis';
		$this->set_settings();
		$this->set_languages();

		// Configure your buttons notice on activation.
		register_activation_hook( $this->plugin->dir_path . '/sharethis-reaction-buttons.php', array( $this, 'st_activation_hook' ) );

		// Clean up plugin information on deactivation.
		register_deactivation_hook( $this->plugin->dir_path . '/sharethis-reaction-buttons.php', array( $this, 'st_deactivation_hook' ) );
	}

	/**
	 * Set the settings sections and fields.
	 *
	 * @access private
	 */
	private function set_settings() {
		// Sections config.
		$this->setting_sections = array(
			esc_html__( 'Reaction Buttons', 'sharethis-reaction-buttons' ),
		);

		// Setting configs.
		$this->setting_fields = array(
			array(
				'id_suffix'   => 'inline-reaction_settings',
				'description' => $this->get_descriptions(),
				'callback'    => 'config_settings',
				'section'     => 'reaction_buttons_section',
				'arg'         => 'inline-reaction',
			),
			array(
				'id_suffix'   => 'shortcode',
				'description' => $this->get_descriptions( '', 'shortcode' ),
				'callback'    => 'shortcode_template',
				'section'     => 'reaction_button_section',
				'arg'         => array(
					'type'  => 'shortcode',
					'value' => '[sharethis-reaction-buttons]',
				),
			),
			array(
				'id_suffix'   => 'template',
				'description' => $this->get_descriptions( '', 'template' ),
				'callback'    => 'shortcode_template',
				'section'     => 'reaction_button_section',
				'arg'         => array(
					'type'  => 'template',
					'value' => '<?php echo sharethis_reaction_buttons(); ?>',
				),
			),
		);

		// Inline setting array.
		$this->reaction_setting_fields = array(
			array(
				'id_suffix' => 'inline-reaction_post_top',
				'title'     => esc_html__( 'Top of post body', 'sharethis-reaction-buttons' ),
				'callback'  => 'onoff_cb',
				'type'      => '',
				'default'   => array(
					'true'   => 'checked="checked"',
					'false'  => '',
					'margin' => true,
				),
			),
			array(
				'id_suffix' => 'inline-reaction_post_bottom',
				'title'     => esc_html__( 'Bottom of post body', 'sharethis-reaction-buttons' ),
				'callback'  => 'onoff_cb',
				'type'      => '',
				'default'   => array(
					'true'   => '',
					'false'  => 'checked="checked"',
					'margin' => true,
				),
			),
			array(
				'id_suffix' => 'inline-reaction_page_top',
				'title'     => esc_html__( 'Top of page body', 'sharethis-reaction-buttons' ),
				'callback'  => 'onoff_cb',
				'type'      => '',
				'default'   => array(
					'true'   => '',
					'false'  => 'checked="checked"',
					'margin' => true,
				),
			),
			array(
				'id_suffix' => 'inline-reaction_page_bottom',
				'title'     => esc_html__( 'Bottom of page body', 'sharethis-reaction-buttons' ),
				'callback'  => 'onoff_cb',
				'type'      => '',
				'default'   => array(
					'true'   => '',
					'false'  => 'checked="checked"',
					'margin' => true,
				),
			),
			array(
				'id_suffix' => 'excerpt',
				'title'     => esc_html__( 'Include in excerpts', 'sharethis-reaction-buttons' ),
				'callback'  => 'onoff_cb',
				'type'      => '',
				'default'   => array(
					'true'   => '',
					'false'  => 'checked="checked"',
					'margin' => true,
				),
			),
		);
	}

	/**
	 * Add in ShareThis menu option.
	 *
	 * @action admin_menu
	 */
	public function define_sharethis_menus() {
		$this->share_buttons_settings();
	}

	/**
	 * Add Reaction Buttons settings page.
	 */
	public function share_buttons_settings() {
		// Menu base64 Encoded icon.
		$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDE2IDE2IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCA0NC4xICg0MTQ1NSkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+RmlsbCAzPC90aXRsZT4KICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPgogICAgPGRlZnM+PC9kZWZzPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9IkRlc2t0b3AtSEQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMC4wMDAwMDAsIC00MzguMDAwMDAwKSIgZmlsbD0iI0ZFRkVGRSI+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMy4xNTE2NDMyLDQ0OS4xMDMwMTEgQzIyLjcyNjg4NzcsNDQ5LjEwMzAxMSAyMi4zMzM1MDYyLDQ0OS4yMjg5OSAyMS45OTcwODA2LDQ0OS40Mzc5ODkgQzIxLjk5NTE0OTksNDQ5LjQzNTA5MyAyMS45OTcwODA2LDQ0OS40Mzc5ODkgMjEuOTk3MDgwNiw0NDkuNDM3OTg5IEMyMS44ODA3NTU1LDQ0OS41MDg5NDMgMjEuNzM1NDY5OCw0NDkuNTQ1NjI2IDIxLjU4OTIxODgsNDQ5LjU0NTYyNiBDMjEuNDUzMTA0LDQ0OS41NDU2MjYgMjEuMzE5ODg1Miw0NDkuNTA3NDk0IDIxLjIwODg2OTYsNDQ5LjQ0NTIyOSBMMTQuODczNzM4Myw0NDYuMDM4OTggQzE0Ljc2NDE3MDcsNDQ1Ljk5MDIzIDE0LjY4NzkwNzgsNDQ1Ljg3ODczMSAxNC42ODc5MDc4LDQ0NS43NTEzMDUgQzE0LjY4NzkwNzgsNDQ1LjYyMzM5NSAxNC43NjUxMzYsNDQ1LjUxMTg5NyAxNC44NzQ3MDM2LDQ0NS40NjI2NjQgTDIxLjIwODg2OTYsNDQyLjA1Njg5NyBDMjEuMzE5ODg1Miw0NDEuOTk1MTE1IDIxLjQ1MzEwNCw0NDEuOTU2NTAxIDIxLjU4OTIxODgsNDQxLjk1NjUwMSBDMjEuNzM1NDY5OCw0NDEuOTU2NTAxIDIxLjg4MDc1NTUsNDQxLjk5MzY2NyAyMS45OTcwODA2LDQ0Mi4wNjQ2MiBDMjEuOTk3MDgwNiw0NDIuMDY0NjIgMjEuOTk1MTQ5OSw0NDIuMDY3MDM0IDIxLjk5NzA4MDYsNDQyLjA2NDYyIEMyMi4zMzM1MDYyLDQ0Mi4yNzMxMzcgMjIuNzI2ODg3Nyw0NDIuMzk5MTE1IDIzLjE1MTY0MzIsNDQyLjM5OTExNSBDMjQuMzY2NTQwMyw0NDIuMzk5MTE1IDI1LjM1MTY4MzQsNDQxLjQxNDQ1NSAyNS4zNTE2ODM0LDQ0MC4xOTk1NTggQzI1LjM1MTY4MzQsNDM4Ljk4NDY2IDI0LjM2NjU0MDMsNDM4IDIzLjE1MTY0MzIsNDM4IEMyMi4wMTYzODc2LDQzOCAyMS4wOTMwMjcyLDQzOC44NjMwMjYgMjAuOTc1MjU0MSw0MzkuOTY3MzkgQzIwLjk3MTM5MjYsNDM5Ljk2MzA0NiAyMC45NzUyNTQxLDQzOS45NjczOSAyMC45NzUyNTQxLDQzOS45NjczOSBDMjAuOTUwNjM3NSw0NDAuMjM5MTM3IDIwLjc2OTE1MTEsNDQwLjQ2NzkyNiAyMC41MzYwMTgzLDQ0MC41ODQyNTEgTDE0LjI3OTU2MzMsNDQzLjk0NzU0MiBDMTQuMTY0MjAzNiw0NDQuMDE3MDQ3IDE0LjAyNDIyNzMsNDQ0LjA1NjE0NCAxMy44Nzk0MjQzLDQ0NC4wNTYxNDQgQzEzLjcwODU1NjgsNDQ0LjA1NjE0NCAxMy41NDgzMDgxLDQ0NC4wMDQ0OTggMTMuNDIwODgxNSw0NDMuOTEwMzc2IEMxMy4wNzUyODUsNDQzLjY4NDk2NiAxMi42NjUwMDk4LDQ0My41NTEyNjQgMTIuMjIxOTEyNiw0NDMuNTUxMjY0IEMxMS4wMDcwMTU1LDQ0My41NTEyNjQgMTAuMDIyMzU1MSw0NDQuNTM2NDA3IDEwLjAyMjM1NTEsNDQ1Ljc1MTMwNSBDMTAuMDIyMzU1MSw0NDYuOTY2MjAyIDExLjAwNzAxNTUsNDQ3Ljk1MDg2MiAxMi4yMjE5MTI2LDQ0Ny45NTA4NjIgQzEyLjY2NTAwOTgsNDQ3Ljk1MDg2MiAxMy4wNzUyODUsNDQ3LjgxNzY0MyAxMy40MjA4ODE1LDQ0Ny41OTIyMzMgQzEzLjU0ODMwODEsNDQ3LjQ5NzYyOSAxMy43MDg1NTY4LDQ0Ny40NDY0NjUgMTMuODc5NDI0Myw0NDcuNDQ2NDY1IEMxNC4wMjQyMjczLDQ0Ny40NDY0NjUgMTQuMTY0MjAzNiw0NDcuNDg1MDc5IDE0LjI3OTU2MzMsNDQ3LjU1NDU4NSBMMjAuNTM2MDE4Myw0NTAuOTE4MzU4IEMyMC43Njg2Njg0LDQ1MS4wMzQyMDEgMjAuOTUwNjM3NSw0NTEuMjYzNDcyIDIwLjk3NTI1NDEsNDUxLjUzNTIxOSBDMjAuOTc1MjU0MSw0NTEuNTM1MjE5IDIwLjk3MTM5MjYsNDUxLjUzOTU2MyAyMC45NzUyNTQxLDQ1MS41MzUyMTkgQzIxLjA5MzAyNzIsNDUyLjYzOTEwMSAyMi4wMTYzODc2LDQ1My41MDI2MDkgMjMuMTUxNjQzMiw0NTMuNTAyNjA5IEMyNC4zNjY1NDAzLDQ1My41MDI2MDkgMjUuMzUxNjgzNCw0NTIuNTE3NDY2IDI1LjM1MTY4MzQsNDUxLjMwMjU2OSBDMjUuMzUxNjgzNCw0NTAuMDg3NjcyIDI0LjM2NjU0MDMsNDQ5LjEwMzAxMSAyMy4xNTE2NDMyLDQ0OS4xMDMwMTEiIGlkPSJGaWxsLTMiPjwvcGF0aD4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==';

		if ( empty( $GLOBALS['admin_page_hooks']['sharethis-share-buttons'] ) ) {
			// Main sharethis menu.
			$this->hook_suffix = add_menu_page(
				$this->get_descriptions( '', 'share_buttons' ),
				__( 'ShareThis', 'sharethis-share-buttons' ),
				'manage_options',
				$this->menu_slug . '-share-buttons',
				array( $this, 'share_button_display' ),
				$icon,
				26
			);
		} else {
			$this->hook_suffix = add_submenu_page(
				$this->menu_slug . '-share-buttons',
				$this->get_descriptions( '', 'reaction_buttons' ),
				__( 'Reaction Buttons', 'sharethis-reaction-buttons' ),
				'manage_options',
				$this->menu_slug . '-reaction-buttons',
				array( $this, 'share_button_display' )
			);
		}
	}

	/**
	 * Enqueue main MU script.
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_mu() {
		if ( ! wp_script_is( 'sharethis-reaction-buttons-mu', 'enqueued' ) ) {
			wp_enqueue_script( "{$this->plugin->assets_prefix}-mu" );
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @action admin_enqueue_scripts
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		$reaction            = get_option( 'sharethis_reaction' );
		$first_exists        = get_option( 'sharethis_first_product' );
		$first_button        = false !== $first_exists && null !== $first_exists ? $first_exists : '';
		$first_exists        = false === $first_exists || null === $first_exists || '' === $first_exists ? true : false;
		$propertyid          = explode( '-', get_option( 'sharethis_property_id' ), 2 );
		$property_id         = isset( $propertyid[0] ) ? $propertyid[0] : '';
		$token               = get_option( 'sharethis_token' );
		$secret              = isset( $propertyid[1] ) ? $propertyid[1] : '';
		$admin_url           = str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) );
		$button_config       = get_option( 'sharethis_button_config' );
		$button_config       = false !== $button_config && null !== $button_config ? $button_config : '';
		$share_buttons_exist = is_plugin_active( 'sharethis-share-buttons/sharethis-share-buttons.php' );

		// If sharethis share buttons are already enqueueing script don't re-enqueue it.
		if ( ! wp_script_is( 'sharethis-reaction-buttons-mua', 'enqueued' ) ) {
			wp_enqueue_script( "{$this->plugin->assets_prefix}-mua" );
		}

		// Only enqueue these scripts on share buttons plugin admin menu.
		if ( $hook_suffix === $this->hook_suffix ) {
			if ( $first_exists ) {
				update_option( 'sharethis_first_product', 'inline-reaction' );
			}

			wp_enqueue_style( "{$this->plugin->assets_prefix}-admin" );

			wp_enqueue_script( "{$this->plugin->assets_prefix}-admin" );
			wp_add_inline_script(
				"{$this->plugin->assets_prefix}-admin",
				sprintf(
					'ReactionButtons.boot( %s );',
					wp_json_encode(
						array(
							'reactionEnabled' => $reaction,
							'propertyid'      => $property_id,
							'secret'          => $secret,
							'token'           => $token,
							'buttonConfig'    => $button_config,
							'shareButtons'    => $share_buttons_exist,
							'nonce'           => wp_create_nonce( $this->plugin->meta_prefix ),
						)
					)
				)
			);
		}

		if ( '' === $property_id ) {
			wp_register_script(
				"{$this->plugin->assets_prefix}-credentials",
				$this->plugin->dir_url . 'js/set-credentials.js',
				array( 'wp-util' ),
				filemtime( "{$this->plugin->dir_path}js/set-credentials.js" ),
				false
			);

			// Only enqueue this script on the general settings page for credentials.
			wp_enqueue_script( "{$this->plugin->assets_prefix}-credentials" );
			wp_add_inline_script(
				"{$this->plugin->assets_prefix}-credentials",
				sprintf(
					'Credentials.boot( %s );',
					wp_json_encode(
						array(
							'nonce'        => wp_create_nonce( $this->plugin->meta_prefix ),
							'email'        => get_bloginfo( 'admin_email' ),
							'url'          => str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ),
							'buttonConfig' => $button_config,
						)
					)
				)
			);
		}
	}

	/**
	 * Call back for displaying the General Settings page.
	 */
	public function general_settings_display() {
		global $current_user;

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// If the property id is set then show the general settings template.
		if ( $this->is_property_id_set() ) {
			include_once "{$this->plugin->dir_path}/templates/general/general-settings.php";
		} else {
			// Get the current sites true url including sub directories.
			$admin_url   = str_replace( '/wp-admin/', '', admin_url() );
			$setup_steps = $this->get_setup_steps();
			$reactions   = $this->get_reactions();
			$languages   = $this->languages;

			$settings = filter_input_array(
				INPUT_GET,
				array(
					'b' => FILTER_SANITIZE_STRING,
					's' => FILTER_SANITIZE_STRING,
					'l' => FILTER_SANITIZE_STRING,
					'p' => FILTER_SANITIZE_STRING,
				)
			);

			$page = true === empty( $settings['s'] )
					&& true === empty( $settings['l'] )
					&& true === empty( $settings['p'] ) ? 'first' : '';

			$page = false === empty( $settings['s'] )
					&& true === empty( $page )
					&& '2' === $settings['s'] ? 'second' : $page;

			$page = false === empty( $settings['s'] )
					&& true === empty( $page )
					&& '3' === $settings['s'] ? 'third' : $page;

			$page = false === empty( $settings['l'] )
					&& true === empty( $page )
					&& 't' === $settings['l'] ? 'login' : $page;

			$page = false === empty( $settings['p'] )
					&& true === empty( $page )
					&& 't' === $settings['p'] ? 'property' : $page;

			$step_class = '';

			include_once "{$this->plugin->dir_path}/templates/general/connection-template.php";
		}
	}

	/**
	 * Call back for property id setting view.
	 */
	public function property_setting() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$credential    = get_option( 'sharethis_property_id' );
		$credential    = null !== $credential && false !== $credential ? $credential : '';
		$error_message = '' === $credential ? '<div class="st-error"><strong>' . esc_html__( 'ERROR', 'sharethis-reaction-buttons' ) . '</strong>: ' . esc_html__( 'Property ID is required.', 'sharethis-reaction-buttons' ) . '</div>' : '';

		include_once "{$this->plugin->dir_path}/templates/general/property-setting.php";
	}

	/**
	 * Call back for displaying Reaction Buttons settings page.
	 */
	public function share_button_display() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$description = $this->get_descriptions( '', 'reaction_buttons' );

		include_once "{$this->plugin->dir_path}templates/reaction-buttons/reaction-button-settings.php";
	}

	/**
	 * Define reaction button setting sections and fields.
	 *
	 * @action admin_init
	 */
	public function settings_api_init() {
		// Register sections.
		foreach ( $this->setting_sections as $index => $title ) {
			// Since the index starts at 0, let's increment it by 1.
			$i       = $index + 1;
			$section = 'reaction_button_section';

			// Add setting section.
			add_settings_section(
				$section,
				$title,
				array( $this, 'social_button_link' ),
				$this->menu_slug . '-reaction-buttons'
			);
		}

		// Register setting fields.
		foreach ( $this->setting_fields as $setting_field ) {
			register_setting( $this->menu_slug . '-reaction-buttons', $this->menu_slug . '_' . $setting_field['id_suffix'] );
			add_settings_field(
				$this->menu_slug . '_' . $setting_field['id_suffix'],
				$setting_field['description'],
				array( $this, $setting_field['callback'] ),
				$this->menu_slug . '-reaction-buttons',
				'reaction_button_section',
				$setting_field['arg']
			);
		}
	}

	/**
	 * Call back function for on / off buttons.
	 *
	 * @param string $type The setting type.
	 */
	public function config_settings( $type = 'inline-reaction' ) {
		$config_array = $this->reaction_setting_fields;

		// Display on off template for inline settings.
		foreach ( $config_array as $setting ) {
			$option       = 'sharethis_' . $setting['id_suffix'];
			$title        = isset( $setting['title'] ) ? $setting['title'] : '';
			$option_value = get_option( 'sharethis_inline-reaction_settings' );
			$default      = isset( $setting['default'] ) ? $setting['default'] : '';
			$allowed      = array(
				'li'    => array(
					'class' => array(),
				),
				'span'  => array(
					'id'    => array(),
					'class' => array(),
				),
				'input' => array(
					'id'    => array(),
					'name'  => array(),
					'type'  => array(),
					'value' => array(),
				),
			);

			// Margin control variables.
			$margin = isset( $setting['default']['margin'] ) ? $setting['default']['margin'] : false;
			$mclass = isset( $option_value[ $option . '_margin_top' ] ) && 0 !== (int) $option_value[ $option . '_margin_top' ] || isset( $option_value[ $option . '_margin_bottom' ] ) && 0 !== (int) $option_value[ $option . '_margin_bottom' ] ? 'active-margin' : '';
			$onoff  = '' !== $mclass ? __( 'On', 'sharethis-reaction-buttons' ) : __( 'Off', 'sharethis-reaction-buttons' );
			$active = array(
				'class' => $mclass,
				'onoff' => esc_html( $onoff ),
			);

			if ( isset( $option_value[ $option ] ) && false !== $option_value[ $option ] && null !== $option_value[ $option ] ) {
				$default = array(
					'true'  => '',
					'false' => '',
				);
			}

			// Display the list call back if specified.
			if ( 'onoff_cb' === $setting['callback'] ) {
				include "{$this->plugin->dir_path}/templates/reaction-buttons/onoff-buttons.php";
			} else {
				$current_omit = $this->get_omit( $setting['type'] );

				$this->list_cb( $setting['type'], $current_omit, $allowed );
			}
		}
	}

	/**
	 * Callback function for onoff buttons
	 *
	 * @param array $id The setting type.
	 */
	public function enable_cb( $id ) {
		include "{$this->plugin->dir_path}/templates/reaction-buttons/enable-buttons.php";
	}

	/**
	 * Callback function for omitting fields.
	 *
	 * @param array $type The type of list to return for exlusion.
	 * @param array $current_omit The currently omited items.
	 * @param array $allowed The allowed html that an omit item can echo.
	 */
	public function list_cb( $type, $current_omit, $allowed ) {
		include "{$this->plugin->dir_path}/templates/reaction-buttons/list.php";
	}

	/**
	 * Callback function for the shortcode and template code fields.
	 *
	 * @param string $type The type of template to pull.
	 */
	public function shortcode_template( $type ) {
		include "{$this->plugin->dir_path}/templates/reaction-buttons/shortcode-templatecode.php";
	}

	/**
	 * Callback function for the login buttons.
	 *
	 * @param string $button The specific product to link to.
	 */
	public function social_button_link( $button ) {
		$reactions = $this->get_reactions();
		$languages = $this->languages;

		$enabled = 'true' === get_option( 'sharethis_inline-reaction' ) ? 'Enabled' : 'Disabled';

		include "{$this->plugin->dir_path}/templates/reaction-buttons/button-config.php";
	}

	/**
	 * Callback function for random gif field.
	 *
	 * @access private
	 * @return string
	 */
	private function random_gif() {
		if ( ! is_wp_error( wp_safe_remote_get( 'http://api.giphy.com/v1/gifs/random?api_key=dc6zaTOxFJmzC&rating=g' ) ) ) {
			$content = wp_safe_remote_get( 'http://api.giphy.com/v1/gifs/random?api_key=dc6zaTOxFJmzC&rating=g' )['body'];

			return '<div id="random-gif-container"><img src="' . esc_url( json_decode( $content, ARRAY_N )['data']['image_url'] ) . '"/></div>';
		} else {
			return esc_html__( 'Sorry we couldn\'t show you a funny gif.  Refresh if you can\'t live without it.', 'sharethis-reaction-buttons' );
		}
	}

	/**
	 * Define setting descriptions.
	 *
	 * @param string $type Type of button.
	 * @param string $subtype Setting type.
	 *
	 * @access private
	 * @return string|void
	 */
	private function get_descriptions( $type = '', $subtype = '' ) {
		global $current_user;

		$description = '';

		switch ( $subtype ) {
			case '':
				$description  = esc_html__( 'WordPress Display Settings', 'sharethis-reaction-buttons' );
				$description .= '<span>';
				$description .= esc_html__( 'Use these settings to automatically include or restrict the display of ', 'sharethis-reaction-buttons' ) . esc_html( $type ) . esc_html__( ' Reaction Buttons on specific pages of your site.', 'sharethis-reaction-buttons' );
				$description .= '</span>';
				break;
			case 'shortcode':
				$description  = esc_html__( 'Shortcode', 'sharethis-reaction-buttons' );
				$description .= '<span>';
				$description .= esc_html__( 'Use this shortcode to deploy your reaction buttons in a widget, or WYSIWYG editor.', 'sharethis-reaction-buttons' );
				$description .= '</span>';
				break;
			case 'template':
				$description  = esc_html__( 'PHP', 'sharethis-reaction-buttons' );
				$description .= '<span>';
				$description .= esc_html__( 'Use this PHP snippet to include your reaction buttons anywhere else in your template.', 'sharethis-reaction-buttons' );
				$description .= '</span>';
				break;
			case 'property':
				$description  = esc_html__( 'Property ID', 'sharethis-reaction-buttons' );
				$description .= '<span>';
				$description .= esc_html__( 'We use this unique ID to identify your property. Copy it from your ', 'sharethis-reaction-buttons' );
				$description .= '<a class="st-support" href="https://platform.sharethis.com/settings?utm_source=sharethis-plugin&utm_medium=sharethis-plugin-page&utm_campaign=property-settings" target="_blank">';
				$description .= esc_html__( 'ShareThis platform settings', 'sharethis-reaction-buttons' );
				$description .= '</a></span>';
				break;
			case 'reaction_buttons':
				$description  = '<h1>';
				$description .= esc_html__( 'Reaction Buttons by ShareThis', 'sharethis-reaction-buttons' );
				$description .= '</h1>';
				$description .= '<h3>';
				$description .= esc_html__( 'Welcome aboard, ', 'sharethis-reaction-buttons' ) . esc_html( $current_user->display_name ) . '! ';
				$description .= esc_html__( 'Use the settings panels below for complete control over where and how reaction buttons appear on your site.', 'sharethis-reaction-buttons' );
				break;
		}

		return wp_kses_post( $description );
	}

	/**
	 * Set the property id and secret key for the user's platform account if query params are present.
	 *
	 * @action wp_ajax_set_reaction_credentials
	 */
	public function set_reaction_credentials() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		if ( ! isset( $_POST['data'], $_POST['token'] ) || '' === $_POST['data'] ) { // WPCS: input var ok.
			wp_send_json_error( 'Set credentials failed.' );
		}

		$data  = sanitize_text_field( wp_unslash( $_POST['data'] ) ); // WPCS: input var ok.
		$token = sanitize_text_field( wp_unslash( $_POST['token'] ) ); // WPCS: input var ok.

		// If both variables exist add them to a database option.
		if ( false === get_option( 'sharethis_property_id' ) ) {
			update_option( 'sharethis_property_id', $data );
			update_option( 'sharethis_token', $token );
		}
	}

	/**
	 * Helper function to determine if property ID is set.
	 *
	 * @param string $type Should empty count as false.
	 *
	 * @access private
	 * @return bool
	 */
	private function is_property_id_set( $type = '' ) {
		$property_id = get_option( 'sharethis_property_id' );

		// If the property id is set then show the general settings template.
		if ( false !== $property_id && null !== $property_id ) {
			if ( 'empty' === $type && '' === $property_id ) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * AJAX Call back to update status of buttons
	 *
	 * @action wp_ajax_update_reaction_buttons
	 */
	public function update_reaction_buttons() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		$type = sanitize_text_field( wp_unslash( $type ) );

		$onoff = filter_input( INPUT_POST, 'onoff', FILTER_SANITIZE_STRING );
		$onoff = sanitize_text_field( wp_unslash( $onoff ) );

		if ( true === empty( $type ) || true === empty( $onoff ) ) {
			wp_send_json_error( 'Update buttons failed.' );
		}

		if ( 'On' === $onoff ) {
			update_option( 'sharethis_reaction', 'true' );
		} elseif ( 'Off' === $onoff ) {
			update_option( 'sharethis_reaction', 'false' );
		}
	}

	/**
	 * AJAX Call back to update status of buttons
	 *
	 * @action wp_ajax_update_buttons
	 */
	public function update_buttons() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		if ( ! isset( $_POST['type'], $_POST['onoff'] ) ) { // phpcs:ignore input var ok.
			wp_send_json_error( 'Update buttons failed.' );
		}

		// Set option type and button value.
		$type  = 'sharethis_' . sanitize_text_field( wp_unslash( $_POST['type'] ) ); // WPCS: input var ok.
		$onoff = sanitize_text_field( wp_unslash( $_POST['onoff'] ) ); // WPCS: input var ok.

		if ( 'On' === $onoff ) {
			update_option( $type, 'true' );
		} elseif ( 'Off' === $onoff ) {
			update_option( $type, 'false' );
		}
	}

	/**
	 * AJAX Call back to set defaults when rest button is clicked.
	 *
	 * @action wp_ajax_set_reaction_default_settings
	 */
	public function set_reaction_default_settings() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		$this->set_the_defaults();
	}

	/**
	 * Helper function to set the default button options.
	 */
	private function set_the_defaults() {
		$default = array(
			'reaction_settings' => array(
				'sharethis_inline-reaction_post_top'    => 'true',
				'sharethis_inline-reaction_post_bottom' => 'false',
				'sharethis_inline-reaction_page_top'    => 'false',
				'sharethis_inline-reaction_page_bottom' => 'false',
				'sharethis_excerpt'                     => 'false',
				'sharethis_inline-reaction_post_top_margin_top' => 0,
				'sharethis_inline-reaction_post_top_margin_bottom' => 0,
				'sharethis_inline-reaction_post_bottom_margin_top' => 0,
				'sharethis_inline-reaction_post_bottom_margin_bottom' => 0,
				'sharethis_inline-reaction_page_top_margin_top' => 0,
				'sharethis_inline-reaction_page_top_margin_bottom' => 0,
				'sharethis_inline-reaction_page_bottom_margin_top' => 0,
				'sharethis_inline-reaction_page_bottom_margin_bottom' => 0,
				'sharethis_excerpt_margin_top'          => 0,
				'sharethis_excerpt_margin_bottom'       => 0,
			),
		);

		update_option( 'sharethis_inline-reaction_settings', $default['reaction_settings'] );
	}

	/**
	 * Display custom admin notice.
	 *
	 * @action admin_notices
	 */
	public function connection_made_admin_notice() {
		$screen = get_current_screen();
		if ( 'sharethis_page_sharethis-reaction-buttons' === $screen->base ) {

			$reset = filter_input( INPUT_GET, 'reset', FILTER_SANITIZE_STRING );
			$reset = sanitize_text_field( wp_unslash( $reset ) );

			if ( false === empty( $rest ) ) {
				?>
					<div class="notice notice-success is-dismissible">
						<p>
							<?php
							printf(
								/* translators: The type of button. */
								esc_html__(
									'Successfully reset your reaction button position display options!',
									'sharethis-reaction-buttons'
								),
								esc_html( $reset )
							);
							?>
						</p>
					</div>
				<?php
			};
		}
	}

	/**
	 * Runs only when the plugin is activated.
	 */
	public function st_activation_hook() {
		// Create transient data.
		set_transient( 'st-reaction-activation', true, 5 );
		set_transient( 'st-reaction-connection', true, 360 );

		// Set the default optons.
		$this->set_the_defaults();
	}

	/**
	 * Admin Notice on Activation.
	 *
	 * @action admin_notices
	 */
	public function activation_inform_notice() {
		$screen  = get_current_screen();
		$product = get_option( 'sharethis_first_product' );
		$product = null !== $product && false !== $product ? ucfirst( $product ) : 'your';
		$gen_url = '<a href="' . esc_url( admin_url( 'admin.php?page=sharethis-share-buttons&nft' ) ) . '">configuration</a>';

		if ( ! $this->is_property_id_set() ) {
			$gen_url = '<a href="' . esc_url( admin_url( 'admin.php?page=sharethis-share-buttons' ) ) . '">configuration</a>';
		}

		// Check transient, if available display notice.
		if ( get_transient( 'st-reaction-activation' ) ) {
			?>
			<div class="updated notice is-dismissible">
				<p>
					<?php
					// translators: The general settings url.
					printf( esc_html__( 'Your ShareThis Reaction Button plugin requires %1$s', 'sharethis-share-button' ), wp_kses_post( $gen_url ) );
					?>
					.
				</p>
			</div>
			<?php
			// Delete transient, only display this notice once.
			delete_transient( 'st-reaction-activation' );
		}

		$nft = filter_input( INPUT_GET, 'nft', FILTER_UNSAFE_RAW );
		$nft = sanitize_text_field( wp_unslash( $nft ) );

		if ( 'sharethis_page_sharethis-reaction-buttons' === $screen->base
			&& get_transient( 'st-reaction-connection' )
			&& true === empty( $nft )
		) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: The product type. */
						esc_html__(
							'Congrats! You’ve activated Reaction Buttons. Sit tight, they’ll appear on your site in just a few minutes!',
							'sharethis-reaction-buttons'
						),
						esc_html( $product )
					);
					?>
				</p>
			</div>
			<?php
			delete_transient( 'st-reaction-connection' );
		}
	}

	/**
	 * Remove all database information when plugin is deactivated.
	 */
	public function st_deactivation_hook() {
		foreach ( wp_load_alloptions() as $option => $value ) {
			if ( strpos( $option, 'sharethis_' ) === 0 ) {
				delete_option( $option );
			}
		}
	}

	/**
	 * Register the button widget.
	 *
	 * @action widgets_init
	 */
	public function register_widgets() {
		register_widget( $this->button_widget );
	}

	/**
	 * Return the set up steps.
	 */
	private function get_setup_steps() {
		$steps = array(
			1 => esc_html__( 'Design your reactions', 'sharethis-reaction-buttons' ),
			2 => esc_html__( 'Register with ShareThis', 'sharethis-reaction-buttons' ),
			3 => esc_html__( 'Configure WordPress Settings', 'sharethis-reaction-buttons' ),
		);

		return $steps;
	}

	/**
	 * Return network array with info.
	 */
	private function get_reactions() {
		$reactions = array(
			'slight_smile' => "{$this->plugin->dir_url}assets/1f642.svg",
			'heart_eyes'   => "{$this->plugin->dir_url}assets/1f60d.svg",
			'laughing'     => "{$this->plugin->dir_url}assets/1f606.svg",
			'astonished'   => "{$this->plugin->dir_url}assets/1f632.svg",
			'sob'          => "{$this->plugin->dir_url}assets/1f62d.svg",
			'rage'         => "{$this->plugin->dir_url}assets/1f621.svg",
		);

		return $reactions;
	}

	/**
	 * Set the languages array.
	 */
	private function set_languages() {
		$this->languages = array(
			'English'    => 'en',
			'German'     => 'de',
			'Spanish'    => 'es',
			'French'     => 'fr',
			'Italian'    => 'it',
			'Japanese'   => 'ja',
			'Korean'     => 'ko',
			'Portuguese' => 'pt',
			'Russian'    => 'ru',
			'Chinese'    => 'zh',
		);
	}

	/**
	 * AJAX Call back to save the set up button config for setup.
	 *
	 * @action wp_ajax_set_reaction_button_config
	 */
	public function set_reaction_button_config() {
		check_ajax_referer( $this->plugin->meta_prefix, 'nonce' );

		if ( ! isset( $_POST['config'] ) || '' === $_POST['config'] ) { // WPCS: input var ok.
			wp_send_json_error( 'Reaction Config Set Failed' );
		}

		$post = filter_input_array(
			INPUT_POST,
			array(
				'button' => FILTER_SANITIZE_STRING,
				'config' => array(
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
				'first'  => FILTER_SANITIZE_STRING,
				'nonce'  => FILTER_SANITIZE_STRING,
				'type'   => FILTER_SANITIZE_STRING,
			)
		);

		$first     = ( true === isset( $post['first'] ) && 'upgrade' !== $post['first'] );
		$type      = ( false === empty( $post['type'] ) );
		$button    = sanitize_text_field( wp_unslash( $post['button'] ) );
		$config    = $post['config'];
		$reactions = array_map( 'sanitize_text_field', wp_unslash( $config['reactions'] ) );

		// If user doesn't have a sharethis account already.
		if ( false !== $type ) {
			$config = 'platform' !== $button ? json_decode( str_replace( '\\', '', $config ), true ) : $config;
		}

		if ( false === $first ) {
			$current_config                                 = get_option( 'sharethis_button_config' );
			$current_config                                 = false !== $current_config && null !== $current_config ? $current_config : array();
			$current_config['inline-reaction']              = $post['config']; // WPCS: input var ok.
			$current_config['inline-reaction']['reactions'] = $reactions;
			$config = $current_config;
		} else {
			$set_config['inline-reaction'] = $config;
			$config                        = $set_config;
		}

		// Make sure bool is "true" or "false".
		if ( isset( $config['inline-reaction'] ) ) {
			$config['inline-reaction']['enabled'] = true === $config['inline-reaction']['enabled'] || '1' === $config['inline-reaction']['enabled'] || 'true' === $config['inline-reaction']['enabled'] ? 'true' : 'false';
		}

		update_option( 'sharethis_button_config', $config );

		if ( $first && 'platform' !== $button ) {
			update_option( 'sharethis_first_product', 'inline-reaction' );
		}
	}
}
