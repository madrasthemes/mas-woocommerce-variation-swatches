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
				//add_action( "{$attr_taxonomy_name}_edit_form_fields",	array( $this, 'edit_swatch_attr_fields' ), 10, 2 );
				//add_action( 'create_term',							    array( $this, 'save_swatch_attr_fields' ), 10, 3 );
				//add_action( 'edit_term',							    array( $this, 'save_swatch_attr_fields' ), 10, 3 );
				
				// Add columns
				//add_filter( "manage_edit-{$attr_taxonomy_name}_columns",	array( $this, 'product_swatch_attr_columns' ) );
				//add_filter( "manage_{$attr_taxonomy_name}_custom_column",	array( $this, 'product_swatch_attr_column' ), 10, 3 );
			}
		}
	}

	public function add_swatch_attr_fields() {
		?><div class="form-field"><h1>Hello World</h1></div><?php
	}

	public function edit_swatch_attr_fields( $term, $taxonomy ) {}

	public function save_swatch_attr_fields( $term_id, $tt_id, $taxonomy ) {}

	public function product_swatch_attr_columns( $columns ){}
	public function product_swatch_attr_column( $columns, $column, $id ){}

	public function color_form_field() {
		?><div class="form-field">
			<label for="term-color"><?php esc_html_e( 'Color', 'mas-wcvs' ); ?></label>
			<input type="text" name="">
		</div><?php
	}
}

new MAS_WCVS_Admin_Swatch_Taxonomies();