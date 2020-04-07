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
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 2 );

		add_action( 'wp_ajax_mas_wcvs_add_new_attribute', array( $this, 'add_new_attribute_ajax' ) );
		add_action( 'admin_footer', array( $this, 'add_attribute_term_template' ) );
	}

	/**
	 * Add selector for extra attribute types
	 *
	 * @param $taxonomy
	 * @param $index
	 */
	public function product_option_terms( $taxonomy, $index ) {
		if ( ! array_key_exists( $taxonomy->attribute_type, mas_wcvs_get_attribute_types() ) ) {
			return;
		}

		$taxonomy_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );
		global $thepostid;

		$product_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : $thepostid;
		?>

		<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'mas-wcvs' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo $index; ?>][]">
			<?php

			$all_terms = get_terms( $taxonomy_name, apply_filters( 'woocommerce_product_attribute_terms', array( 'orderby' => 'name', 'hide_empty' => false ) ) );
			if ( $all_terms ) {
				foreach ( $all_terms as $term ) {
					echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy_name, $product_id ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
				}
			}
			?>
		</select>
		<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'mas-wcvs' ); ?></button>
		<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'mas-wcvs' ); ?></button>
		<button class="button fr plus mas_wcvs_add_new_attribute" data-type="<?php echo $taxonomy->attribute_type ?>"><?php esc_html_e( 'Add new', 'mas-wcvs' ); ?></button>

		<?php
	}

	/**
	 * Ajax function handles adding new attribute term
	 */
	public function add_new_attribute_ajax() {
		$nonce  = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$tax    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
		$type   = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$name   = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$slug   = isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';
		$swatch = isset( $_POST['swatch'] ) ? sanitize_text_field( $_POST['swatch'] ) : '';

		if ( ! wp_verify_nonce( $nonce, '_mas_wcvs_create_attribute' ) ) {
			wp_send_json_error( esc_html__( 'Wrong request', 'mas-wcvs' ) );
		}

		if ( empty( $name ) || empty( $swatch ) || empty( $tax ) || empty( $type ) ) {
			wp_send_json_error( esc_html__( 'Not enough data', 'mas-wcvs' ) );
		}

		if ( ! taxonomy_exists( $tax ) ) {
			wp_send_json_error( esc_html__( 'Taxonomy is not exists', 'mas-wcvs' ) );
		}

		if ( term_exists( $name, $tax ) ) {
			wp_send_json_error( esc_html__( 'This term is exists', 'mas-wcvs' ) );
		}

		$term = wp_insert_term( $name, $tax, array( 'slug' => $slug ) );

		if ( is_wp_error( $term ) ) {
			wp_send_json_error( $term->get_error_message() );
		} else {
			switch ( $type ) {
				case 'color':
					$meta_key = 'mas_wcvs_color';
					break;

				case 'image':
					$meta_key = 'mas_wcvs_image_id';
					break;

				case 'label':
					$meta_key = 'mas_wcvs_label';
					break;

				default:
					$meta_key = $type;
					break;
			}

			$term = get_term_by( 'id', $term['term_id'], $tax );
			update_term_meta( $term->term_id, $meta_key, $swatch );
		}

		wp_send_json_success(
			array(
				'msg'  => esc_html__( 'Added successfully', 'mas-wcvs' ),
				'id'   => $term->term_id,
				'slug' => $term->slug,
				'name' => $term->name,
			)
		);
	}

	/**
	 * Print HTML of modal at admin footer and add js templates
	 */
	public function add_attribute_term_template() {
		global $pagenow, $post;

		if ( $pagenow != 'post.php' || ( isset( $post ) && get_post_type( $post->ID ) != 'product' ) ) {
			return;
		}
		?>

		<div id="mas_wcvs-modal-container" class="mas_wcvs-modal-container">
			<div class="mas_wcvs-modal">
				<button type="button" class="button-link media-modal-close mas_wcvs-modal-close">
					<span class="media-modal-icon"></span></button>
				<div class="mas_wcvs-modal-header"><h2><?php esc_html_e( 'Add new term', 'mas-wcvs' ) ?></h2></div>
				<div class="mas_wcvs-modal-content">
					<p class="mas_wcvs-term-name">
						<label>
							<?php esc_html_e( 'Name', 'mas-wcvs' ) ?>
							<input type="text" class="widefat mas_wcvs-input" name="name">
						</label>
					</p>
					<p class="mas_wcvs-term-slug">
						<label>
							<?php esc_html_e( 'Slug', 'mas-wcvs' ) ?>
							<input type="text" class="widefat mas_wcvs-input" name="slug">
						</label>
					</p>
					<div class="mas_wcvs-term-swatch">

					</div>
					<div class="hidden mas_wcvs-term-tax"></div>

					<input type="hidden" class="mas_wcvs-input" name="nonce" value="<?php echo wp_create_nonce( '_mas_wcvs_create_attribute' ) ?>">
				</div>
				<div class="mas_wcvs-modal-footer">
					<button class="button button-secondary mas_wcvs-modal-close"><?php esc_html_e( 'Cancel', 'mas-wcvs' ) ?></button>
					<button class="button button-primary mas_wcvs-new-attribute-submit"><?php esc_html_e( 'Add New', 'mas-wcvs' ) ?></button>
					<span class="message"></span>
					<span class="spinner"></span>
				</div>
			</div>
			<div class="mas_wcvs-modal-backdrop media-modal-backdrop"></div>
		</div>

		<script type="text/template" id="tmpl-mas_wcvs-input-color">

			<label><?php esc_html_e( 'Color', 'mas-wcvs' ) ?></label><br>
			<input type="text" class="mas_wcvs-input mas_wcvs-input-color" name="swatch">

		</script>

		<script type="text/template" id="tmpl-mas_wcvs-input-image">

			<label><?php esc_html_e( 'Image', 'mas-wcvs' ) ?></label><br>
			<div class="mas_wcvs-term-image-thumbnail" style="float:left;margin-right:10px;">
				<img src="<?php echo esc_url( WC()->plugin_url() . '/assets/images/placeholder.png' ) ?>" width="60px" height="60px" />
			</div>
			<div style="line-height:60px;">
				<input type="hidden" class="mas_wcvs-input mas_wcvs-input-image mas_wcvs-term-image" name="swatch" value="" />
				<button type="button" class="mas_wcvs-upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'mas-wcvs' ); ?></button>
				<button type="button" class="mas_wcvs-remove-image-button button hidden"><?php esc_html_e( 'Remove image', 'mas-wcvs' ); ?></button>
			</div>

		</script>

		<script type="text/template" id="tmpl-mas_wcvs-input-label">

			<label>
				<?php esc_html_e( 'Label', 'mas-wcvs' ) ?>
				<input type="text" class="widefat mas_wcvs-input mas_wcvs-input-label" name="swatch">
			</label>

		</script>

		<script type="text/template" id="tmpl-mas_wcvs-input-tax">

			<input type="hidden" class="mas_wcvs-input" name="taxonomy" value="{{data.tax}}">
			<input type="hidden" class="mas_wcvs-input" name="type" value="{{data.type}}">

		</script>
		<?php
	}
}

new MAS_WCVS_Admin_Product();