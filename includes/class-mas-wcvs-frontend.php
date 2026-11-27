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
		wp_register_script( 'mas-wcvs-scripts', plugins_url( 'assets/js/scripts' . $suffix . '.js', MAS_WCVS_PLUGIN_FILE ), array( 'jquery', 'wc-add-to-cart-variation' ), mas_wcvs()->version, true );
		wp_register_style( 'mas-wcvs-style', plugins_url( 'assets/css/style.css', MAS_WCVS_PLUGIN_FILE ), array(), mas_wcvs()->version );
		wp_style_add_data( 'mas-wcvs-style', 'rtl', 'replace' );

		$should_enqueue = is_woocommerce() && ( is_product() || is_shop() || is_product_category() || is_tax( 'product_label' ) || is_tax( get_object_taxonomies( 'product' ) ) );

		if ( apply_filters( 'mas_wcvs_enqueue_scripts', $should_enqueue ) ) {
			if ( apply_filters( 'mas_wcvs_plugin_styles', true ) ) {
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
		$options      = isset( $args['options'] ) ? $args['options'] : array();
		$product      = isset( $args['product'] ) ? $args['product'] : null;
		$attribute    = isset( $args['attribute'] ) ? $args['attribute'] : '';
		$id           = ! empty( $args['id'] ) ? $args['id'] : sanitize_title( $attribute );
		$type         = mas_wcvs_attribute_type( $attribute );
		$swatch_types = mas_wcvs_get_attribute_types();

		// Return if this is normal attribute
		if ( ! array_key_exists( $type, $swatch_types ) ) {
			return $html;
		}

		if ( empty( $options ) && $product instanceof WC_Product && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = isset( $attributes[ $attribute ] ) ? $attributes[ $attribute ] : array();
		}

		$swatches_html = '';
		if ( apply_filters( 'mas_wcvs_is_variation_swatches_html', true ) && ! empty( $options ) && $product instanceof WC_Product && taxonomy_exists( $attribute ) ) {
			// Get terms if this is a taxonomy - ordered. We need the names too.
			$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {
						$swatches_html .= $this->swatches_html( $term, $type, $args );
					}
				}
			}
		}

		if ( ! empty( $swatches_html ) ) {
			$swatches_html = sprintf( '<div id="%1$s" class="mas-wcvs-swatches" data-attribute_name="attribute_%2$s">%3$s</div>', esc_attr( $id ), esc_attr( $attribute ), $swatches_html );
			$html          = sprintf( '<div class="hidden">%1$s</div>%2$s', $html, $swatches_html );
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
		$selected = isset( $args['selected'] ) && sanitize_title( (string) $args['selected'] ) === $value ? 'selected' : '';
		$html     = '';

		switch ( $type ) {
			case 'color':
				$color_hex = (string) get_term_meta( $term->term_id, 'mas_wcvs_color', true );
				$rgba      = $this->hex_to_rgba( $color_hex, 0.5 );
				$style     = '';
				if ( '' !== $color_hex ) {
					$style = sprintf( 'background-color:%s;color:%s;', esc_attr( $color_hex ), esc_attr( $rgba ) );
				}
				$color = sprintf( '<span style="%s">%s</span>', $style, esc_html( $name ) );

				$html = sprintf(
					'<span class="mas-wcvs-swatch swatch-color swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $selected ),
					$color
				);
				break;

			case 'image':
				$image_id  = absint( get_term_meta( $term->term_id, 'mas_wcvs_image_id', true ) );
				$image_url = $image_id ? wp_get_attachment_thumb_url( $image_id ) : '';
				if ( ! $image_url ) {
					$image_url = wc_placeholder_img_src();
				}

				$image = sprintf( '<span class="swatch-image-label">%2$s</span> <img src="%1$s" alt="%2$s">', esc_url( $image_url ), esc_attr( $name ) );

				$html = sprintf(
					'<span class="mas-wcvs-swatch swatch-image swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $selected ),
					$image
				);
				break;

			case 'label':
				$label = (string) get_term_meta( $term->term_id, 'mas_wcvs_label', true );

				$html = sprintf(
					'<span class="mas-wcvs-swatch swatch-label swatch-%2$s %3$s" data-value="%2$s" title="%1$s">%4$s</span>',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $selected ),
					esc_html( $label )
				);
				break;

			default:
				break;
		}

		return apply_filters( 'mas_wcvs_variation_swatches_html', $html, $term, $type, $args );
	}

	/**
	 * Convert a hex color string to an rgba() CSS value safely.
	 *
	 * @param string $hex   Hex color string (#rgb or #rrggbb).
	 * @param float  $alpha Alpha channel value.
	 * @return string
	 */
	private function hex_to_rgba( $hex, $alpha = 0.5 ) {
		$hex = ltrim( trim( (string) $hex ), '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if ( 6 === strlen( $hex ) && ctype_xdigit( $hex ) ) {
			$rgb = sscanf( $hex, '%02x%02x%02x' );
			if ( is_array( $rgb ) && 3 === count( $rgb ) ) {
				return sprintf( 'rgba(%d,%d,%d,%s)', (int) $rgb[0], (int) $rgb[1], (int) $rgb[2], (float) $alpha );
			}
		}

		return 'rgba(0,0,0,' . (float) $alpha . ')';
	}
}

return new MAS_WCVS_Frontend();
