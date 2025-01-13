;(function ( $ ) {
	'use strict';

	/**
	 * @TODO Code a function the calculate available combination instead of use WC hooks
	 */
	$.fn.mas_wcvs_variation_swatches_form = function () {
		return this.each( function() {
			var $form = $( this ),
				clicked = null,
				selected = [];

			$form
				.addClass( 'swatches-support' )
				.on( 'click', '.mas-wcvs-swatch', function ( e ) {
					e.preventDefault();
					var $el = $( this ),
						$select = $el.closest( '.value' ).find( 'select' ),
						attribute_name = $select.data( 'attribute_name' ) || $select.attr( 'name' ),
						value = $el.data( 'value' );

					$select.trigger( 'focusin' );

					// Check if this combination is available
					if ( ! $select.find( 'option[value="' + value + '"]' ).length ) {
						$el.siblings( '.mas-wcvs-swatch' ).removeClass( 'selected' );
						$select.val( '' ).change();
						$form.trigger( 'mas_wcvs_no_matching_variations', [$el] );
						return;
					}

					clicked = attribute_name;

					if ( selected.indexOf( attribute_name ) === -1 ) {
						selected.push(attribute_name);
					}

					if ( $el.hasClass( 'selected' ) ) {
						$select.val( '' );
						$el.removeClass( 'selected' );

						delete selected[selected.indexOf(attribute_name)];
					} else {
						$el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
						$select.val( value );
					}

					$select.change();
				} )
				.on( 'click', '.reset_variations', function () {
					$( this ).closest( '.variations_form' ).find( '.mas-wcvs-swatch.selected' ).removeClass( 'selected' );
					selected = [];
				} )
				.on( 'mas_wcvs_no_matching_variations', function() {
					window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
				} );
		} );
	};

	$( function () {
		$( '.variations_form' ).mas_wcvs_variation_swatches_form();
		$( document.body ).trigger( 'mas_wcvs_initialized' );
	} );

	$(document).on('yith_quick_view_loaded', function () {
	    $('.variations_form').mas_wcvs_variation_swatches_form();
	    $(document.body).trigger('mas_wcvs_initialized');
	});
})( jQuery );