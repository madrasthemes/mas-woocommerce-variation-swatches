jQuery( function ( $ ) {

	// Only show the "remove image" button when needed
	if ( ! $('#mas_wcvs_image_id').val() ) {
		$('.mas_wcvs_remove_image_button').hide();
	}

	// Uploading files
	var file_frame;

	$(document).on( 'click', '.mas_wcvs_upload_image_button', function( event ){

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.downloadable_file = wp.media({
			title: mas_wcvs_admin_options.media_title,
			button: {
				text: mas_wcvs_admin_options.media_btn_text
			},
			multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();

			$('#mas_wcvs_image_id').val( attachment.id );
			$('#mas_wcvs_image img').attr('src', attachment.url );
			$('.mas_wcvs_remove_image_button').show();
		});

		// Finally, open the modal.
		file_frame.open();
	});

	$(document).on( 'click', '.mas_wcvs_remove_image_button', function( event ){
		$('#mas_wcvs_image img').attr('src', mas_wcvs_admin_options.placeholder_img_src);
		$('#mas_wcvs_image_id').val('');
		$('.mas_wcvs_remove_image_button').hide();
		return false;
	});

	// Add Color Picker to all inputs that have 'color-field' class
	$( '.mas_wcvs_color_picker' ).wpColorPicker();

	// Toggle add new attribute term modal
	var $modal = $( '#mas_wcvs-modal-container' ),
		$spinner = $modal.find( '.spinner' ),
		$msg = $modal.find( '.message' ),
		$metabox = null;

	$(document).on( 'click', '.mas_wcvs_add_new_attribute', function ( e ) {
		e.preventDefault();
		var $button = $( this ),
			taxInputTemplate = wp.template( 'mas_wcvs-input-tax' ),
			data = {
				type: $button.data( 'type' ),
				tax : $button.closest( '.woocommerce_attribute' ).data( 'taxonomy' )
			};

		// Insert input
		$modal.find( '.mas_wcvs-term-swatch' ).html( $( '#tmpl-mas_wcvs-input-' + data.type ).html() );
		$modal.find( '.mas_wcvs-term-tax' ).html( taxInputTemplate( data ) );

		if ( 'color' === data.type ) {
			$modal.find( 'input.mas_wcvs-input-color' ).wpColorPicker();
		}

		$metabox = $button.closest( '.woocommerce_attribute.wc-metabox' );
		$modal.show();
	} ).on( 'click', '.mas_wcvs-modal-close, .mas_wcvs-modal-backdrop', function ( e ) {
		e.preventDefault();

		closeModal();
	} );

	// Send ajax request to add new attribute term
	$(document).on( 'click', '.mas_wcvs-new-attribute-submit', function ( e ) {
		e.preventDefault();

		var $button = $( this ),
			type = $button.data( 'type' ),
			error = false,
			data = {};

		// Validate
		$modal.find( '.mas_wcvs-input' ).each( function () {
			var $this = $( this );

			if ( $this.attr( 'name' ) !== 'slug' && !$this.val() ) {
				$this.addClass( 'error' );
				error = true;
			} else {
				$this.removeClass( 'error' );
			}

			data[$this.attr( 'name' )] = $this.val();
		} );

		if ( error ) {
			return;
		}

		// Send ajax request
		$spinner.addClass( 'is-active' );
		$msg.hide();
		wp.ajax.send( 'mas_wcvs_add_new_attribute', {
			data   : data,
			error  : function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'error' ).text( res ).show();
			},
			success: function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'success' ).text( res.msg ).show();

				$metabox.find( 'select.attribute_values' ).append( '<option value="' + res.id + '" selected="selected">' + res.name + '</option>' );
				$metabox.find( 'select.attribute_values' ).change();

				closeModal();
			}
		} );
	} );

	/**
	 * Close modal
	 */
	function closeModal() {
		$modal.find( '.mas_wcvs-term-name input, .mas_wcvs-term-slug input' ).val( '' );
		$spinner.removeClass( 'is-active' );
		$msg.removeClass( 'error success' ).hide();
		$modal.hide();
	}

});