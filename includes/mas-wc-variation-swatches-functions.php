<?php
/**
 * Get attribute taxonomies.
 *
 * @return array of objects
 */
function mas_wcvs_get_swatch_attribute_taxonomies() {
    if ( false === ( $attribute_taxonomies = get_transient( 'wc_swatch_attribute_taxonomies' ) ) ) {
        global $wpdb;

        $attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_type = 'color' OR attribute_type = 'image' OR attribute_type = 'label' order by attribute_name ASC;" );

        set_transient( 'wc_swatch_attribute_taxonomies', $attribute_taxonomies );
    }

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