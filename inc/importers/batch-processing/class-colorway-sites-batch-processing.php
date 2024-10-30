<?php
/**
 * Batch Processing
 *
 * @package Colorway Sites
 * @since 1.0.14
 */

if ( ! class_exists( 'Colorway_Sites_Batch_Processing' ) ) :

	/**
	 * Colorway_Sites_Batch_Processing
	 *
	 * @since 1.0.14
	 */
	class Colorway_Sites_Batch_Processing {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Process All
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access public
		 */
		public static $process_all;

		/**
		 * Initiator
		 *
		 * @since 1.0.14
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
		 * @since 1.0.14
		 */
		public function __construct() {

			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Image Downloader.
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/helpers/class-colorway-sites-image-importer.php';

			// Core Helpers - Batch Processing.
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-async-request.php';
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process.php';
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process-colorway.php';

			// Prepare Widgets.
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing-widgets.php';

			// Prepare Page Builders.
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing-beaver-builder.php';

			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.0.0-beta1', '>=' ) ) {
				require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing-elementor-v2.php';
			} else {
				require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing-elementor.php';
			}

			// Prepare Misc.
			require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing-misc.php';

			self::$process_all = new WP_Background_Process_Colorway();

			// Start image importing after site import complete.
			add_filter( 'colorway_sites_image_importer_skip_image', array( $this, 'skip_image' ), 10, 2 );
			add_action( 'colorway_sites_import_complete', array( $this, 'start_process' ) );
		}

		/**
		 * Skip Image from Batch Processing.
		 *
		 * @since 1.0.14
		 *
		 * @param  boolean $can_process Batch process image status.
		 * @param  array   $attachment  Batch process image input.
		 * @return boolean
		 */
		function skip_image( $can_process, $attachment ) {

			if ( isset( $attachment['url'] ) && ! empty( $attachment['url'] ) ) {
				if (
					strpos( $attachment['url'], 'inkthemes.com' ) !== false ||
					strpos( $attachment['url'], 'wpcolorway.com' ) !== false ||
					strpos( $attachment['url'], 'sharkz.in' ) !== false ||
					strpos( $attachment['url'], 'inkthemesdemos.com' ) !== false
				) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.14
		 *
		 * @return void
		 */
		public function start_process() {

			Colorway_Sites_Image_Importer::log( '=================== ' . Colorway_Sites_White_Label::get_instance()->page_title( COLORWAY_SITES_NAME ) . ' - Importing Images for Blog name \'' . get_bloginfo( 'name' ) . '\' (' . get_current_blog_id() . ') ===================' );

			// Add "widget" in import [queue].
			if ( class_exists( 'Colorway_Sites_Batch_Processing_Widgets' ) ) {
				self::$process_all->push_to_queue( Colorway_Sites_Batch_Processing_Widgets::get_instance() );
			}

			// Add "bb-plugin" in import [queue].
			// Add "beaver-builder-lite-version" in import [queue].
			if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
				if ( class_exists( 'Colorway_Sites_Batch_Processing_Beaver_Builder' ) ) {
					self::$process_all->push_to_queue( Colorway_Sites_Batch_Processing_Beaver_Builder::get_instance() );
				}
			}

			// Add "elementor" in import [queue].
			// @todo Remove required `allow_url_fopen` support.
			if ( ini_get( 'allow_url_fopen' ) ) {
				if ( is_plugin_active( 'elementor/elementor.php' ) ) {
					if ( class_exists( '\Elementor\TemplateLibrary\Colorway_Sites_Batch_Processing_Elementor' ) ) {
						$import = new \Elementor\TemplateLibrary\Colorway_Sites_Batch_Processing_Elementor();
						self::$process_all->push_to_queue( $import );
					}
				}
			} else {
				Colorway_Sites_Image_Importer::log( 'Couldn\'t not import image due to allow_url_fopen() is disabled!' );
			}

			// Add "colorway-addon" in import [queue].
			if ( is_plugin_active( 'colorway-addons/colorway-addons.php' ) ) {
				if ( class_exists( 'Colorway_Sites_Compatibility_Colorway_Pro' ) ) {
					self::$process_all->push_to_queue( Colorway_Sites_Compatibility_Colorway_Pro::get_instance() );
				}
			}

			// Add "misc" in import [queue].
			if ( class_exists( 'Colorway_Sites_Batch_Processing_Misc' ) ) {
				self::$process_all->push_to_queue( Colorway_Sites_Batch_Processing_Misc::get_instance() );
			}

			// Dispatch Queue.
			self::$process_all->save()->dispatch();
		}

		/**
		 * Get all post id's
		 *
		 * @since 1.0.14
		 *
		 * @return array
		 */
		public static function get_pages() {

			$args = array(
				'post_type'     => 'any',

				// Query performance optimization.
				'fields'        => 'ids',
				'no_found_rows' => true,
				'post_status'   => 'publish',
			);

			$query = new WP_Query( $args );

			// Have posts?
			if ( $query->have_posts() ) :

				return $query->posts;

			endif;
			return null;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_Batch_Processing::get_instance();

endif;
