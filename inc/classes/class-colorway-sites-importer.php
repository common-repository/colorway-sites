<?php

/**
 * Colorway Sites Importer
 *
 * @since  1.0.0
 * @package Colorway Sites
 */
defined('ABSPATH') or exit;

if (!class_exists('Colorway_Sites_Importer')) :

    /**
     * Colorway Sites Importer
     */
    class Colorway_Sites_Importer {

        /**
         * Instance
         *
         * @since  1.0.0
         * @var (Object) Class object
         */
        public static $_instance = null;

        /**
         * Set Instance
         *
         * @since  1.0.0
         *
         * @return object Class object.
         */
        public static function get_instance() {
            if (!isset(self::$_instance)) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }

        /**
         * Constructor.
         *
         * @since  1.0.0
         */
        public function __construct() {

            require_once COLORWAY_SITES_DIR . 'inc/classes/class-colorway-sites-importer-log.php';
            require_once COLORWAY_SITES_DIR . 'inc/importers/class-colorway-sites-helper.php';
            require_once COLORWAY_SITES_DIR . 'inc/importers/class-widgets-importer.php';
            require_once COLORWAY_SITES_DIR . 'inc/importers/class-colorway-customizer-import.php';
            require_once COLORWAY_SITES_DIR . 'inc/importers/class-colorway-site-options-import.php';

            // Import AJAX.
            add_action('wp_ajax_colorway-sites-import-set-site-data', array($this, 'import_start'));
            add_action('wp_ajax_colorway-sites-import-customizer-settings', array($this, 'import_customizer_settings'));
            add_action('wp_ajax_colorway-sites-import-prepare-xml', array($this, 'prepare_xml_data'));
            add_action('wp_ajax_colorway-sites-import-options', array($this, 'import_options'));
            add_action('wp_ajax_colorway-sites-import-widgets', array($this, 'import_widgets'));
            add_action('wp_ajax_colorway-sites-import-end', array($this, 'import_end'));

            // Hooks in AJAX.
            add_action('colorway_sites_import_complete', array($this, 'clear_cache'));
            add_action('init', array($this, 'load_importer'));
//            add_action('init', array($this, 'set_menu_location'));

            require_once COLORWAY_SITES_DIR . 'inc/importers/batch-processing/class-colorway-sites-batch-processing.php';

            add_action('colorway_sites_image_import_complete', array($this, 'clear_cache'));
        }

        /**
         * Load WordPress WXR importer.
         */
        public function load_importer() {
            require_once COLORWAY_SITES_DIR . 'inc/importers/wxr-importer/class-colorway-wxr-importer.php';
        }

        /**
         * Start Site Import
         *
         * @since 1.1.0
         * @return void
         */
        function import_start() {

            if (!current_user_can('customize')) {
                wp_send_json_error(__('You have not "customize" access to import the  Colorway site.', 'colorway-sites'));
            }

            $demo_api_uri = isset($_POST['api_url']) ? esc_url($_POST['api_url']) : '';

            if (!empty($demo_api_uri)) {

                $demo_data = self::get_colorway_single_demo($demo_api_uri);

                update_option('colorway_sites_import_data', $demo_data);

                if (is_wp_error($demo_data)) {
                    wp_send_json_error($demo_data->get_error_message());
                } else {
                    $log_file = Colorway_Sites_Importer_Log::add_log_file_url();
                    if (isset($log_file['abs_url']) && !empty($log_file['abs_url'])) {
                        $demo_data['log_file'] = $log_file['abs_url'];
                    }
                    do_action('colorway_sites_import_start', $demo_data, $demo_api_uri);
                }

                wp_send_json_success($demo_data);
            } else {
                wp_send_json_error(__('Request site API URL is empty. Try again!', 'colorway-sites'));
            }
        }

        /**
         * Import Customizer Settings.
         *
         * @since 1.0.14
         * @return void
         */
        function import_customizer_settings() {

            do_action('colorway_sites_import_customizer_settings');

            $customizer_data = ( isset($_POST['customizer_data']) ) ? (array) json_decode(stripcslashes($_POST['customizer_data']), 1) : '';

            if (isset($customizer_data)) {

                Colorway_Customizer_Import::instance()->import($customizer_data);
                wp_send_json_success(Colorway_Customizer_Import::instance()->import($customizer_data));
            } else {
                wp_send_json_error(__('Customizer data is empty!', 'colorway-sites'));
            }
        }

        /**
         * Prepare XML Data.
         *
         * @since 1.1.0
         * @return void
         */
        function prepare_xml_data() {

            do_action('colorway_sites_import_prepare_xml_data');
            if (!class_exists('XMLReader')) {
                wp_send_json_error(__('If XMLReader is not available, it imports all other settings and only skips XML import. This creates an incomplete website.', 'colorway-sites'));
            }
            $wxr_url = ( isset($_REQUEST['wxr_url']) ) ? urldecode($_REQUEST['wxr_url']) : '';

            if (isset($wxr_url)) {
                // Download XML file.
                $xml_path = Colorway_Sites_Helper::download_file($wxr_url);
                if ($xml_path['success']) {

                    if (isset($xml_path['data']['file'])) {
                        $data = Colorway_WXR_Importer::instance()->get_xml_data($xml_path['data']['file']);
                        $data['xml'] = $xml_path['data'];
                        //echo $data['xml'];                        
                        wp_send_json_success($data);
                    } else {
                        wp_send_json_error(__('There was an error downloading the XML file.', 'colorway-sites'));
                    }
                } else {
                    wp_send_json_error($xml_path['data']);
                }
            } else {
                wp_send_json_error(__('Invalid site XML file!', 'colorway-sites'));
            }
        }

        /**
         * Import Options.
         *
         * @since 1.0.14
         * @return void
         */
        function import_options() {

            do_action('colorway_sites_import_options');

            $options_data = ( isset($_POST['options_data']) ) ? (array) json_decode(stripcslashes($_POST['options_data']), 1) : '';

            if (isset($options_data)) {
                $options_importer = Colorway_Site_Options_Import::instance();
                $options_importer->import_options($options_data);
                wp_send_json_success($options_data);
            } else {
                wp_send_json_error(__('Site options are empty!', 'colorway-sites'));
            }
        }

        /**
         * Import Widgets.
         *
         * @since 1.0.14
         * @return void
         */
        function import_widgets() {

            do_action('colorway_sites_import_widgets');

            $widgets_data = ( isset($_POST['widgets_data']) ) ? (object) json_decode(stripcslashes($_POST['widgets_data'])) : '';

            if (isset($widgets_data)) {
                $widgets_importer = Colorway_Widget_Importer::instance();
                $status = $widgets_importer->import_widgets_data($widgets_data);
                wp_send_json_success($widgets_data);
            } else {
                wp_send_json_error(__('Widget data is empty!', 'colorway-sites'));
            }
        }

        /**
         * Import End.
         *
         * @since 1.0.14
         * @return void
         */
        function import_end() {
            do_action('colorway_sites_import_complete');
            $this->set_menu_location();
        }

        /*
         * Set Menu Location
         */

        function set_menu_location() {
            $menus = get_terms('nav_menu');
            foreach ($menus as $menu) {
                if ('main-menu' === $menu->slug) {
                    set_theme_mod('nav_menu_locations', array('custom_menu' => $menu->term_id));
                }
            }
        }

        /**
         * Get single demo.
         *
         * @since  1.0.0
         *
         * @param  (String) $demo_api_uri API URL of a demo.
         *
         * @return (Array) $colorway_demo_data demo data for the demo.
         */
        public static function get_colorway_single_demo($demo_api_uri) {

            // default values.
            $remote_args = array();
            $defaults = array(
                'id' => '',
                'site-widgets-data' => '',
                'site-customizer-data' => '',
                'site-options-data' => '',
                //'colorway-post-data-mapping'    => '',
                'site-wxr-path' => '',
                //'colorway-enabled-extensions'   => '',
                //'colorway-custom-404'           => '',
                'required-plugins' => '',
            );

            $api_args = apply_filters(
                    'colorway_sites_api_args', array(
                'timeout' => 15,
                    )
            );

            // Use this for premium demos.
            $request_params = apply_filters(
                    'colorway_sites_api_params', array(
                'purchase_key' => '',
                'site_url' => '',
                    )
            );

            $demo_api_uri = add_query_arg($request_params, $demo_api_uri);

            // API Call.
            $response = wp_remote_get($demo_api_uri, $api_args);

            if (is_wp_error($response) || ( isset($response->status) && 0 == $response->status )) {
                if (isset($response->status)) {
                    $data = json_decode($response, true);
                } else {
                    return new WP_Error('api_invalid_response_code', $response->get_error_message());
                }
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['code'])) {
                $remote_args['id'] = $data['id'];
                $remote_args['site-widgets-data'] = json_decode($data['acf']['site-widgets-data']);
                //$remote_args['site-customizer-data'] = file_get_contents($data['acf']['site-customizer-data'], true);
                // update_option('inkthemes_option', $remote_args['site-customizer-data']);
                $remote_args['site-customizer-data'] = unserialize(file_get_contents($data['acf']['site-customizer-data'], true));
                $remote_args['site-options-data'] = $data['acf']['site-options-data'];
                //$remote_args['colorway-post-data-mapping']    = $data['colorway-post-data-mapping'];
                $remote_args['site-wxr-path'] = $data['acf']['site-wxr-path'];
                //$remote_args['colorway-enabled-extensions']   = $data['colorway-enabled-extensions'];
                //$remote_args['colorway-custom-404']           = $data['colorway-custom-404'];
                $remote_args['required-plugins'] = $data['acf']['required-plugins'];
            }

            // Merge remote demo and defaults.
            return wp_parse_args($remote_args, $defaults);
        }

        /**
         * Clear Cache.
         *
         * @since  1.0.9
         */
        public function clear_cache() {
            // Clear 'Elementor' file cache.
            if (class_exists('\Elementor\Plugin')) {
                Elementor\Plugin::$instance->posts_css_manager->clear_cache();
            }

            // Clear 'Builder Builder' cache.
            if (is_callable('FLBuilderModel::delete_asset_cache_for_all_posts')) {
                FLBuilderModel::delete_asset_cache_for_all_posts();
            }

            // Clear ' Colorway Addon' cache.
            if (is_callable('Colorway_Minify::refresh_assets')) {
                Colorway_Minify::refresh_assets();
            }

            //Update Page Template of Home Page and Display Front Page Settings.             
            $homepage = get_page_by_title('Home', OBJECT, 'page');

            if ($homepage) {
                update_option('page_on_front', $homepage->ID);
                update_option('show_on_front', 'page');

                $x = get_post_meta($homepage->ID, '_wp_page_template', true);

                if ($x == 'default') {
                    update_post_meta($homepage->ID, '_wp_page_template', 'elementor_header_footer');
                }
            }
        }

    }

    /**
     * Kicking this off by calling 'get_instance()' method
     */
    Colorway_Sites_Importer::get_instance();

endif;
