<?php
/**
 * Batch Processing
 *
 * @package ColorWay Sites
 * @since 1.0.14
 */

if ( ! class_exists( 'Colorway_Sites_Batch_Processing_Widgets' ) ) :

	/**
	 * Colorway_Sites_Batch_Processing_Widgets
	 *
	 * @since 1.0.14
	 */
	class Colorway_Sites_Batch_Processing_Widgets {

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
			$this->widget_media_image();
		}

		/**
		 * Widget Media Image
		 *
		 * @since 1.0.14
		 * @return void
		 */
		public function widget_media_image() {

			$data = get_option( 'widget_media_image', null );

			Colorway_Sites_Image_Importer::log( '---- Processing Images from Widgets -----' );

			foreach ( $data as $key => $value ) {

				if (
					isset( $value['url'] ) &&
					isset( $value['attachment_id'] )
				) {

					$image = array(
						'url' => $value['url'],
						'id'  => $value['attachment_id'],
					);

					$downloaded_image = Colorway_Sites_Image_Importer::get_instance()->import( $image );

					$data[ $key ]['url']           = $downloaded_image['url'];
					$data[ $key ]['attachment_id'] = $downloaded_image['id'];
				}
			}

			update_option( 'widget_media_image', $data );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_Batch_Processing_Widgets::get_instance();

endif;
