/*global wp:false */

(function( window, $, undefined ) {
	'use strict';

	jQuery(function( $ ) {
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
				$spinner = $( this ).siblings( '.spinner' ).show();

			wp.ajax.post( 'satispress_toggle_plugin', {
				plugin_file: $checkbox.val(),
				status: $checkbox.prop( 'checked' ),
				_wpnonce: $checkbox.siblings( '.satispress-status-nonce' ).val()
			}).done(function() {
				$checkbox.show();
				$spinner.hide();
			});
		});
	});
})( this, jQuery );
