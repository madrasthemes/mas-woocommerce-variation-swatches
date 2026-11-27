;(function ( $ ) {
	'use strict';

	$.fn.mas_wcvs_variation_swatches_form = function () {
		return this.each( function() {
			var $form = $( this ),
				selected = [];

			if ( $form.hasClass( 'swatches-support' ) ) {
				return;
			}

			$form
				.addClass( 'swatches-support' )
				.on( 'click', '.mas-wcvs-swatch', function ( e ) {
					e.preventDefault();
					var $el = $( this ),
						$select = $el.closest( '.value' ).find( 'select' ),
						attribute_name = $select.data( 'attribute_name' ) || $select.attr( 'name' ),
						value = $el.data( 'value' ),
						$option = $select.find( 'option' ).filter( function () {
							return $( this ).val() === String( value );
						} );

					$select.trigger( 'focusin' );

					// Check if this combination is available
					if ( ! $option.length || $option.prop( 'disabled' ) ) {
						$el.siblings( '.mas-wcvs-swatch' ).removeClass( 'selected' );
						$select.val( '' ).trigger( 'change' );
						$form.trigger( 'mas_wcvs_no_matching_variations', [$el] );
						return;
					}

					if ( selected.indexOf( attribute_name ) === -1 ) {
						selected.push( attribute_name );
					}

					if ( $el.hasClass( 'selected' ) ) {
						$select.val( '' );
						$el.removeClass( 'selected' );

						var idx = selected.indexOf( attribute_name );
						if ( idx !== -1 ) {
							selected.splice( idx, 1 );
						}
					} else {
						$el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
						$select.val( value );
					}

					$select.trigger( 'change' );
				} )
				.on( 'woocommerce_update_variation_values', function () {
					$form.find( '.mas-wcvs-swatches' ).each( function () {
						var $swatches = $( this ),
							$select = $swatches.closest( '.value' ).find( 'select' ),
							currentVal = String( $select.val() || '' );

						$swatches.find( '.mas-wcvs-swatch' ).each( function () {
							var $swatch = $( this ),
								val = String( $swatch.data( 'value' ) ),
								$opt = $select.find( 'option' ).filter( function () {
									return $( this ).val() === val;
								} );

							if ( ! $opt.length || $opt.prop( 'disabled' ) ) {
								$swatch.addClass( 'disabled' ).removeClass( 'selected' );
							} else {
								$swatch.removeClass( 'disabled' );
								$swatch.toggleClass( 'selected', currentVal === val );
							}
						} );
					} );
				} )
				.on( 'click', '.reset_variations', function () {
					$( this ).closest( '.variations_form' ).find( '.mas-wcvs-swatch' ).removeClass( 'selected disabled' );
					selected = [];
				} )
				.on( 'reset_data', function () {
					$form.find( '.mas-wcvs-swatch' ).removeClass( 'selected disabled' );
					selected = [];
				} )
				.on( 'mas_wcvs_no_matching_variations', function() {
					if ( typeof wc_add_to_cart_variation_params !== 'undefined' && wc_add_to_cart_variation_params.i18n_no_matching_variations_text ) {
						window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
					}
				} );
		} );
	};

	$( function () {
		$( '.variations_form' ).mas_wcvs_variation_swatches_form();
		$( document.body ).trigger( 'mas_wcvs_initialized' );
	} );

	$( document ).on( 'yith_quick_view_loaded', function () {
		$( '.variations_form' ).mas_wcvs_variation_swatches_form();
		$( document.body ).trigger( 'mas_wcvs_initialized' );
	} );
})( jQuery );