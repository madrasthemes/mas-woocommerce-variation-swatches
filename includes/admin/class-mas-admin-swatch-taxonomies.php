<?php
/**
 * Handles taxonomies with swatches in admin
 *
 * @class MAS_WCVS_Admin_Swatch_Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * MAS_WCVS_Admin_Swatch_Taxonomies class
 */
class MAS_WCVS_Admin_Swatch_Taxonomies {

	public function __construct() {
		$swatch_attr_taxonomies = mas_wcvs_get_swatch_attribute_taxonomies();

		if ( ! empty( $swatch_attr_taxonomies ) ) {
			foreach ( $swatch_attr_taxonomies as $swatch_attr_taxonomy ) {
				$attr_taxonomy_name = wc_attribute_taxonomy_name( $swatch_attr_taxonomy->attribute_name );
				
				// Add form
				add_action( "{$attr_taxonomy_name}_add_form_fields",	array( $this, 'add_swatch_attr_fields' ),  10 );
				add_action( "{$attr_taxonomy_name}_edit_form_fields",	array( $this, 'edit_swatch_attr_fields' ), 10, 2 );
				add_action( 'create_term',							    array( $this, 'save_swatch_attr_fields' ), 10, 3 );
				add_action( 'edit_term',							    array( $this, 'save_swatch_attr_fields' ), 10, 3 );
				
				// Add columns
				add_filter( "manage_edit-{$attr_taxonomy_name}_columns",	array( $this, 'product_swatch_attr_columns' ) );
				add_filter( "manage_{$attr_taxonomy_name}_custom_column",	array( $this, 'product_swatch_attr_column' ), 10, 3 );
			}
		}
	}

	public function add_swatch_attr_fields( $taxonomy ) {
		$type = mas_wcvs_attribute_type( $taxonomy );
		
		switch ( $type ) {
			case 'color':
				?>
				<div class="form-field">
					<label class="color"><?php esc_html_e( 'Color', 'mas-wcvs' ); ?></label>
					<input name="mas_wcvs_color" id="mas_wcvs_color" class="mas_wcvs_color_picker" type="text" value autocomplete="off">
					<p class="description"><?php echo esc_html__( 'Select a color.', 'mas-wcvs' ); ?></p>
				</div>
				<?php
				break;

			case 'image':
				?>
				<div class="form-field">
					<label><?php esc_html_e( 'Image', 'mas-wcvs' ); ?></label>
					<div id="mas_wcvs_image" style="float:left;margin-right:10px;"><img src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" alt="" /></div>
					<div style="line-height:60px;">
						<input type="hidden" id="mas_wcvs_image_id" name="mas_wcvs_image_id" />
						<button type="button" class="mas_wcvs_upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'mas-wcvs' ); ?></button>
						<button type="button" class="mas_wcvs_remove_image_button button"><?php esc_html_e( 'Remove image', 'mas-wcvs' ); ?></button>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				break;

			case 'label':
				?>
				<div class="form-field">
					<label class="label"><?php esc_html_e( 'Label', 'mas-wcvs' ); ?></label>
					<input name="mas_wcvs_label" id="mas_wcvs_label" type="text" value autocomplete="off">
					<p class="description"><?php echo esc_html__( 'Enter your label text.', 'mas-wcvs' ); ?></p>
				</div>
				<?php
				break;

			default:
				do_action( 'mas_wcvs_add_swatch_attr_fields', $taxonomy );
				break;
		}
	}

