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
		add_action( 'init', array( $this, 'includes' ) );
	}

	public function init_hooks() {
		
	}

	/**
	 * Hooks actions and filters for attributes
	 * 
	 */
	public function setup_attribute_hooks() {
		
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/class-mas-admin-swatch-taxonomies.php' );
	}
}

return new MAS_WCVS_Admin();