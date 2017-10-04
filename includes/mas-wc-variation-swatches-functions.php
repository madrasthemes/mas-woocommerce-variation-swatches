<?php
/**
 * Get attribute taxonomies.
 *
 * @return array of objects
 */
function mas_wcvs_get_swatch_attribute_taxonomies() {
	// if ( false === ( $attribute_taxonomies = get_transient( 'wc_swatch_attribute_taxonomies' ) ) ) {
		global $wpdb;

		$attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_type = 'color' OR attribute_type = 'image' OR attribute_type = 'label' order by attribute_name ASC;" );

		// set_transient( 'wc_swatch_attribute_taxonomies', $attribute_taxonomies );
	// }

	return (array) array_filter( apply_filters( 'woocommerce_swatch_attribute_taxonomies', $attribute_taxonomies ) );
}

/**
 * Get swatch attribute types
 * 
 * @return array of attribute types
 */
function mas_wcvs_get_attribute_types() {
	return mas_wcvs()->attribute_types;
}

/**
 * Build WHERE query to get swatch attribute types
 *
 * @return string
 */
function mas_wcvs_build_swatch_attribute_where_string() {
	$attribute_types = mas_wcvs_get_attribute_types();

	$where = array();

	foreach( $attribute_types as $attribute_type ) {
		$where[] = ' attribute_type = \'' . $attribute_type .'\'';
	}

	if ( ! empty( $where ) ) {
		$where_string = 'WHERE ' . implode( ' OR ', $where );
	}

	echo $where_string;

	return $where_string;
}

/**
 * Get a product attributes type setting.
 *
 * @param mixed $name
 * @return string
 */
function mas_wcvs_attribute_type( $name ) {
	global $wc_product_attributes, $wpdb;

	$name = str_replace( 'pa_', '', sanitize_title( $name ) );

	if ( isset( $wc_product_attributes[ 'pa_' . $name ] ) ) {
		$type = $wc_product_attributes[ 'pa_' . $name ]->attribute_type;
	} else {
		$type = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_type FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $name ) );
	}

	return apply_filters( 'mas_wcvs_attribute_type', $type, $name );
}

function mas_wcvs_variation_swatches_html( $term, $type, $args ) {
	$name     = apply_filters( 'woocommerce_variation_option_name', $term->name );
	$value    = $term->slug;
	$selected = sanitize_title( $args['selected'] ) == $value ? 'selected' : '';
	$html     = '';

	switch ( $type ) {
		case 'color':
			$color_hex = get_woocommerce_term_meta( $term->term_id, 'mas_wcvs_color', true );
			list( $r, $g, $b ) = sscanf( $color_hex, "#%02x%02x%02x" );
			$color = sprintf( '<span style="background-color:%s;color:%s;">%s</span>', $color_hex, "rgba($r,$g,$b,0.5)", $name );

			$html = sprintf( '<span class="mas-wcvs-swatch swatch-color swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $color );
			break;
		
		case 'image':
			$image_id = get_woocommerce_term_meta( $term->term_id, 'mas_wcvs_image_id', true );

			if( $image_id ) {
				$image_url = wp_get_attachment_thumb_url( $image_id );
			} else {
				$image_url = wc_placeholder_img_src();
			}

			$image = sprintf( '<img src="%s" alt="%s">', $image_url, $name );

			$html = sprintf( '<span class="mas-wcvs-swatch swatch-image swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $image );
			break;

		case 'label':
			$label = get_woocommerce_term_meta( $term->term_id, 'mas_wcvs_label', true );

			$html = sprintf( '<span class="mas-wcvs-swatch swatch-label swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $label );
			break;

		default:
			break;
	}
	
	return apply_filters( 'mas_wcvs_variation_swatches_html', $html, $term, $type, $args );
}

function mas_wcvs_variation_attribute_options_html( $html, $args ) {
	$options               = $args['options'];
	$product               = $args['product'];
	$attribute             = $args['attribute'];
	$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
	$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
	$type                  = mas_wcvs_attribute_type( $attribute );
	$swatch_types          = mas_wcvs()->attribute_types;

	// Return if this is normal attribute
	if ( ! array_key_exists( $type, $swatch_types ) ) {
		return $html;
	}

	if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
		$attributes = $product->get_variation_attributes();
		$options    = $attributes[ $attribute ];
	}

	$swatches_html  = '';
	if ( ! empty( $options ) && $product && taxonomy_exists( $attribute ) ) {
		// Get terms if this is a taxonomy - ordered. We need the names too.
		$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $options ) ) {
				$swatches_html .= mas_wcvs_variation_swatches_html( $term, $type, $args );
			}
		}
	}

	if ( ! empty( $swatches_html ) ) {
		$swatches_html = '<div class="mas-wcvs-swatches" data-attribute_name="attribute_' . esc_attr( $attribute ) . '">' . $swatches_html . '</div>';
		$html     = '<div class="hidden">' . $html . '</div>' . $swatches_html;
	}

	return $html;
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'mas_wcvs_variation_attribute_options_html', 10, 2 );