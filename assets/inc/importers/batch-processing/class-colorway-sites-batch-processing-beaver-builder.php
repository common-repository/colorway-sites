<?php
/**
 * Batch Processing
 *
 * @package ColorWay Sites
 * @since 1.0.14
 */

if ( ! class_exists( 'Colorway_Sites_Batch_Processing_Beaver_Builder' ) ) :

	/**
	 * Colorway_Sites_Batch_Processing_Beaver_Builder
	 *
	 * @since 1.0.14
	 */
	class Colorway_Sites_Batch_Processing_Beaver_Builder {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

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
		}

		/**
		 * Import
		 *
		 * @since 1.0.14
		 * @return void
		 */
		public function import() {

			Colorway_Sites_Image_Importer::log( '---- Processing WordPress Posts / Pages - for Beaver Builder ----' );

			$post_ids = Colorway_Sites_Batch_Processing::get_pages();
			if ( is_array( $post_ids ) ) {
				foreach ( $post_ids as $post_id ) {
					$this->import_single_post( $post_id );
				}
			}
		}

		/**
		 * Update post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {

			Colorway_Sites_Image_Importer::log( 'Post ID: ' . $post_id );

			if ( ! empty( $post_id ) ) {

				// Get page builder data.
				$data = get_post_meta( $post_id, '_fl_builder_data', true );

				if ( ! empty( $data ) ) {
					foreach ( $data as $key => $el ) {

						// Import 'row' images.
						if ( 'row' === $el->type ) {
							$data[ $key ]->settings = self::import_row_images( $el->settings );
						}

						// Import 'module' images.
						if ( 'module' === $el->type ) {
							$data[ $key ]->settings = self::import_module_images( $el->settings );
						}

						// Import 'column' images.
						if ( 'column' === $el->type ) {
							$data[ $key ]->settings = self::import_column_images( $el->settings );
						}
					}

					// Update page builder data.
					update_post_meta( $post_id, '_fl_builder_data', $data );
					update_post_meta( $post_id, '_fl_builder_draft', $data );

					// Clear all cache.
					FLBuilderModel::delete_asset_cache_for_all_posts();
				}
			}
		}

		/**
		 * Import Module Images.
		 *
		 * @param  object $settings Module settings object.
		 * @return object
		 */
		public static function import_module_images( $settings ) {

			/**
			 * 1) Set photos.
			 */
			$settings = self::import_photo( $settings );

			/**
			 * 2) Set `$settings->data` for Only type 'image-icon'
			 *
			 * @todo Remove the condition `'image-icon' === $settings->type` if `$settings->data` is used only for the Image Icon.
			 */
			if (
				isset( $settings->data ) &&
				isset( $settings->photo ) && ! empty( $settings->photo ) &&
				'image-icon' === $settings->type
			) {
				$settings->data = FLBuilderPhoto::get_attachment_data( $settings->photo );
			}

			/**
			 * 3) Set `list item` module images
			 */
			if ( isset( $settings->add_list_item ) ) {
				foreach ( $settings->add_list_item as $key => $value ) {
					$settings->add_list_item[ $key ] = self::import_photo( $value );
				}
			}

			return $settings;
		}

		/**
		 * Import Column Images.
		 *
		 * @param  object $settings Column settings object.
		 * @return object
		 */
		public static function import_column_images( $settings ) {

			// 1) Set BG Images.
			$settings = self::import_bg_image( $settings );

			return $settings;
		}

		/**
		 * Import Row Images.
		 *
		 * @param  object $settings Row settings object.
		 * @return object
		 */
		public static function import_row_images( $settings ) {

			// 1) Set BG Images.
			$settings = self::import_bg_image( $settings );

			return $settings;
		}

		/**
		 * Helper: Import BG Images.
		 *
		 * @param  object $settings Row settings object.
		 * @return object
		 */
		public static function import_bg_image( $settings ) {

			if (
				( ! empty( $settings->bg_image ) && ! empty( $settings->bg_image_src ) )
			) {
				$image = array(
					'url' => $settings->bg_image_src,
					'id'  => $settings->bg_image,
				);

				$downloaded_image = Colorway_Sites_Image_Importer::get_instance()->import( $image );

				$settings->bg_image_src = $downloaded_image['url'];
				$settings->bg_image     = $downloaded_image['id'];
			}

			return $settings;
		}

		/**
		 * Helper: Import Photo.
		 *
		 * @param  object $settings Row settings object.
		 * @return object
		 */
		public static function import_photo( $settings ) {

			if ( ! empty( $settings->photo ) && ! empty( $settings->photo_src ) ) {

				$image = array(
					'url' => $settings->photo_src,
					'id'  => $settings->photo,
				);

				$downloaded_image = Colorway_Sites_Image_Importer::get_instance()->import( $image );

				$settings->photo_src = $downloaded_image['url'];
				$settings->photo     = $downloaded_image['id'];
			}

			return $settings;
		}


	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_Batch_Processing_Beaver_Builder::get_instance();

endif;


