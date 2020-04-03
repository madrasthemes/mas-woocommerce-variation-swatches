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
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/class-mas-admin-swatch-taxonomies.php' );
		include_once( dirname( __FILE__ ) . '/class-mas-admin-product.php' );
	}

	/**
	 * scripts function.
	 *
	 * @return void
	 */
	public function scripts() {
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( strstr( $screen_id, 'edit-pa_' ) || in_array( 'edit-product', array( 'edit-product', 'product' ) ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_media();
			wp_register_script( 'mas-wcvs-admin-scripts', plugins_url( 'assets/js/admin' . $suffix . '.js', MAS_WCVS_PLUGIN_FILE ), array( 'jquery', 'wp-color-picker' ), mas_wcvs()->version );
			wp_register_style( 'mas-wcvs-admin-style', plugins_url( 'assets/css/admin.css', MAS_WCVS_PLUGIN_FILE ), '', mas_wcvs()->version );

			$js_options = apply_filters( 'mas_wcvs_admin_localize_script_data', array(
				'media_title'			=> esc_html__( 'Choose an image', 'mas-wcvs' ),
				'media_btn_text'		=> esc_html__( 'Use image', 'mas-wcvs' ),
				'placeholder_img_src'	=> wc_placeholder_img_src()
			) );

			wp_localize_script( 'mas-wcvs-admin-scripts', 'mas_wcvs_admin_options', $js_options );
		}

		if ( strstr( $screen_id, 'edit-pa_' ) || in_array( 'edit-product', array( 'edit-product', 'product' ) ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'mas-wcvs-admin-style' );
			wp_enqueue_script( 'mas-wcvs-admin-scripts' );
		}
	}
}

return new MAS_WCVS_Admin();