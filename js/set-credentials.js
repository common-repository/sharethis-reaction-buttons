/**
 * Credentials
 *
 * @package ShareThisReactionButtons
 */

/* exported Credentials */
var Credentials = ( function( $, wp ) {
	'use strict';

	return {
		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Boot plugin.
		 *
		 * @param data
		 */
		boot: function( data ) {
			this.data = data;

			$( document ).ready(
				function() {
					this.init();
				}.bind( this )
			);
		},

		/**
		 * Initialize plugin.
		 */
		init: function() {
			const self = this;

			// Set default WP config.
			wp.ajax.post(
				'set_reaction_default_settings',
				{
					type: 'both',
					nonce: self.data.nonce
				}
			).always(
				function( link ) {
					self.registerAccount( self.data.email, Math.random() + '1_stsb_PW!' );
				}.bind( self )
			);
		},

		/**
		 * Send hash data to credential setting.
		 *
		 * @param secret
		 * @param propertyid
		 * @param token
		 * @param type
		 */

		setCredentials: function( secret, propertyid, token ) {
			var propSecret = propertyid + '-' + secret;
			const self     = this;

			// If hash exists send it to credential setting.
			wp.ajax.post(
				'set_reaction_credentials',
				{
					data: propSecret,
					token: token,
					nonce: this.data.nonce
				}
			).always(
				function( link ) {
					// Set default product configs.
					const config = {
						alignment: 'center',
						enabled: false,
						reactions: [ 'slight_smile', 'heart_eyes', 'laughing', 'astonished', 'sob', 'rage' ],
						language: 'en',
					};

					let theData = {
						'token' : token,
						'id': propertyid,
						'product': 'inline-reaction-buttons',
						'config': config
					};

					theData = JSON.stringify( theData );

					// Send new button status value.
					$.ajax(
						{
							url: 'https://platform-api.sharethis.com/v1.0/property/product',
							method: 'POST',
							async: false,
							contentType: 'application/json; charset=utf-8',
							data: theData,
							success: function () {
								wp.ajax.post(
									'set_reaction_button_config',
									{
										button: 'reaction',
										config: config,
										first: true,
										nonce: self.data.nonce
									}
								)
							}
						}
					);
				}.bind( this )
			);
		},

		/**
		 * Register new account.
		 *
		 * @param email
		 * @param pw
		 */
		registerAccount: function( email, pw ) {
			var result        = null,
				self          = this,
				url           = this.data.url,
				randomNumber  = Math.floor(
					(
					Math.random() * 10000000000000000
					) + 1
				),
				randomNumber2 = Math.floor(
					(
					Math.random() * 10000000000000000
					) + 1
				),
				theData       = JSON.stringify(
					{
						email: randomNumber + '@' + randomNumber2 + '.com',
						password: pw,
						custom: {
							onboarding_product: 'inline-reaction-buttons',
							onboarding_domain: url,
							is_wordpress: true,
							wordpress_email: email,
						}
					}
				);

			$.ajax(
				{
					url: 'https://sso.sharethis.com/register',
					method: 'POST',
					async: false,
					contentType: 'application/json; charset=utf-8',
					data: theData,
					success: function( results ) {
						result = results;

						// Create property.
						self.createProperty( result, url, '' );
					},
				}
			);
		},

		/**
		 * Create property for new account.
		 *
		 * @param accountInfo
		 * @param url
		 */
		createProperty: function( accountInfo, url, type ) {
			var result = null,
				self   = this,
				token  = accountInfo.token,
				theData;

			if ( 'string' === typeof accountInfo ) {
				token = accountInfo;
			}

			theData = JSON.stringify(
				{
					token: token,
					product: 'inline-reaction-buttons',
					domain: url,
					is_wordpress: true
				}
			);

			$.ajax(
				{
					url: 'https://platform-api.sharethis.com/v1.0/property',
					method: 'POST',
					async: false,
					contentType: 'application/json; charset=utf-8',
					data: theData,
					success: function( results ) {
						result = results;
						self.setCredentials( result.secret, result._id, token, type );
					}
				}
			);
		}
	};
} )( window.jQuery, window.wp );
