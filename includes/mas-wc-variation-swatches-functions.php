<?php

if( ! function_exists( 'mas_wcvs_get_swatch_attribute_taxonomies' ) ) {
	/**
	 * Get attribute taxonomies.
	 *
	 * @return array of objects
	 */
	function mas_wcvs_get_swatch_attribute_taxonomies() {
		global $wpdb;

		$attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_type = 'color' OR attribute_type = 'image' OR attribute_type = 'label' order by attribute_name ASC;" );

		return (array) array_filter( apply_filters( 'woocommerce_swatch_attribute_taxonomies', $attribute_taxonomies ) );
	}
}

if( ! function_exists( 'mas_wcvs_get_attribute_types' ) ) {
	/**
	 * Get swatch attribute types
	 * 
	 * @return array of attribute types
	 */
	function mas_wcvs_get_attribute_types() {
		return apply_filters( 'mas_wcvs_get_attribute_types', array(
			'color' => esc_html__( 'Color', 'mas-wcvs' ),
			'image' => esc_html__( 'Image', 'mas-wcvs' ),
			'label' => esc_html__( 'Label', 'mas-wcvs' )
		) );
	}
}

if( ! function_exists( 'mas_wcvs_build_swatch_attribute_where_string' ) ) {
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
}

if( ! function_exists( 'mas_wcvs_attribute_type' ) ) {
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
}

if( ! function_exists( 'mas_wcvs_loop_variation' ) ) {
	/**
	 * Show Variation in Loop Products
	 *
	 */
	function mas_wcvs_loop_variation() {

		global $product;

		if ( apply_filters( 'mas_wcvs_loop_variation_enable', true ) && $product->is_type( 'variable' ) ) {
			woocommerce_variable_add_to_cart();
		}
	}
}

add_action( 'woocommerce_after_shop_loop_item', 'mas_wcvs_loop_variation', 6 );