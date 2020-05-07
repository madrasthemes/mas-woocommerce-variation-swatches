<?php
/**
 * MAS WCVS Frontend
 *
 * @class    MAS_WCVS_Frontend
 * @author   MadrasThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * MAS_WCVS_Frontend
 */
class MAS_WCVS_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	public function init_hooks() {
		// Add scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'swatches_variation_attribute_options_html' ), 10, 2 );
	}

	/**
	 * scripts function.
	 *
	 * @return void
	 */
	public function scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'mas-wcvs-scripts', plugins_url( 'assets/js/scripts' . $suffix . '.js', MAS_WCVS_PLUGIN_FILE ), array( 'jquery' ), mas_wcvs()->version );
		wp_register_style( 'mas-wcvs-style', plugins_url( 'assets/css/style.css', MAS_WCVS_PLUGIN_FILE ), '', mas_wcvs()->version );

		if( is_woocommerce() && ( is_product() || is_shop() || is_product_category() || is_tax( 'product_label' ) || is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			if( apply_filters( 'mas_wcvs_plugin_styles', true ) ) {
				wp_enqueue_style( 'mas-wcvs-style' );
			}
			wp_enqueue_script( 'mas-wcvs-scripts' );
		}
	}

	/**
	 * swatches output.
	 *
	 * @return string
	 */
	public function swatches_variation_attribute_options_html( $html, $args ) {
		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$type                  = mas_wcvs_attribute_type( $attribute );
		$swatch_types          = mas_wcvs_get_attribute_types();

		// Return if this is normal attribute
		if ( ! array_key_exists( $type, $swatch_types ) ) {
			return $html;
		}

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$swatches_html  = '';
		if ( apply_filters( 'mas_wcvs_is_variation_swatches_html', true ) && ! empty( $options ) && $product && taxonomy_exists( $attribute ) ) {
			// Get terms if this is a taxonomy - ordered. We need the names too.
			$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

			foreach ( $terms as $term ) {
				if ( in_array( $term->slug, $options ) ) {
					$swatches_html .= $this->swatches_html( $term, $type, $args );
				}
			}
		}

		if ( ! empty( $swatches_html ) ) {
			$swatches_html = sprintf( '<div id="%1$s" class="mas-wcvs-swatches" data-attribute_name="attribute_%2$s">%3$s</div>', esc_attr( $id ), esc_attr( $attribute ), $swatches_html );
			$html = sprintf( '<div class="hidden">%1$s</div>%2$s', $html, $swatches_html );
		}

		return $html;
	}

	/**
	 * swatches term output.
	 *
	 * @return string
	 */
	public function swatches_html( $term, $type, $args ) {
		$name     = apply_filters( 'woocommerce_variation_option_name', $term->name );
		$value    = $term->slug;
		$selected = sanitize_title( $args['selected'] ) == $value ? 'selected' : '';
		$html     = '';

		switch ( $type ) {
			case 'color':
				$color_hex = get_term_meta( $term->term_id, 'mas_wcvs_color', true );
				list( $r, $g, $b ) = sscanf( $color_hex, "#%02x%02x%02x" );
				$color = sprintf( '<span style="background-color:%s;color:%s;">%s</span>', $color_hex, "rgba($r,$g,$b,0.5)", $name );

				$html = sprintf( '<span class="mas-wcvs-swatch swatch-color swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $color );
				break;

			case 'image':
				$image_id = get_term_meta( $term->term_id, 'mas_wcvs_image_id', true );

				if( $image_id ) {
					$image_url = wp_get_attachment_thumb_url( $image_id );
				} else {
					$image_url = wc_placeholder_img_src();
				}

				$image = sprintf( '<span class="swatch-image-label">%2$s</span> <img src="%1$s" alt="%2$s">', $image_url, $name );

				$html = sprintf( '<span class="mas-wcvs-swatch swatch-image swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $image );
				break;

			case 'label':
				$label = get_term_meta( $term->term_id, 'mas_wcvs_label', true );

				$html = sprintf( '<span class="mas-wcvs-swatch swatch-label swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>', $name, $value, $selected, $label );
				break;

			default:
				break;
		}

		return apply_filters( 'mas_wcvs_variation_swatches_html', $html, $term, $type, $args );
	}
}

return new MAS_WCVS_Frontend();
