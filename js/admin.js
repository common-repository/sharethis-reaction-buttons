/**
 * ShareThis Reaction Buttons
 *
 * @package ShareThisReactionButtons
 */

/* exported ReactionButtons */
var ReactionButtons = ( function( $, wp ) {
	'use strict';

	return {
		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Boot plugin.
		 */
		boot: function( data ) {
			this.data = data;

			$( document ).ready( function() {
				this.init();
			}.bind( this ) );
		},

		/**
		 * Initialize plugin.
		 */
		init: function() {
			this.$container = $( '.sharethis-wrap' );

			// Get and set current accounts platform configurations to global.
			this.$config = this.getConfig();

			this.listen();
			this.createReset();

			// Check if platform has changed its button config.
			this.checkIfChanged();

			// Check for non WP Share Buttons.
			this.shareButtonsExists();
		},

		/**
		 * Change font color of selected buttons.
		 * Also decide whether to update WP enable / disable status or just show / hide menu options.
		 */
		markSelected: function() {
			var iConfigSet = null !== this.$config && undefined !== this.$config[ 'inline-reaction-buttons' ],
				iturnOn,
				iturnOff,
				inlineReactionEnable;

			// Check if api call is successful and if inline buttons are enabled.  Use WP data base if not.
			if ( iConfigSet ) {
				inlineReactionEnable = this.$config[ 'inline-reaction-buttons' ][ 'enabled' ]; // Dot notation cannot be used due to dashes in name.
			} else {
				if ( undefined !== this.data.buttonConfig[ 'inline-reaction' ] ) {
					inlineReactionEnable = this.data.buttonConfig[ 'inline-reaction' ][ 'enabled' ];
				}
			}

			// Decide whether to update WP database or just show / hide menu options.
			if ( ! iConfigSet || (
					undefined !== this.data.buttonConfig[ 'inline-reaction' ] && this.data.buttonConfig[ 'inline-reaction' ][ 'enabled' ] === this.$config[ 'inline-reaction-buttons' ][ 'enabled' ] ) ) { // Dot notation cannot be used due to dashes in name.
				iturnOn = 'show';
				iturnOff = 'hide';
			} else {
				iturnOn = 'On';
				iturnOff = 'Off';
			}

			// If enabled show button configuration.
			if ( 'true' === inlineReactionEnable || true === inlineReactionEnable ) {
				this.updateButtons( 'inline-reaction', iturnOn );
				$( '#inline-reaction label.share-on input' ).prop( 'checked', true );
			} else {
				this.updateButtons( 'inline-reaction', iturnOff );
				$( '#inline-reaction label.share-off input' ).prop( 'checked', true );
			}

			// Change button font color based on status.
			$( '.share-on input:checked, .share-off input:checked' ).closest( 'label' ).find( 'span.label-text' ).css( 'color', '#ffffff' );
		},

		/**
		 * Check the platform has updated the button configs.
		 */
		checkIfChanged: function() {
			if (undefined === typeof this.$config
				|| null === this.$config
			) {
				return;
			}

			var iTs = this.$config[ 'inline-reaction-buttons' ],
				myITs = this.data.buttonConfig['inline-reaction'],
				theConfig;

			// Set variables if array exists.
			if ( undefined !== iTs ) {
				iTs = iTs[ 'updated_at' ];

				if ( undefined !== iTs ) {
					iTs = iTs.toString();
				}
			}

			if ( undefined !== myITs ) {
				myITs = myITs[ 'updated_at' ];
			}

			// If platform has updated the button config or platform configs are broken use WP config.
			if ( iTs !== myITs || undefined === this.data.buttonConfig['inline-reaction'] ) {
				this.setConfigFields( 'inline-reaction', this.$config[ 'inline-reaction-buttons' ], 'platform' );
			} else {
				this.loadPreview( 'initial', '' );
			}
		},

		/**
		 * Show button configuration.
		 *
		 * @param button
		 * @param type
		 * @param event
		 */
		updateButtons: function( button, type ) {
			const pTypes = [ 'show', 'On', '►', 'true' ];

			// If not one of the show types then hide.
			const turnOnOff = -1 !== $.inArray( type, pTypes ) ? 'On' : 'Off';

			// Set option value for button.
			wp.ajax.post( 'update_buttons', {
				type: button.toLowerCase(),
				onoff: turnOnOff,
				nonce: this.data.nonce
			} ).always( function() {
			} );
		},

		/**
		 * Copy text to clipboard
		 *
		 * @param copiedText
		 */
		copyText: function( copiedText ) {
			copiedText.select();
			document.execCommand( 'copy' );
		},

		/**
		 * Add the reset buttons to share buttons menu
		 */
		createReset: function() {
			var button = '<input type="button" id="reset" class="button button-primary" value="Reset">',
				newButtons = $( '.sharethis-wrap form .submit' ).append( button ).clone(),
				browserWidth = $( '.sharethis-wrap form' ).outerWidth();

			$( '.inline-reaction-platform .button-alignment' ).css( 'width', browserWidth + 'px' );
		},

		/**
		 * Set to default settings when reset is clicked.
		 *
		 * @param type
		 */
		setDefaults: function( type ) {
			wp.ajax.post( 'set_reaction_default_settings', {
				type: type,
				nonce: this.data.nonce
			} ).always( function() {
				if ( 'both' !== type ) {
					location.href = location.pathname + '?page=sharethis-share-buttons&reset=' + type;
				} else {
					location.reload();
				}
			} );
		},

		/**
		 * Get current config data from user.
		 */
		getConfig: function() {
			var result = null,
				callExtra = 'secret=' + this.data.secret;

			if ( 'undefined' === this.data.secret ) {
				callExtra = 'token=' + this.data.token;
			}

			$.ajax( {
				url: 'https://platform-api.sharethis.com/v1.0/property/?' + callExtra + '&id=' + this.data.propertyid,
				method: 'GET',
				async: false,
				contentType: 'application/json; charset=utf-8',
				success: function( results ) {
					result = results;
				}
			} );

			return result;
		},

		/**
		 * Activate specified option margin controls and show/hide
		 *
		 * @param marginButton
		 * @param status
		 */
		activateMargin: function( marginButton, status ) {
			if ( ! status ) {
				$( marginButton ).addClass( 'active-margin' ).find( 'span.margin-on-off' ).html( 'On' );
				$( marginButton ).siblings( 'div.margin-input-fields' ).show().find( 'input' ).prop( 'disabled', false );
			} else {
				$( marginButton ).removeClass( 'active-margin' ).find( 'span.margin-on-off' ).html( 'Off' );
				$( marginButton ).siblings( 'div.margin-input-fields' ).hide().find( 'input' ).prop( 'disabled', true );
			}
		},

		/**
		 * Set the settings fields for the button configurations.
		 *
		 * @param button
		 */
		setConfigFields: function( button, config, type ) {
			var size,
				button = 'inline-reaction';

			if ( '' === config ) {
				config = this.data.buttonConfig[ button ];
			}

			if ( undefined === config ) {
				return;
			}

			$( '.reaction-buttons .reaction-button' ).each( function() {
				$( this ).attr( 'data-selected', false );
			} );

			// Reactions.
			$.each( config[ 'reactions' ], function( index, value ) {
				$( '.reaction-buttons .reaction-button[data-reaction="' + value + '"]' ).attr( 'data-selected', 'true' );
			} );

			// Alignment.
			$( '.button-alignment .alignment-button[data-selected="true"]' ).attr( 'data-selected', 'false' );
			$( '.button-alignment .alignment-button[data-alignment="' + config[ 'alignment' ] + '"]' ).attr( 'data-selected', 'true' );

			// Language.
			$( '#st-language option[value="' + config[ 'language' ] + '"]' ).prop( 'selected', true );

			if ( 'platform' === type ) {
				this.loadPreview( 'initial-platform', button );
			}
		},

		/**
		 * Check if share buttons are active and plugin doesn't exist.
		 */
		shareButtonsExists: function() {
			var needPlugin = ( ( undefined !== this.$config[ 'inline-share-buttons' ] || undefined !== this.$config[ 'sticky-share-buttons' ] ) && false === this.data.shareButtons );

			if ( needPlugin ) {
				this.$container.before(
					'<div class="notice notice-error is-dismissible">' +
					'<p>' +
					'It appears you have share buttons enabled in your account, but do not have the ' +
					'<strong>' +
					'ShareThis Share Buttons' +
					'</strong>' +
					' WordPress plugin installed or activated!' +
					'</p>' +
					'<p>' +
					'Please go here: ' +
					'<a href="https://wordpress.org/plugins/sharethis-share-buttons/" target="_blank">' +
					'https://wordpress.org/plugins/sharethis-share-buttons/' +
					'</a>' +
					' to download our plugin and utilize our Share Buttons with the power of WordPress!' +
					'</p>' +
					'</div>'
				);
			}
		},

		/**
		 * Initiate listeners.
		 */
		listen: function() {
			var self = this,
				timer = '';

			// Enable tool submit.
			const enableButtons = document.querySelectorAll( '.enable-tool' );

			if ( enableButtons ) {
				enableButtons.forEach( enableButton => {
					enableButton.addEventListener( 'click', ( e ) => {
						e.stopPropagation();
						e.preventDefault();

						self.updateButtons( enableButton.dataset.button, 'On' );
						self.loadPreview( 'turnon', enableButton.dataset.button );
					} );
				} );
			}

			// Disable tool submit.
			const disableButtons = document.querySelectorAll( '.disable-tool' );

			if ( disableButtons ) {
				disableButtons.forEach( disableButton => {
					disableButton.addEventListener( 'click', ( e ) => {
						e.stopPropagation();
						e.preventDefault();

						self.updateButtons( disableButton.dataset.button, 'Off' );
						self.loadPreview( 'turnoff', disableButton.dataset.button );
					} );
				} );
			}

			// Copy text from read only input fields.
			this.$container.on( 'click', '#copy-shortcode, #copy-template', function() {
				self.copyText( $( this ).closest( 'div' ).find( 'input' ) );
			} );

			// Click reset buttons.
			this.$container.on( 'click', 'p.submit #reset', function() {
				var type = $( this )
					.closest( 'p.submit' )
					.prev()
					.find( '.enable-buttons' )
					.attr( 'id' );

				self.setDefaults( type );
			} );

			// Toggle margin control buttons.
			this.$container.on( 'click', 'button.margin-control-button', function() {
				var status = $( this ).hasClass( 'active-margin' );

				self.activateMargin( this, status );
			} );

			// All levers.
			this.$container.on( 'click', '.item div.switch', function() {
				self.loadPreview( '', 'inline-reactions' );
			} );

			// Button alignment.
			this.$container.on( 'click', '.button-alignment .alignment-button', function() {
				$( '.button-alignment .alignment-button[data-selected="true"]' ).attr( 'data-selected', 'false' );
				$( this ).attr( 'data-selected', 'true' );

				self.loadPreview( '' );
			} );

			// Select or deselect a network.
			this.$container.on( 'click', '.reaction-buttons .reaction-button', function() {
				var selection = $( this ).attr( 'data-selected' ),
					reaction = $( this ).attr( 'data-reaction' );

				if ( 'true' === selection ) {
					$( this ).attr( 'data-selected', 'false' );
					$( '.sharethis-selected-reactions > div > div[data-reaction="' + reaction + '"]' ).remove();
				} else {
					$( this ).attr( 'data-selected', 'true' );
					$( '.sharethis-selected-reactions > div' ).append( '<div class="st-btn" data-reaction="' + reaction + '" style="display: inline-block;"></div>' );
				}

				self.loadPreview( '' );
			} );

			// Submit configurations.
			$( '.sharethis-wrap form' ).submit( function() {
				self.loadPreview( 'submit', 'inline-reaction' );
			} );

			// When reacting with preview.
			this.$container.on( 'click', '.st-btn', function() {
				var timer = '';

				clearTimeout( timer );

				timer = setTimeout( function() {
					self.loadPreview( '' );
				}.bind( this ), 1000 );
			} );

			// Change language.
			this.$container.on( 'change', '#st-language', function() {
				self.loadPreview( '' );
			} );
		},

		/**
		 * Load preview buttons.
		 *
		 * @param type
		 * @param button
		 */
		loadPreview: function( type, button ) {
			if ( 'initial' === type ) {
				this.setConfigFields( 'inline-reaction', '', '' );
			}

			var bAlignment = $( '.button-alignment .alignment-button[data-selected="true"]' ).attr( 'data-alignment' ),
				language = $( '#st-language option:selected' ).val(),
				self = this,
				reactions,
				config,
				beforeConfig,
				theFirst = false,
				wpConfig,
				timer = '',
				upConfig,
				theData,
				enabled = false,
				button = 'inline-reaction';

			if ( 'initial' === type && undefined !== this.data.buttonConfig['inline-reaction'] ) {
				reactions = this.data.buttonConfig['inline-reaction']['reactions'];
			} else {
				reactions = [];

				$( '.sharethis-inline-reaction-buttons .st-btn' ).each( function( index ) {
					reactions[ index ] = $( this ).attr( 'data-reaction' );
				} );
			}

			if ( 'sync-platform' === type && undefined !== this.$config[ 'inline-reaction-buttons' ] ) {
				reactions = this.$config[ 'inline-reaction-buttons' ][ 'reactions' ];
			}

			// If newly turned on use selected reactions.
			if ( 'turnon' === type || undefined !== this.data.buttonConfig['inline-reaction'] && undefined === this.data.buttonConfig['inline-reaction']['reactions'] ) {
				reactions = [];

				$( '.inline-reaction-platform .reaction-buttons .reaction-button[data-selected="true"]' ).each( function( index ) {
					reactions[ index ] = $( this ).attr( 'data-reaction' );
				} );
			}

			if ( 'submit' === type ) {
				reactions = [];

				$( '.sharethis-inline-reaction-buttons .st-btn' ).each( function( index ) {
					reactions[ index ] = $( this ).attr( 'data-reaction' );
				} );
			}

			// If submited or turned on make sure enabled setting is set properly.
			if ( undefined !== this.$config[ 'inline-reaction-buttons' ] && undefined !== this.$config[ 'inline-reaction-buttons' ][ 'enabled' ] ) {
				enabled = 'true' === this.$config[ 'inline-reaction-buttons' ][ 'enabled' ] ||
						  true === this.$config[ 'inline-reaction-buttons' ][ 'enabled' ] ||
						  true === this.$tempEnable;
			} else {
				enabled = false;
			}

			config = {
				alignment: bAlignment,
				language: language,
				enabled: enabled,
				reactions: reactions
			};

			// Set config for initial post.
			beforeConfig = config;

			if ( 'submit' === type || 'initial-platform' === type || 'turnon' === type || 'turnoff' === type ) {

				// If submiting WP keep platform timestamp if exists.
				if ( 'submit' === type && undefined !== this.$config[ 'inline-reaction-buttons' ] && undefined !== this.$config[ 'inline-reaction-buttons' ][ 'updated_at' ] ) {
					config[ 'updated_at' ] = this.$config[ 'inline-reaction-buttons' ][ 'updated_at' ];
				}

				// If platform different from WP.
				if ( 'initial-platform' === type ) {
					config = this.$config[ 'inline-reaction-buttons' ];

					if ( undefined === this.data.buttonConfig['inline-reaction'] || true === this.data.buttonConfig['inline-reaction'] ) {
						theFirst = 'upgrade';
					}
				}

				// If first load ever.
				if ( 'initial-platform' === type && undefined !== this.data.buttonConfig['inline-reaction'] && undefined === this.data.buttonConfig['inline-reaction']['updated_at'] && undefined !== this.$config[ 'inline-reaction-buttons' ][ 'updated_at' ] ) {
					config = beforeConfig;
					config[ 'updated_at' ] = this.$config[ 'inline-reaction-buttons' ][ 'updated_at' ];
					config[ 'reactions' ] = this.data.buttonConfig['inline-reaction'][ 'reactions' ];
				}

				if ( 'turnon' === type ) {
					config[ 'enabled' ] = true;
					config[ 'reactions' ] = [ 'slight_smile', 'heart_eyes', 'laughing', 'astonished', 'sob', 'rage' ];

					$.each( config[ 'reactions' ], function( index, value ) {
						$( '.inline-reaction-list .reaction-button[data-reaction="' + value + '"]' ).attr( 'data-selected', 'true' );
					} );

					// Set temp enable to true.
					this.$tempEnable = true;
				}

				if ( 'turnoff' === type ) {
					config.enabled = false;

					// Set temp enable to false.
					this.$tempEnable = false;
				}

				if ( 'upgrade' === theFirst ) {
					upConfig = {
						'inline-reaction': this.$config[ 'inline-reaction-buttons' ]
					};

					wp.ajax.post( 'set_reaction_button_config', {
						button: 'platform',
						config: upConfig,
						first: theFirst,
						type: 'login',
						nonce: this.data.nonce
					} ).always( function( results ) {
						location.reload();
					}.bind( this ) );
				} else {
					wp.ajax.post( 'set_reaction_button_config', {
						button: button,
						config: config,
						first: false,
						nonce: this.data.nonce
					} );

					if ( 'initial-platform' !== type || (
							undefined !== this.data.buttonConfig['inline-reaction'] && undefined === this.data.buttonConfig['inline-reaction']['updated_at']
						) ) {
						config.enabled = 'true' === config.enabled || true === config.enabled;

						delete config[ 'container' ];
						delete config[ 'id' ];
						delete config[ 'has_spacing' ];
						delete config[ 'show_mobile_buttons' ];

						theData = JSON.stringify( {
							'secret': this.data.secret,
							'id': this.data.propertyid,
							'product': 'inline-reaction-buttons',
							'config': config
						} );

						// Send new button status value.
						$.ajax( {
							url: 'https://platform-api.sharethis.com/v1.0/property/product',
							method: 'POST',
							async: false,
							contentType: 'application/json; charset=utf-8',
							data: theData,
							success: function() {
								if ( 'turnon' === type || 'turnoff' === type ) {
									location.reload();
								}
							}
						} );
					}
				}
			}

			$( '.st-inline-reaction-buttons' ).html( '' );

			config['enabled'] = true;

			window.__sharethis__.href = 'https://www.sharethis.com/';
			__sharethis__.storage.set( 'st_reaction_' + __sharethis__.href, null );
			window.__sharethis__.load( 'inline-reaction-buttons', config );
			$( '.sharethis-inline-reaction-buttons' ).removeClass( 'st-reacted' );

			$( '.sharethis-selected-reactions > div' ).sortable( {
				stop: function( event, ui ) {
					self.loadPreview( '' );
				}
			} );
		}
	};
} )( window.jQuery, window.wp );
