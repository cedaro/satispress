/* global wp:false */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var $tabs = $( '.nav-tab-wrapper .nav-tab' ),
		$panels = $( '.satispress-tab-panel' ),
		updateTabs;

	updateTabs = function() {
		var hash = window.location.hash;

		$tabs.removeClass( 'nav-tab-active' ).filter( '[href="' + hash + '"]' ).addClass( 'nav-tab-active' );
		$panels.removeClass( 'is-active' ).filter( hash ).addClass( 'is-active' );

		if ( $tabs.filter( '.nav-tab-active' ).length < 1 ) {
			var href = $tabs.eq( 0 ).addClass( 'nav-tab-active' ).attr( 'href' );
			$panels.removeClass( 'is-active' ).filter( href ).addClass( 'is-active' );
		}
	};

	updateTabs();
	$( window ).on( 'hashchange', updateTabs );

	// Scroll back to the top when a tab panel is reloaded or submitted.
	setTimeout(function() {
		if ( location.hash ) {
			window.scrollTo( 0, 1 );
		}
	}, 1 );

	// Handle the checkbox for toggling plugins.
	$( '.satispress-status' ).on( 'change', function() {
		var $checkbox = $( this ),
			$spinner = $( this ).siblings( '.spinner' ).addClass( 'is-active' );

		wp.ajax.post( 'satispress_toggle_plugin', {
			plugin_file: $checkbox.val(),
			status: $checkbox.prop( 'checked' ),
			_wpnonce: $checkbox.siblings( '.satispress-status-nonce' ).val()
		}).done(function() {
			setTimeout( function() {
				$spinner.removeClass( 'is-active' );
			}, 300);
		}).fail(function() {
			$spinner.removeClass( 'is-active' );
		});
	});

	function toggleDropdown( $dropdown, isOpen ) {
		return $dropdown
			.toggleClass( 'is-open', isOpen )
			.attr( 'aria-expanded', isOpen );
	}

	$( document )
		.on( 'click', '.satispress-dropdown-toggle', function( e ) {
			var $group = $( e.target ).closest( '.satispress-dropdown-group' ),
				isOpen = $group.hasClass( 'is-open' );

			e.preventDefault();

			toggleDropdown( $group, ! isOpen );
		})
		.on( 'click', function( e ) {
			var $button = $( e.target ).closest( 'button' ),
				$group = $( e.target ).closest( '.satispress-dropdown-group' );

			if ( ! $button.hasClass( 'satispress-dropdown-toggle' ) ) {
				toggleDropdown( $( 'satispress-dropdown-group' ), false );
			} else {
				toggleDropdown( $( '.satispress-dropdown-group' ).not( $group ), false );
			}
		});

	/**
	 * ========================================================================
	 * Package Panels
	 * ========================================================================
	 */

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
