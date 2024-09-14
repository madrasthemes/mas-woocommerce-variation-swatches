<?php
/**
 * Plugin Name: MAS Variation Swatches for WooCommerce
 * Plugin URI: https://github.com/madrasthemes/mas-woocommerce-variation-swatches
 * Description: Replace dropdown fields on your variable products with Color, Label and Image Swatches.
 * Version: 1.0.7
 * Author: MadrasThemes
 * Author URI: https://madrasthemes.com/
 * Text Domain: mas-wcvs
 * Domain Path: /languages/
 * WC tested up to: 9.3
 *
 * @package MAS_WCVS
 * @category Core
 * @author MadrasThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define MAS_WCVS_PLUGIN_FILE.
if ( ! defined( 'MAS_WCVS_PLUGIN_FILE' ) ) {
	define( 'MAS_WCVS_PLUGIN_FILE', __FILE__ );
}

/**
 * Required functions
 */
if ( ! function_exists( 'mas_wcvs_is_woocommerce_active' ) ) {
	function mas_wcvs_is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if ( mas_wcvs_is_woocommerce_active() ) {
	if ( ! class_exists( 'MAS_WCVS' ) ) {
		include_once dirname( MAS_WCVS_PLUGIN_FILE ) . '/includes/class-mas-wcvs.php';
	}

	/**
	 * Main instance of MAS_WCVS class
	 *
	 * Returns the main instance of MAS_WCVS class to prevent the need to use globals.
	 *
	 * @return MAS_WCVS
	 */
	function mas_wcvs() {
		return MAS_WCVS::instance();
	}

	// Global for backward compatibility
	$GLOBALS['mas_wcvs'] = mas_wcvs();
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );