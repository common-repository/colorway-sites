<?php
/**
 * Colorway Sites White Label
 *
 * @package Colorway Sites
 * @since 1.0.12
 */

if ( ! class_exists( 'Colorway_Sites_White_Label' ) ) :

	/**
	 * Colorway_Sites_White_Label
	 *
	 * @since 1.0.12
	 */
	class Colorway_Sites_White_Label {

		/**
		 * Instance
		 *
		 * @since 1.0.12
		 *
		 * @var object Class Object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @since 1.0.12
		 *
		 * @var array branding
		 * @access private
		 */
		private static $branding;

		/**
		 * Initiator
		 *
		 * @since 1.0.12
		 *
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
		 * @since 1.0.12
		 */
		public function __construct() {

			add_filter( 'all_plugins', array( $this, 'plugins_page' ) );
			add_filter( 'colorway_addon_branding_options', __CLASS__ . '::settings' );
			add_action( 'colorway_pro_white_label_add_form', __CLASS__ . '::add_white_lavel_form' );
			add_filter( 'colorway_sites_menu_page_title', array( $this, 'page_title' ) );

			// Display the link with the plugin meta.
			if ( is_admin() ) {
				add_filter( 'plugin_row_meta', array( $this, 'plugin_links' ), 10, 4 );
			}
		}

		/**
		 * White labels the plugins page.
		 *
		 * @since 1.0.12
		 *
		 * @param array $plugins Plugins Array.
		 * @return array
		 */
		function plugins_page( $plugins ) {

			if ( ! is_callable( 'Colorway_Ext_White_Label_Markup::get_white_label' ) ) {
				return $plugins;
			}

			if ( ! isset( $plugins[ COLORWAY_SITES_BASE ] ) ) {
				return $plugins;
			}

			// Set White Labels.
			$name        = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'name' );
			$description = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'description' );
			$author      = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-agency', 'author' );
			$author_uri  = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-agency', 'author_url' );

			if ( ! empty( $name ) ) {
				$plugins[ COLORWAY_SITES_BASE ]['Name'] = $name;

				// Remove Plugin URI if Agency White Label name is set.
				$plugins[ COLORWAY_SITES_BASE ]['PluginURI'] = '';
			}

			if ( ! empty( $description ) ) {
				$plugins[ COLORWAY_SITES_BASE ]['Description'] = $description;
			}

			if ( ! empty( $author ) ) {
				$plugins[ COLORWAY_SITES_BASE ]['Author'] = $author;
			}

			if ( ! empty( $author_uri ) ) {
				$plugins[ COLORWAY_SITES_BASE ]['AuthorURI'] = $author_uri;
			}

			return $plugins;
		}

		/**
		 * Remove a "view details" link from the plugin list table
		 *
		 * @since 1.0.12
		 *
		 * @param array  $plugin_meta  List of links.
		 * @param string $plugin_file Relative path to the main plugin file from the plugins directory.
		 * @param array  $plugin_data  Data from the plugin headers.
		 * @return array
		 */
		public function plugin_links( $plugin_meta, $plugin_file, $plugin_data ) {

			if ( ! is_callable( 'Colorway_Ext_White_Label_Markup::get_white_label' ) ) {
				return $plugin_meta;
			}

			// Set White Labels.
			if ( COLORWAY_SITES_BASE == $plugin_file ) {

				$name        = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'name' );
				$description = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'description' );

				// Remove Plugin URI if Agency White Label name is set.
				if ( ! empty( $name ) ) {
					unset( $plugin_meta[2] );
				}
			}

			return $plugin_meta;
		}

		/**
		 * Add White Label setting's
		 *
		 * @since 1.0.12
		 *
		 * @param  array $settings White label setting.
		 * @return array
		 */
		public static function settings( $settings = array() ) {

			$settings['colorway-sites'] = array(
				'name'        => '',
				'description' => '',
			);

			return $settings;
		}

		/**
		 * Add White Label form
		 *
		 * @since 1.0.12
		 *
		 * @param  array $settings White label setting.
		 * @return void
		 */
		public static function add_white_lavel_form( $settings = array() ) {

			/* translators: %1$s product name */
			$plugin_name = sprintf( __( '%1$s Branding', 'colorway-sites' ), COLORWAY_SITES_NAME );

			require_once COLORWAY_SITES_DIR . 'inc/includes/white-label.php';
		}

		/**
		 * Page Title
		 *
		 * @since 1.0.12
		 *
		 * @param  string $title Page Title.
		 * @return string        Filtered Page Title.
		 */
		function page_title( $title ) {
			$get_white_labels = 'Colorway_Ext_White_Label_Markup::get_white_labels';
			if ( is_callable( $get_white_labels ) ) {
				$colorway_sites_name = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'name' );
				if ( ! empty( $colorway_sites_name ) ) {
					$title = Colorway_Ext_White_Label_Markup::get_white_label( 'colorway-sites', 'name' );
				}
			}

			return $title;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_White_Label::get_instance();

endif;
