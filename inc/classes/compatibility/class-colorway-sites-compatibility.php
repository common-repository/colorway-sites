<?php
/**
 * Colorway Sites Compatibility for 3rd party plugins.
 *
 * @package Colorway Sites
 * @since 1.0.11
 */

if ( ! class_exists( 'Colorway_Sites_Compatibility' ) ) :

	/**
	 * Colorway Sites Compatibility
	 *
	 * @since 1.0.11
	 */
	class Colorway_Sites_Compatibility {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.11
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.11
		 * @return object initialized object of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.11
		 */
		public function __construct() {

			// Plugin -  Colorway Pro.
			require_once COLORWAY_SITES_DIR . 'inc/classes/compatibility/colorway-pro/class-colorway-sites-compatibility-colorway-pro.php';

			// Plugin - Site Origin Widgets.
			require_once COLORWAY_SITES_DIR . 'inc/classes/compatibility/so-widgets-bundle/class-colorway-sites-compatibility-so-widgets.php';

			// Plugin - WooCommerce.
			require_once COLORWAY_SITES_DIR . 'inc/classes/compatibility/woocommerce/class-colorway-sites-compatibility-woocommerce.php';

		}

	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Colorway_Sites_Compatibility::instance();

endif;


