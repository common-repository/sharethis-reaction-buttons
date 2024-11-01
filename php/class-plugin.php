<?php
/**
 * Bootstraps the Sharethis Reaction Buttons plugin.
 *
 * @package SharethisReactionButtons
 */

namespace SharethisReactionButtons;

/**
 * Main plugin bootstrap file.
 */
class Plugin extends Plugin_Base {

	/**
	 * Plugin assets prefix.
	 *
	 * @var string Lowercased dashed prefix.
	 */
	public $assets_prefix;

	/**
	 * Plugin meta prefix.
	 *
	 * @var string Lowercased underscored prefix.
	 */
	public $meta_prefix;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Global.
		$button_widget = new Button_Widget( $this );

		// Initiate classes.
		$classes = array(
			$button_widget,
			new Reaction_Buttons( $this, $button_widget ),
			new Minute_Control( $this ),
		);

		// Add classes doc hooks.
		foreach ( $classes as $instance ) {
			$this->add_doc_hooks( $instance );
		}

		// Define some prefixes to use througout the plugin.
		$this->assets_prefix = strtolower( preg_replace( '/\B([A-Z])/', '-$1', __NAMESPACE__ ) );
		$this->meta_prefix   = strtolower( preg_replace( '/\B([A-Z])/', '_$1', __NAMESPACE__ ) );
	}

	/**
	 * Register MU Script
	 *
	 * @action wp_enqueue_scripts
	 */
	public function register_assets() {
		$propertyid = get_option( 'sharethis_property_id' );
		$propertyid = false !== $propertyid && null !== $propertyid ? explode( '-', $propertyid, 2 ) : array();
		$first_prod = get_option( 'sharethis_first_product' );
		$first_prod = false !== $first_prod && null !== $first_prod ? $first_prod : '';

		if ( in_array( $first_prod, array( 'inline', 'sticky' ), true ) ) {
			$first_prod = $first_prod . '-share';
		}

		$sb_active = in_array( 'sharethis-share-buttons/sharethis-share-buttons.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );

		if ( false === $sb_active && true === is_array( $propertyid ) && array() !== $propertyid ) {
			wp_register_script(
				"{$this->assets_prefix}-mu",
				"//platform-api.sharethis.com/js/sharethis.js#property={$propertyid[0]}&product={$first_prod}-buttons&source=sharethis-reaction-buttons-wordpress",
				null,
				SHARETHIS_REACTION_BUTTONS_VERSION,
				false
			);
		}
	}

	/**
	 * Register admin scripts/styles.
	 *
	 * @action admin_enqueue_scripts
	 */
	public function register_admin_assets() {
		// Check if the ShareThis script is already enqueued from another plugin.
		if ( false === wp_script_is( 'sharethis-reaction-buttons-mua', 'registered' ) ) {
			wp_register_script(
				"{$this->assets_prefix}-mua",
				'//platform-api.sharethis.com/js/sharethis.js?product=inline-reaction-buttons',
				null,
				SHARETHIS_REACTION_BUTTONS_VERSION,
				false
			);
			wp_register_script(
				"{$this->assets_prefix}-admin",
				"{$this->dir_url}js/admin.js",
				array(
					'jquery',
					'jquery-ui-sortable',
					'wp-util',
				),
				filemtime( "{$this->dir_path}js/admin.js" ),
				false
			);
			wp_register_script(
				"{$this->assets_prefix}-meta-box",
				"{$this->dir_url}js/meta-box.js",
				array(
					'jquery',
					'wp-util',
				),
				filemtime( "{$this->dir_path}js/meta-box.js" ),
				false
			);
			wp_register_style(
				"{$this->assets_prefix}-admin",
				"{$this->dir_url}css/admin.css",
				false,
				filemtime( "{$this->dir_path}css/admin.css" )
			);
			wp_register_style(
				"{$this->assets_prefix}-meta-box",
				"{$this->dir_url}css/meta-box.css",
				false,
				filemtime( "{$this->dir_path}css/meta-box.css" )
			);
		}
	}
}
