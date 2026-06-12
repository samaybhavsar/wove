/**
 * Wove settings: wire the "Select photo" button to the WordPress media library
 * for the home intro portrait. Stores the chosen attachment ID in a hidden field
 * and shows a round preview. Progressive enhancement — the field works without JS
 * (the ID is just a number), this only adds the picker UI.
 */
( function ( $ ) {
	$( function () {
		var frame;
		var $id      = $( '#wove_intro_photo' );
		var $preview = $( '#wove-intro-photo-preview' );
		var $remove  = $( '#wove-intro-photo-remove' );
		var strings  = window.woveAdmin || {};

		$( '#wove-intro-photo-select' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title:    strings.frameTitle || 'Select photo',
				button:   { text: strings.frameButton || 'Use this photo' },
				library:  { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var att   = frame.state().get( 'selection' ).first().toJSON();
				var thumb = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;

				$id.val( att.id );
				$preview.html(
					$( '<img>', {
						src: thumb,
						css: { width: '96px', height: '96px', borderRadius: '50%', objectFit: 'cover' },
					} )
				);
				$remove.show();
			} );

			frame.open();
		} );

		$remove.on( 'click', function ( e ) {
			e.preventDefault();
			$id.val( '' );
			$preview.empty();
			$( this ).hide();
		} );
	} );
}( jQuery ) );
