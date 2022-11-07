<?php
/**
 * Variation Swatches setup
 *
 * @author MadrasThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class MAS_WCVS {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.4';

	/**
	 * The single instance of the class.
	 *
	 * @var Mas_WCVS
	 */
	protected static $_instance = null;

	/**
	 * Extra attribute types
	 *
	 * @var array
	 */
	public $attribute_types = array();

	/**
	 * Main Mas_WCVS Instance.
	 *
	 * Ensures only one instance of Mas_WCVS is loaded or can be loaded.
	 *
	 * @static
	 * @return Mas_WCVS - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'mas-wcvs' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'mas-wcvs' ), '2.1' );
	}

	/**
	 * Mas_WCVS Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		$this->attribute_types = mas_wcvs_get_attribute_types();

		do_action( 'mas_wcvs_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );

		// Widgets Register
		add_action( 'widgets_init', array( $this, 'widgets_register' ) );
	}

	/**
	 * Add extra attribute types
	 * Add color, image and label type
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_attribute_types( $types ) {
		$types = array_merge( $types, mas_wcvs_get_attribute_types() );
		return $types;
	}

	/**
	 * Define Docs constants
	 */
	private function define_constants() {
		$this->define( 'MAS_WCVS_ABSPATH', dirname( MAS_WCVS_PLUGIN_FILE ) . '/' );
		$this->define( 'MAS_WCVS_PLUGIN_BASENAME', plugin_basename( MAS_WCVS_PLUGIN_FILE ) );
		$this->define( 'MAS_WCVS_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		include MAS_WCVS_ABSPATH . 'includes/mas-wc-variation-swatches-functions.php';

		if ( $this->is_request( 'admin' ) ) {
			include_once( MAS_WCVS_ABSPATH . 'includes/admin/class-mas-wcvs-admin.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
			include_once( MAS_WCVS_ABSPATH . 'includes/class-mas-wcvs-frontend.php' );
		}
	}

	/**
	 * Register widgets.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
	 */
	public function widgets_register() {
		include_once MAS_WCVS_ABSPATH . 'includes/class-mas-wcvs-widget-layered-nav.php';
		register_widget( 'MAS_WCVS_Widget_Layered_Nav' );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', MAS_WCVS_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( MAS_WCVS_PLUGIN_FILE ) );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Init Docs when Wordpress Initializes
	 */
	public function init() {
		// Before init action.
		do_action( 'before_mas_wcvs_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/mas-woocommerce-variation-swatches/mas-wcvs-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/mas-wcvs-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'mas-wcvs' );

		unload_textdomain( 'mas-wcvs' );
		load_textdomain( 'mas-wcvs', WP_LANG_DIR . '/mas-woocommerce-variation-swatches/mas-wcvs-' . $locale . '.mo' );
		load_plugin_textdomain( 'mas-wcvs', false, plugin_basename( dirname( MAS_WCVS_PLUGIN_FILE ) ) . '/languages' );
	}
}