<?php
/**
 * MAS WCVS Admin
 *
 * @class    MAS_WCVS_Admin
 * @author   MadrasThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * MAS_WCVS_Admin
 */
class MAS_WCVS_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		add_action( 'init', array( $this, 'includes' ) );
	}

	public function init_hooks() {
		// Add scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 2 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/class-mas-admin-swatch-taxonomies.php' );
	}

	/**
	 * scripts function.
	 *
	 * @return void
	 */
	public function scripts() {
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( strstr( $screen_id, 'edit-pa_' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_media();
			wp_register_script( 'mas-wcvs-admin-scripts', plugins_url( 'assets/js/admin' . $suffix . '.js', MAS_WCVS_PLUGIN_FILE ), array( 'jquery', 'wp-color-picker' ), mas_wcvs()->version );
			wp_register_style( 'mas-wcvs-admin-style', plugins_url( 'assets/css/admin.css', MAS_WCVS_PLUGIN_FILE ), '', mas_wcvs()->version );

			$js_options = apply_filters( 'mas_wcvs_admin_localize_script_data', array(
				'media_title'			=> esc_html__( 'Choose an image', 'mas-wcvs' ),
				'media_btn_text'		=> esc_html__( 'Use image', 'mas-wcvs' ),
				'placeholder_img_src'	=> wc_placeholder_img_src()
			) );

			wp_localize_script( 'mas-wcvs-admin', 'mas_wcvs_admin_options', $js_options );
		}
	}

	/**
	 * Add selector for extra attribute types
	 *
	 * @param $taxonomy
	 * @param $index
	 */
	public function product_option_terms( $taxonomy, $i ) {
		if ( ! array_key_exists( $taxonomy->attribute_type, mas_wcvs()->attribute_types ) ) {
			return;
		}

		$taxonomy_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );
		?>
		<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'mas-wcvs' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo $i; ?>][]">
			<?php
			$args = array(
				'orderby'    => 'name',
				'hide_empty' => 0,
			);
			$all_terms = get_terms( $taxonomy_name, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
			if ( $all_terms ) {
				foreach ( $all_terms as $term ) {
					echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( has_term( $term->term_id, $taxonomy_name ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
				}
			}
			?>
		</select>
		<button class="button plus select_all_attributes"><?php _e( 'Select all', 'mas-wcvs' ); ?></button>
		<button class="button minus select_no_attributes"><?php _e( 'Select none', 'mas-wcvs' ); ?></button>
		<button class="button fr plus add_new_attribute"><?php _e( 'Add new', 'mas-wcvs' ); ?></button>
		<?php
	}
}

return new MAS_WCVS_Admin();