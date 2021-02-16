/* global jQuery:false */

( function( window, $, undefined ) {
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

} )( this, jQuery );
