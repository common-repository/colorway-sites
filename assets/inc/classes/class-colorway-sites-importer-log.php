<?php
/**
 * ColorWay Sites Importer Log
 *
 * @since 1.1.0
 * @package ColorWay Sites
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Colorway_Sites_Importer_Log' ) ) :

	/**
	 * ColorWay Sites Importer
	 */
	class Colorway_Sites_Importer_Log {

		/**
		 * Instance
		 *
		 * @since 1.1.0
		 * @var (Object) Class object
		 */
		private static $_instance = null;

		/**
		 * Log File
		 *
		 * @since 1.1.0
		 * @var (Object) Class object
		 */
		private static $log_file = null;

		/**
		 * Set Instance
		 *
		 * @since 1.1.0
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.1.0
		 */
		private function __construct() {

			// Check file read/write permissions.
			add_action( 'admin_init', array( $this, 'has_file_read_write' ) );

		}

		/**
		 * Check file read/write permissions and process.
		 *
		 * @since 1.1.0
		 * @return null
		 */
		function has_file_read_write() {

			// Get user credentials for WP file-system API.
			$colorway_sites_import = wp_nonce_url( admin_url( 'themes.php?page=colorway-sites' ), 'colorway-import' );
			if ( false === ( $creds = request_filesystem_credentials( $colorway_sites_import, '', false, false, null ) ) ) {
				return;
			}

			// Set log file.
			self::set_log_file();

			// Initial AJAX Import Hooks.
			add_action( 'colorway_sites_import_start', array( $this, 'start' ), 10, 2 );
			add_action( 'colorway_sites_import_customizer_settings', array( $this, 'start_customizer' ) );
			add_action( 'colorway_sites_import_prepare_xml_data', array( $this, 'start_xml' ) );
			add_action( 'colorway_sites_import_options', array( $this, 'start_options' ) );
			add_action( 'colorway_sites_import_widgets', array( $this, 'start_widgets' ) );
			add_action( 'colorway_sites_import_complete', array( $this, 'start_end' ) );

			// Hooks in between the process of import.
			add_filter( 'wie_import_results', array( $this, 'widgets_data' ) );
			add_action( 'colorway_sites_import_xml_log', array( $this, 'xml_log' ), 10, 3 );
		}

		/**
		 * Add log file URL in UI response.
		 *
		 * @since 1.1.0
		 */
		public static function add_log_file_url() {

			$upload_dir   = self::log_dir();
			$upload_path  = trailingslashit( $upload_dir['url'] );
			$file_abs_url = get_option( 'colorway_sites_recent_import_log_file', self::$log_file );
			$file_url     = $upload_path . basename( $file_abs_url );

			return array(
				'abs_url' => $file_abs_url,
				'url'     => $file_url,
			);
		}

		/**
		 * XML Log.
		 *
		 * @since 1.1.0
		 * @param  string $level   Level (Debug, Info etc.).
		 * @param  string $message Message.
		 * @param  string $context Context.
		 * @return void
		 */
		function xml_log( $level = '', $message = '', $context = '' ) {
			Colorway_Sites_Importer_Log::add( $message );
		}

		/**
		 * Current Time for log.
		 *
		 * @since 1.1.0
		 * @return string Current time with time zone.
		 */
		public static function current_time() {
			return date( 'H:i:s' ) . ' ' . date_default_timezone_get();
		}

		/**
		 * Import Start
		 *
		 * @since 1.1.0
		 * @param  array  $data         Import Data.
		 * @param  string $demo_api_uri Import site API URL.
		 * @return void
		 */
		function start( $data = array(), $demo_api_uri = '' ) {

			Colorway_Sites_Importer_Log::add( '# System Details: ' );
			Colorway_Sites_Importer_Log::add( "Debug Mode \t\t: " . self::get_debug_mode() );
			Colorway_Sites_Importer_Log::add( "Operating System \t: " . self::get_os() );
			Colorway_Sites_Importer_Log::add( "Software \t\t: " . self::get_software() );
			Colorway_Sites_Importer_Log::add( "MySQL version \t\t: " . self::get_mysql_version() );
			Colorway_Sites_Importer_Log::add( "PHP Version \t\t: " . self::get_php_version() );
			Colorway_Sites_Importer_Log::add( "PHP Max Input Vars \t: " . self::get_php_max_input_vars() );
			Colorway_Sites_Importer_Log::add( "PHP Max Post Size \t: " . self::get_php_max_post_size() );
			Colorway_Sites_Importer_Log::add( "PHP Extension GD \t: " . self::get_php_extension_gd() );
			Colorway_Sites_Importer_Log::add( "PHP Max Execution Time \t: " . self::get_max_execution_time() );
			Colorway_Sites_Importer_Log::add( "Max Upload Size \t: " . size_format( wp_max_upload_size() ) );
			Colorway_Sites_Importer_Log::add( "Memory Limit \t\t: " . self::get_memory_limit() );
			Colorway_Sites_Importer_Log::add( "Timezone \t\t: " . self::get_timezone() );
			Colorway_Sites_Importer_Log::add( PHP_EOL . '-----' . PHP_EOL );
			Colorway_Sites_Importer_Log::add( 'Importing Started! - ' . self::current_time() );

			Colorway_Sites_Importer_Log::add( '---' . PHP_EOL );
			Colorway_Sites_Importer_Log::add( 'WHY IMPORT PROCESS CAN FAIL? READ THIS - ' );
			Colorway_Sites_Importer_Log::add( 'https://wpcolorway.com/docs/?p=1314&utm_source=demo-import-panel&utm_campaign=import-error&utm_medium=wp-dashboard' . PHP_EOL );
			Colorway_Sites_Importer_Log::add( '---' . PHP_EOL );

		}

		/**
		 * Start Customizer Import
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function start_customizer() {
			Colorway_Sites_Importer_Log::add( PHP_EOL . '1. Imported "Customizer Settings"  - ' . self::current_time() );
			Colorway_Sites_Importer_Log::add( PHP_EOL . '---' );
		}

		/**
		 * Start XML Import
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function start_xml() {
			Colorway_Sites_Importer_Log::add( PHP_EOL . '2. Importing "XML"  - ' . self::current_time() );
		}

		/**
		 * Start Options Import
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function start_options() {
			Colorway_Sites_Importer_Log::add( PHP_EOL . '---' );
			Colorway_Sites_Importer_Log::add( PHP_EOL . '3. Imported "Site Options"  - ' . self::current_time() );
			Colorway_Sites_Importer_Log::add( PHP_EOL . '---' );
		}

		/**
		 * Start Widgets Import
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function start_widgets() {
			Colorway_Sites_Importer_Log::add( PHP_EOL . '4. Importing "Widgets"  - ' . self::current_time() );
		}

		/**
		 * End Import Process
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function start_end() {
			Colorway_Sites_Importer_Log::add( PHP_EOL . '---' );
			Colorway_Sites_Importer_Log::add( PHP_EOL . 'Import Complete!  - ' . self::current_time() );

			// Delete Log file.
			delete_option( 'colorway_sites_recent_import_log_file' );
		}

		/**
		 * Log Widget Import Data.
		 *
		 * @since 1.1.0
		 * @param  array $results Widget import info in array.
		 * @return void
		 */
		function widgets_data( $results = array() ) {

			if ( is_array( $results ) ) {
				foreach ( $results as $sidebar_key => $widgets ) {
					Colorway_Sites_Importer_Log::add( 'Sidebar: ' . $sidebar_key );
					foreach ( $widgets['widgets'] as $widget_key => $widget ) {
						if ( isset( $widget['name'] ) && isset( $widget['message'] ) ) {
							Colorway_Sites_Importer_Log::add( 'Widget: "' . $widget['name'] . '" - ' . $widget['message'] );
						}
					}
				}
			}
		}

		/**
		 * Get an instance of WP_Filesystem_Direct.
		 *
		 * @since 1.1.0
		 * @return object A WP_Filesystem_Direct instance.
		 */
		static public function get_filesystem() {
			global $wp_filesystem;

			require_once ABSPATH . '/wp-admin/includes/file.php';

			WP_Filesystem();

			return $wp_filesystem;
		}

		/**
		 * Get Log File
		 *
		 * @since 1.1.0
		 * @return string log file URL.
		 */
		public static function get_log_file() {
			return self::$log_file;
		}

		/**
		 * Log file directory
		 *
		 * @since 1.1.0
		 * @param  string $dir_name Directory Name.
		 * @return array    Uploads directory array.
		 */
		public static function log_dir( $dir_name = 'colorway-sites' ) {

			$upload_dir = wp_upload_dir();

			// Build the paths.
			$dir_info = array(
				'path' => $upload_dir['basedir'] . '/' . $dir_name . '/',
				'url'  => $upload_dir['baseurl'] . '/' . $dir_name . '/',
			);

			// Create the upload dir if it doesn't exist.
			if ( ! file_exists( $dir_info['path'] ) ) {

				// Create the directory.
				wp_mkdir_p( $dir_info['path'] );

				// Add an index file for security.
				self::get_filesystem()->put_contents( $dir_info['path'] . 'index.html', '' );
			}

			return $dir_info;
		}

		/**
		 * Set log file
		 *
		 * @since 1.1.0
		 */
		public static function set_log_file() {

			$upload_dir = self::log_dir();

			$upload_path = trailingslashit( $upload_dir['path'] );

			// File format e.g. 'import-31-Oct-2017-06-39-12.txt'.
			self::$log_file = $upload_path . 'import-' . date( 'd-M-Y-h-i-s' ) . '.txt';

			if ( ! get_option( 'colorway_sites_recent_import_log_file', false ) ) {
				update_option( 'colorway_sites_recent_import_log_file', self::$log_file );
			}
		}

		/**
		 * Write content to a file.
		 *
		 * @since 1.1.0
		 * @param string $content content to be saved to the file.
		 */
		public static function add( $content ) {

			if ( get_option( 'colorway_sites_recent_import_log_file', false ) ) {
				$log_file = get_option( 'colorway_sites_recent_import_log_file', self::$log_file );
			} else {
				$log_file = self::$log_file;
			}

			$existing_data = '';
			if ( file_exists( $log_file ) ) {
				$existing_data = self::get_filesystem()->get_contents( $log_file );
			}

			// Style separator.
			$separator = PHP_EOL;

			self::get_filesystem()->put_contents( $log_file, $existing_data . $separator . $content, FS_CHMOD_FILE );
		}

		/**
		 * Debug Mode
		 *
		 * @since 1.1.0
		 * @return string Enabled for Debug mode ON and Disabled for Debug mode Off.
		 */
		public static function get_debug_mode() {
			if ( WP_DEBUG ) {
				return __( 'Enabled', 'colorway-sites' );
			}

			return __( 'Disabled', 'colorway-sites' );
		}

		/**
		 * Memory Limit
		 *
		 * @since 1.1.0
		 * @return string Memory limit.
		 */
		public static function get_memory_limit() {

			$required_memory                = '64M';
			$memory_limit_in_bytes_current  = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
			$memory_limit_in_bytes_required = wp_convert_hr_to_bytes( $required_memory );

			if ( $memory_limit_in_bytes_current < $memory_limit_in_bytes_required ) {
				return sprintf(
					/* translators: %1$s Memory Limit, %2$s Recommended memory limit. */
					_x( 'Current memory limit %1$s. We recommend setting memory to at least %2$s.', 'Recommended Memory Limit', 'colorway-sites' ),
					WP_MEMORY_LIMIT,
					$required_memory
				);
			}

			return WP_MEMORY_LIMIT;
		}

		/**
		 * Timezone
		 *
		 * @since 1.1.0
		 * @see https://codex.wordpress.org/Option_Reference/
		 *
		 * @return string Current timezone.
		 */
		public static function get_timezone() {
			$timezone = get_option( 'timezone_string' );

			if ( ! $timezone ) {
				return get_option( 'gmt_offset' );
			}

			return $timezone;
		}

		/**
		 * Operating System
		 *
		 * @since 1.1.0
		 * @return string Current Operating System.
		 */
		public static function get_os() {
			return PHP_OS;
		}

		/**
		 * Server Software
		 *
		 * @since 1.1.0
		 * @return string Current Server Software.
		 */
		public static function get_software() {
			return $_SERVER['SERVER_SOFTWARE'];
		}

		/**
		 * MySql Version
		 *
		 * @since 1.1.0
		 * @return string Current MySql Version.
		 */
		public static function get_mysql_version() {
			global $wpdb;
			return $wpdb->db_version();
		}

		/**
		 * PHP Version
		 *
		 * @since 1.1.0
		 * @return string Current PHP Version.
		 */
		public static function get_php_version() {
			if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
				return _x( 'We recommend to use php 5.4 or higher', 'PHP Version', 'colorway-sites' );
			}
			return PHP_VERSION;
		}

		/**
		 * PHP Max Input Vars
		 *
		 * @since 1.1.0
		 * @return string Current PHP Max Input Vars
		 */
		public static function get_php_max_input_vars() {
			return ini_get( 'max_input_vars' );
		}

		/**
		 * PHP Max Post Size
		 *
		 * @since 1.1.0
		 * @return string Current PHP Max Post Size
		 */
		public static function get_php_max_post_size() {
			return ini_get( 'post_max_size' );
		}

		/**
		 * PHP Max Execution Time
		 *
		 * @since 1.1.0
		 * @return string Current Max Execution Time
		 */
		public static function get_max_execution_time() {
			return ini_get( 'max_execution_time' );
		}

		/**
		 * PHP GD Extension
		 *
		 * @since 1.1.0
		 * @return string Current PHP GD Extension
		 */
		public static function get_php_extension_gd() {
			if ( extension_loaded( 'gd' ) ) {
				return __( 'Yes', 'colorway-sites' );
			}

			return __( 'No', 'colorway-sites' );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Colorway_Sites_Importer_Log::get_instance();

endif;
