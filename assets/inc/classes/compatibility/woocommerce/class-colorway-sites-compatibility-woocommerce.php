<?php
/**
 * ColorWay Sites Compatibility for 'WooCommerce'
 *
 * @see  https://wordpress.org/plugins/woocommerce/
 *
 * @package ColorWay Sites
 * @since 1.1.4
 */

if ( ! class_exists( 'Colorway_Sites_Compatibility_WooCommerce' ) ) :

	/**
	 * WooCommerce Compatibility
	 *
	 * @since 1.1.4
	 */
	class Colorway_Sites_Compatibility_WooCommerce {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.1.4
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.1.4
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
		 * @since 1.1.4
		 */
		public function __construct() {
			add_filter( 'woocommerce_enable_setup_wizard', '__return_false', 5 );
			add_action( 'colorway_sites_import_start', array( $this, 'add_attributes' ), 10, 2 );
		}

		/**
		 * Add product attributes.
		 *
		 * @since 1.1.4
		 *
		 * @param  string $demo_data        Import data.
		 * @param  array  $demo_api_uri     Demo site URL.
		 * @return void
		 */
		function add_attributes( $demo_data = array(), $demo_api_uri = '' ) {
			$attributes = ( isset( $demo_data['colorway-site-options-data']['woocommerce_product_attributes'] ) ) ? $demo_data['colorway-site-options-data']['woocommerce_product_attributes'] : array();

			if ( ! empty( $attributes ) && function_exists( 'wc_create_attribute' ) ) {
				foreach ( $attributes as $key => $attribute ) {
					$args = array(
						'name'         => $attribute['attribute_label'],
						'slug'         => $attribute['attribute_name'],
						'type'         => $attribute['attribute_type'],
						'order_by'     => $attribute['attribute_orderby'],
						'has_archives' => $attribute['attribute_public'],
					);

					$id = wc_create_attribute( $args );
				}
			}
		}
	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Colorway_Sites_Compatibility_WooCommerce::instance();

endif;
