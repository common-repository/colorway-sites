<?php
/**
 * ColorWay Sites Compatibility for ' ColorWay Pro'
 *
 * @see  https://wordpress.org/plugins/colorway-pro/
 *
 * @package ColorWay Sites
 * @since 1.0.0
 */

if ( ! class_exists( 'Colorway_Sites_Compatibility_Colorway_Pro' ) ) :

	/**
	 * Colorway_Sites_Compatibility_Colorway_Pro
	 *
	 * @since 1.0.0
	 */
	class Colorway_Sites_Compatibility_Colorway_Pro {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'colorway_sites_after_plugin_activation', array( $this, 'colorway_pro' ), 10, 2 );
			add_action( 'colorway_sites_import_start', array( $this, 'import_enabled_extension' ), 10, 2 );
			add_action( 'colorway_sites_import_complete', array( $this, 'clear_cache' ) );
		}

		/**
		 * Import
		 *
		 * @since 1.1.6
		 * @return void
		 */
		public function import() {
			Colorway_Sites_Image_Importer::log( '---- Processing Mapping - for  ColorWay Pro ----' );

			self::start_post_mapping();
		}

		/**
		 * Update Site Origin Active Widgets
		 *
		 * @since 1.0.0
		 *
		 * @param  string $plugin_init        Plugin init file.
		 * @param  array  $data               Data.
		 * @return void
		 */
		function colorway_pro( $plugin_init = '', $data = array() ) {

			if ( 'colorway-addon/colorway-addon.php' === $plugin_init ) {

				$data = json_decode( json_encode( $data ), true );

				if ( isset( $data['enabled_extensions'] ) ) {
					$extensions = $data['enabled_extensions'];

					if ( ! empty( $extensions ) ) {
						if ( is_callable( 'Colorway_Admin_Helper::update_admin_settings_option' ) ) {
							Colorway_Admin_Helper::update_admin_settings_option( '_colorway_ext_enabled_extensions', $extensions );
						}
					}
				}
			}
		}

		/**
		 * Import custom 404 section.
		 *
		 * @since 1.0.0
		 * @param  array $demo_data Site all data render from API call.
		 * @param  array $demo_api_uri Demo URL.
		 */
		public function import_custom_404( $demo_data = array(), $demo_api_uri = '' ) {

			if ( isset( $demo_data['colorway-custom-404'] ) ) {
				if ( is_callable( 'Colorway_Admin_Helper::update_admin_settings_option' ) ) {
					$options_404 = $demo_data['colorway-custom-404'];
					Colorway_Admin_Helper::update_admin_settings_option( '_colorway_ext_custom_404', $options_404 );
				}
			}
		}

		/**
		 * Import settings enabled  ColorWay extensions from the demo.
		 *
		 * @since  1.0.0
		 * @param  array $demo_data Site all data render from API call.
		 * @param  array $demo_api_uri Demo URL.
		 */
		public function import_enabled_extension( $demo_data = array(), $demo_api_uri = '' ) {

			if ( isset( $demo_data['colorway-enabled-extensions'] ) ) {
				if ( is_callable( 'Colorway_Admin_Helper::update_admin_settings_option' ) ) {
					Colorway_Admin_Helper::update_admin_settings_option( '_colorway_ext_enabled_extensions', $demo_data['colorway-enabled-extensions'] );
				}
			}
		}

		/**
		 * Start post meta mapping of  ColorWay Addon
		 *
		 * @since 1.1.6
		 *
		 * @return null     If there is no import option data found.
		 */
		public static function start_post_mapping() {
			$demo_data = get_option( 'colorway_sites_import_data', array() );
			if ( ! isset( $demo_data['colorway-post-data-mapping'] ) ) {
				return;
			}

			$post_type = 'colorway-advanced-hook';
			$posts     = ( isset( $demo_data['colorway-post-data-mapping'][ $post_type ] ) ) ? $demo_data['colorway-post-data-mapping'][ $post_type ] : array();
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					$page = get_page_by_title( $post['post_title'], OBJECT, $post_type );
					if ( is_object( $page ) ) {
						self::update_location_rules( $page->ID, 'ast-advanced-hook-location', $post['mapping']['ast-advanced-hook-location'] );
					}
				}
			}

			$post_type = 'colorway_adv_header';
			$posts     = ( isset( $demo_data['colorway-post-data-mapping'][ $post_type ] ) ) ? $demo_data['colorway-post-data-mapping'][ $post_type ] : array();
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					$page = get_page_by_title( $post['post_title'], OBJECT, $post_type );
					if ( is_object( $page ) ) {

						self::update_location_rules( $page->ID, 'ast-advanced-headers-location', $post['mapping']['ast-advanced-headers-location'] );
						self::update_location_rules( $page->ID, 'ast-advanced-headers-exclusion', $post['mapping']['ast-advanced-headers-exclusion'] );
						self::update_header_mapping( $page->ID, 'ast-advanced-headers-design', $post['mapping']['ast-advanced-headers-design'] );
					}
				}
			}
		}

		/**
		 * Update Header Mapping Data
		 *
		 * @since 1.1.6
		 *
		 * @param  int    $post_id     Post ID.
		 * @param  string $meta_key Post meta key.
		 * @param  array  $mapping  Mapping array.
		 * @return void
		 */
		public static function update_header_mapping( $post_id = '', $meta_key = '', $mapping = array() ) {
			Colorway_Sites_Image_Importer::log( 'Mapping "' . $meta_key . '" for ' . $post_id );

			$headers_old = get_post_meta( $post_id, $meta_key, true );
			$headers_new = self::get_header_mapping( $headers_old, $mapping );
			update_post_meta( $post_id, $meta_key, $headers_new );
		}

		/**
		 * Update Location Rules
		 *
		 * @since 1.1.6
		 *
		 * @param  int    $post_id     Post ID.
		 * @param  string $meta_key Post meta key.
		 * @param  array  $mapping  Mapping array.
		 * @return void
		 */
		public static function update_location_rules( $post_id = '', $meta_key = '', $mapping = array() ) {
			Colorway_Sites_Image_Importer::log( 'Mapping "' . $meta_key . '" for ' . $post_id );

			$location_new = self::get_location_mappings( $mapping );
			update_post_meta( $post_id, $meta_key, $location_new );
		}

		/**
		 * Get mapping locations.
		 *
		 * @since 1.1.6
		 *
		 * @param  array $location Location data.
		 * @return array            Location mapping data.
		 */
		public static function get_location_mappings( $location = array() ) {
			if ( empty( $location ) ) {
				return $location;
			}

			if ( ! isset( $location['specific'] ) || empty( $location['specific'] ) ) {
				return $location;
			}

			$mapping = array();

			if ( isset( $location['specific']['post'] ) ) {
				foreach ( $location['specific']['post'] as $post_type => $old_post_data ) {
					if ( is_array( $old_post_data ) ) {
						foreach ( $old_post_data as $post_key => $post ) {
							if ( $post_object = get_page_by_path( $post['slug'] ) ) {
								$mapping[] = 'post-' . absint( $post_object->ID );
							}
						}
					}
				}
			}

			if ( isset( $location['specific']['tax'] ) ) {
				foreach ( $location['specific']['tax'] as $taxonomy_type => $old_term_data ) {
					if ( is_array( $old_term_data ) ) {
						foreach ( $old_term_data as $term_key => $term_data ) {
							$term = get_term_by( 'slug', $term_data['slug'], $taxonomy_type );
							if ( is_object( $term ) ) {
								$mapping[] = 'tax-' . absint( $term->term_id );
							}
						}
					}
				}
			}

			$location['specific'] = $mapping;

			return $location;
		}

		/**
		 * Get advanced header mapping data
		 *
		 * @since 1.1.6
		 *
		 * @param  array $headers_old  Header mapping stored data.
		 * @param  array $headers_data Header mapping data.
		 * @return array                Filtered header mapping data.
		 */
		public static function get_header_mapping( $headers_old = array(), $headers_data = array() ) {

			// Set menu location by menu slug.
			if ( isset( $headers_data['menus'] ) && ! empty( $headers_data['menus'] ) ) {
				foreach ( $headers_data['menus'] as $header_option_name => $menu_data ) {
					$term = get_term_by( 'slug', $menu_data['slug'], 'nav_menu' );
					if ( is_object( $term ) ) {
						$headers_old[ $header_option_name ] = $term->term_id;
					}
				}
			}

			// Set image ID & URL after importing these on website.
			if ( isset( $headers_data['images'] ) && ! empty( $headers_data['images'] ) ) {
				foreach ( $headers_data['images'] as $key => $image_data ) {
					if ( isset( $image_data['image'] ) && ! empty( $image_data['image'] ) ) {
						$downloaded_image = Colorway_Sites_Image_Importer::get_instance()->import( $image_data['image'] );

						$headers_old[ $image_data['key_map']['url'] ] = $downloaded_image['url'];
						$headers_old[ $image_data['key_map']['id'] ]  = $downloaded_image['id'];
					}
				}
			}

			return $headers_old;
		}

		/**
		 * Clear Cache
		 *
		 * @since 1.2.3
		 * @return void
		 */
		function clear_cache() {
			if ( is_callable( 'Colorway_Minify::refresh_assets' ) ) {
				Colorway_Minify::refresh_assets();
			}
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_Compatibility_Colorway_Pro::get_instance();

endif;