	public function edit_swatch_attr_fields( $term, $taxonomy ) {
		$type = mas_wcvs_attribute_type( $taxonomy );

		$color 		= get_term_meta( $term->term_id, 'mas_wcvs_color', true );
		$label 		= get_term_meta( $term->term_id, 'mas_wcvs_label', true );
		$image_id 	= get_term_meta( $term->term_id, 'mas_wcvs_image_id', true );
		$image 		= ( $image_id ) ? wp_get_attachment_thumb_url( $image_id ) : wc_placeholder_img_src();
		switch ( $type ) {
			case 'color':
				?>
				<tr class="form-field">
					<th scope="row" valign="top"><label><?php esc_html_e( 'Color', 'mas-wcvs' ); ?></label></th>
					<td>
						<input name="mas_wcvs_color" id="mas_wcvs_color" class="mas_wcvs_color_picker" type="text" value="<?php echo esc_attr( $color ); ?>" autocomplete="off">
					</td>
				</tr>
				<?php
				break;

			case 'image':
				?>
				<tr class="form-field">
					<th scope="row" valign="top"><label><?php esc_html_e( 'Image', 'mas-wcvs' ); ?></label></th>
					<td>
						<div id="mas_wcvs_image" style="float:left;margin-right:10px;"><img src="<?php echo esc_url( $image ); ?>" alt="" style="max-width: 150px; height: auto;" /></div>
						<div style="line-height:60px;">
							<input type="hidden" id="mas_wcvs_image_id" name="mas_wcvs_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
							<button type="submit" class="mas_wcvs_upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'mas-wcvs' ); ?></button>
							<button type="submit" class="mas_wcvs_remove_image_button button"><?php esc_html_e( 'Remove image', 'mas-wcvs' ); ?></button>
						</div>
						<div class="clear"></div>
					</td>
				</tr>
				<?php
				break;

			case 'label':
				?>
				<tr class="form-field">
					<th scope="row" valign="top"><label><?php esc_html_e( 'Label', 'mas-wcvs' ); ?></label></th>
					<td>
						<input name="mas_wcvs_label" id="mas_wcvs_label" type="text" value="<?php echo esc_attr( $label ); ?>" autocomplete="off">
					</td>
				</tr>
				<?php
				break;

			default:
				do_action( 'mas_wcvs_edit_swatch_attr_fields', $term, $taxonomy );
				break;
		}
	}

	public function save_swatch_attr_fields( $term_id, $tt_id, $taxonomy ) {
		if ( isset( $_POST['mas_wcvs_color'] ) ) {
			update_term_meta( $term_id, 'mas_wcvs_color', sanitize_text_field( $_POST['mas_wcvs_color'] ) );
		}

		if ( isset( $_POST['mas_wcvs_image_id'] ) ) {
			update_term_meta( $term_id, 'mas_wcvs_image_id', sanitize_text_field( $_POST['mas_wcvs_image_id'] ) );
		}

		if ( isset( $_POST['mas_wcvs_label'] ) ) {
			update_term_meta( $term_id, 'mas_wcvs_label', sanitize_text_field( $_POST['mas_wcvs_label'] ) );
		}

		do_action( 'mas_wcvs_save_swatch_attr_fields', $term_id, $tt_id, $taxonomy );

		delete_transient( 'wc_term_counts' );
	}

	public function product_swatch_attr_columns( $columns ) {
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : '';
		$type = mas_wcvs_attribute_type( $taxonomy );

		$new_columns = array();

		$new_columns['cb']    = isset( $columns['cb'] ) ? $columns['cb'] : '';

		switch ( $type ) {
			case 'color':
				$new_columns['color'] = esc_html__( 'Color', 'mas-wcvs' );
				break;

			case 'image':
				$new_columns['image'] = esc_html__( 'Image', 'mas-wcvs' );
				break;

			case 'label':
				$new_columns['label'] = esc_html__( 'Label', 'mas-wcvs' );
				break;

			default:
				break;
		}
		
		unset( $columns['cb'] );

		unset( $columns['description'] );

		return array_merge( $new_columns, $columns );
	}
	
	public function product_swatch_attr_column( $columns, $column, $id ) {

		switch ( $column ) {
			case 'color':
				$color 	= get_term_meta( $id, 'mas_wcvs_color', true );
				$columns .= ! empty( $color ) ? '<span style="background-color:' . esc_attr( $color ) . ';"></span>' : '';
				break;

			case 'image':
				$image_id 	= get_term_meta( $id, 'mas_wcvs_image_id', true );

				if( $image_id ) {
					$image = wp_get_attachment_thumb_url( $image_id );
				} else {
					$image = wc_placeholder_img_src();
				}

				$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_html__( 'Image', 'mas-wcvs' ) . '" class="wp-post-image" height="48" width="48" />';
				break;

			case 'label':
				$label 	= get_term_meta( $id, 'mas_wcvs_label', true );
				$columns .= ! empty( $label ) ? '<span>' . esc_html( $label ) . '</span>' : '';
				break;

			default:
				break;
		}

		return $columns;
	}
}

new MAS_WCVS_Admin_Swatch_Taxonomies();