/* global _satispressApiKeysData, _satispressApiKeySettings, wp */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var app = {},
		data     = _satispressApiKeysData,
		settings = _satispressApiKeySettings;

	_.extend( app, { collection: {}, controller: {}, model: {}, view: {} } );

	app.controller.Manager = Backbone.Model.extend({
		defaults: {
			apiKeys: null
		},

		createApiKey: function( name, user_id ) {
			var self = this;

			return wp.ajax.post( 'satispress_create_api_key', {
				name: name,
				user: user_id,
				nonce: settings.createApiKeyNonce
			}).done(function( response ) {
				self.get( 'apiKeys' ).add( response, { merge: true });
			});
		},

		deleteApiKey: function( model ) {
			return wp.ajax.post( 'satispress_delete_api_key', {
				token: model.get( 'token' ),
				nonce: settings.deleteApiKeyNonce
			}).done(function( response ) {
				// Avoid syncing to the server by triggering an event instead of
				// calling destroy() directly on the model.
				model.trigger( 'destroy', model );
			});
		}
	});

	app.model.ApiKey = Backbone.Model.extend({
		idAttribute: 'token',

		defaults: {
			created: '',
			last_used: '',
			name: '',
			token: '',
			user: null,
			user_edit_link: '',
			user_login: ''
		}
	});

	app.collection.ApiKeys = Backbone.Collection.extend({
		model: app.model.ApiKey,
	});

	app.view.ApiKeysTable = Backbone.View.extend({
		initialBody: null,
		template: wp.template( 'satispress-api-key-table' ),

		initialize: function( options ) {
			this.collection = options.collection;
			this.controller = options.controller;

			this.listenTo( this.collection, 'add remove reset', this.render );
		},

		render: function() {
			this.$el.html( this.template() );

			// Save a reference to the initial state.
			if ( ! this.initialBody ) {
				this.initialBody = this.$( 'tbody' ).html();
			}

			this.$tbody = this.$( 'tbody' ).empty();

			if ( this.collection.length ) {
				this.collection.each( this.addRow, this );
			} else {
				this.$tbody.html( this.initialBody );
			}

			new app.view.CreateApiKeyForm({
				el: this.$( '.satispress-create-api-key-form' ),
				controller: this.controller
			}).render();

			return this;
		},

		addRow: function( model ) {
			const row = new app.view.ApiKeysTableRow({
				controller: this.controller,
				model: model
			});

			this.$tbody.append( row.render().el );
		}
	});

	app.view.ApiKeysTableRow = wp.Backbone.View.extend({
		tagName: 'tr',
		template: wp.template( 'satispress-api-key-table-row' ),

		events: {
			'click .js-revoke': 'revoke',
			'click input': 'selectField'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.model = options.model;

			this.listenTo( this.model, 'change', this.render );
			this.listenTo( this.model, 'destroy', this.remove );
		},

		render: function() {
			var data = this.model.toJSON();
			data.last_used = data.last_used || '—';
			this.$el.html( this.template( data ) );

			return this;
		},

		remove: function() {
			this.$el.remove();
		},

		revoke: function( e ) {
			e.preventDefault();

			if ( window.confirm( settings.l10n.aysDeleteApiKey ) ) {
				this.controller.deleteApiKey( this.model );
			}
		},

		selectField: function( e ) {
			e.target.select();
		}
	});

	app.view.CreateApiKeyForm = wp.Backbone.View.extend({
		events: {
			'click button': 'createApiKey',
			'input input': 'toggleButtonState',
			'keydown input': 'routeKeyPress'
		},

		initialize: function( options ) {
			this.controller = options.controller;
		},

		render: function() {
			this.$button = this.$( '.button' );
			this.$feedback = this.$( '.satispress-create-api-key-feedback' );
			this.$name = this.$( '#satispress-create-api-key-name' );
			this.$spinner = $( '<span class="spinner"></span>' ).insertAfter( this.$button );

			this.toggleButtonState();

			return this;
		},

		createApiKey: function( e ) {
			var view = this;

			e.preventDefault();

			if ( '' === this.$name.val() ) {
				return;
			}

			this.$button.prop( 'disabled', true );
			this.$spinner.addClass( 'is-active' );

			this.controller
				.createApiKey( this.$name.val(), data.userId )
				.done(function() {
					view.$name.val( '' );
					view.$feedback.hide().text( '' );
				})
				.fail(function( response ) {
					if ( 'message' in response ) {
						view.$feedback.text( response.message );
					}
				})
				.always(function( response ) {
					view.toggleButtonState();
					view.$spinner.removeClass( 'is-active' );
				});

		},

		toggleButtonState: function() {
			this.$button.prop( 'disabled', '' === this.$name.val() );
		},

		routeKeyPress: function( e ) {
			// Enter.
			if ( 13 === e.keyCode ) {
				this.createApiKey( e );
			}
		},
	});

	var controller = new app.controller.Manager({
		apiKeys: new app.collection.ApiKeys( data.items ),
	});

	new app.view.ApiKeysTable({
		el: document.getElementById( 'satispress-api-key-manager' ),
		collection: controller.get( 'apiKeys' ),
		controller: controller
	}).render();

})( this, jQuery, _, Backbone, wp );
