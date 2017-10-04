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
	}

	/**
	 * scripts function.
	 *
	 * @return void
	 */
	public function scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'mas-wcvs-admin-scripts', plugins_url( 'assets/js/scripts' . $suffix . '.js', MAS_WCVS_PLUGIN_FILE ), array( 'jquery' ), mas_wcvs()->version );
	}
}

return new MAS_WCVS_Frontend();