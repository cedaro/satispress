/* global wp:false */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var PackagePanel = wp.Backbone.View.extend({
		initialize: function( options ) {
			this.model = options.model;
			this.selection = options.selection;
		},

		render: function() {
			var model = this.model,
				selection = this.selection;

			this.$( '.satispress-release' ).each(function() {
				new ReleaseButton({
					el: this,
					selection: selection
				}).render();
			});

			this.views.add( '.satispress-releases', [
				new ReleaseActions({
					package: this.model,
					selection: this.selection
				})
			]);

			return this;
		}
	});

	var ReleaseButton = wp.Backbone.View.extend({
		events: {
			'click': 'click'
		},

		initialize: function( options ) {
			this.model = new Backbone.Model();
			this.selection = options.selection;

			this.listenTo( this.selection, 'add remove reset', this.updateSelectedClass );
		},

		render: function() {
			var $button = $( '<button/>', {
				'aria-expanded': false,
				'class': this.$el.attr( 'class' ),
				'text': this.$el.text()
			});

			this.model.set({
				download_url: this.$el.attr( 'href' ),
				version: this.$el.data( 'version' )
			});

			this.$el.replaceWith( $button );
			this.setElement( $button );

			this.updateSelectedClass();

			return this;
		},

		click: function( e ) {
			e.preventDefault();

			if ( this.isSelected() ) {
				this.selection.remove( this.model );
			} else {
				this.selection.reset( this.model );
			}
		},

		isSelected: function() {
			return this.selection.length > 0 && this.model === this.selection.first();
		},

		updateSelectedClass: function() {
			var isSelected = this.isSelected();

			this.$el
				.toggleClass( 'active', isSelected )
				.attr( 'aria-expanded', isSelected );
		}
	});

	var ReleaseActions = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'satispress-release-actions',
		template: wp.template( 'satispress-release-actions' ),

		events: {
			'click input': 'selectTextField'
		},

		initialize: function( options ) {
			this.package = options.package;
			this.selection = options.selection;
			this.listenTo( this.selection, 'add change remove reset', this.render );
		},

		render: function() {
			var data;

			if ( this.selection.length ) {
				data = _.extend( this.selection.first().toJSON(), this.package.toJSON() );
				this.$el.html( this.template( data ) ).show();
			} else {
				this.$el.hide();
			}

			return this;
		},

		selectTextField: function( e ) {
			e.target.select();
		}
	});

	$( '.satispress-package' ).each(function() {
		new PackagePanel({
			el: this,
			model: new Backbone.Model({
				name: $( this ).find( 'thead th' ).text()
			}),
			selection: new Backbone.Collection([])
		}).render();
	});

})( this, jQuery, _, Backbone, wp );
