<?php
/**
 * Handles products with swatches in admin
 *
 * @class MAS_WCVS_Admin_Product
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * MAS_WCVS_Admin_Product class
 */
class MAS_WCVS_Admin_Product {

    public function __construct() {
        add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 2 );
    }
    
    public function product_option_terms( $attribute_taxonomy, $i ) {
        if ( array_key_exists( $attribute_taxonomy->attribute_type, mas_wcvs_get_attribute_types() ) ) {
            ?>
            <select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'mas-wcvs' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
                <?php
                $taxonomy_name = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
                global $thepostid;
                $product_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : $thepostid;
                $args      = array(
                    'orderby'    => 'name',
                    'hide_empty' => 0,
                );
                $all_terms = get_terms( $taxonomy_name, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
                if ( $all_terms ) {
                    foreach ( $all_terms as $term ) {
                        echo '<option value="' . esc_attr( $term->term_id ) . '"' . selected( has_term( absint( $term->term_id ), $taxonomy_name, $product_id ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
                    }
                }
                ?>
            </select>
            <button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'mas-wcvs' ); ?></button>
            <button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'mas-wcvs' ); ?></button>
            <button class="button fr plus add_new_attribute" data-type="<?php echo esc_attr( $attribute_taxonomy->attribute_type ); ?>"><?php esc_html_e( 'Add new', 'mas-wcvs' ); ?></button>
            <?php
        }
    }
}

new MAS_WCVS_Admin_Product();