<?php
/**
 * Setup Woocommerce Quick View plugin.
 *
 * @package Ultimate_Woo_Quick_View
 */

namespace Uwquickview;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Class to setup Woocommerce Quick View plugin.
 */
class Setup {
	/**
	 * Init the class setup.
	 */
	public static function init() {

		$class = new Setup();

		add_action( 'plugins_loaded', array( $class, 'setup' ) );

	}

	/**
	 * Setup the class.
	 */
	public function setup() {

		// This plugin depends on Woocommerce, so let's check if it's active.
		if ( ! class_exists( 'WooCommerce' ) ) {

			// Stop if Woocommerce is not installed or not active.
			return;

		}

		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		$this->load_modules();

		register_deactivation_hook( plugin_basename( __FILE__ ), array( $this, 'deactivation' ), 20 );

	}

	/**
	 * Admin body class.
	 */
	public function admin_body_class( $classes ) {

		$screens = array(
			'woocommerce_page_uwquickview_settings',
		);

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $screens, true ) ) {
			return $classes;
		}

		$classes .= ' heatbox-admin has-header';

		return $classes;

	}

	/**
	 * Load Woocommerce Quick View modules.
	 */
	public function load_modules() {

		$modules = array();

		$modules['Uwquickview\\QuickView\\Quick_View_Module'] = __DIR__ . '/modules/quick-view/class-quick-view-module.php';
		$modules['Uwquickview\\Settings\\Settings_Module']    = __DIR__ . '/modules/settings/class-settings-module.php';

		$modules = apply_filters( 'uwquickview_modules', $modules );

		foreach ( $modules as $class => $file ) {
			$splits      = explode( '/', $file );
			$module_name = $splits[ count( $splits ) - 2 ];
			$filter_name = str_ireplace( '-', '_', $module_name );
			$filter_name = 'uwquickview_' . $filter_name;

			// We have a filter here uwquickview_$module_name to allow us to prevent loading modules under certain circumstances.
			if ( apply_filters( $filter_name, true ) ) {

				require_once $file;
				$module = new $class();
				$module->setup();

			}
		}

	}
}
