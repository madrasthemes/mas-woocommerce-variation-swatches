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

});