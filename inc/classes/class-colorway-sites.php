<?php

/**
 * Colorway Sites
 *
 * @since  1.0.0
 * @package Colorway Sites
 */
defined('ABSPATH') or exit;

if (!class_exists('Colorway_Sites')) :

    /**
     * Colorway_Sites
     */
    class Colorway_Sites {

        /**
         * API URL which is used to get the response from.
         *
         * @since  1.0.0
         * @var (String) URL
         */
        public static $api_url;

        /**
         * Instance of Colorway_Sites
         *
         * @since  1.0.0
         * @var (Object) Colorway_Sites
         */
        private static $_instance = null;

        /**
         * Instance of Colorway_Sites.
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
        private function __construct() {

            self::set_api_url();

            $this->includes();

            add_action('admin_notices', array($this, 'add_notice'), 1);
            add_action('admin_notices', array($this, 'admin_notices'));
            add_action('plugins_loaded', array($this, 'load_textdomain'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));

            // AJAX.
            add_action('wp_ajax_colorway-required-plugins', array($this, 'required_plugin'));
            add_action('wp_ajax_colorway-required-plugin-activate', array($this, 'required_plugin_activate'));
            add_action('wp_ajax_colorway-license-checker', array($this, 'check_license'));
            add_action('wp_ajax_colorway-license-reset', array($this, 'reset_license'));
        }

        /**
         * Add Admin Notice.
         */
        function add_notice() {
            if ('colorway' != get_option('template')) {
                Colorway_Sites_Notices::add_notice(
                        array(
                            'id' => 'colorway-theme-activation-nag',
                            'type' => 'error',
                            'show_if' => (!defined('COLORWAY_THEME_SETTINGS') ) ? true : false,
                            /* translators: 1: theme.php file */
                            'message' => sprintf(__(' Colorway Theme needs to be active for you to use currently installed "%1$s" plugin. <a href="%2$s">Install & Activate Now</a>', 'colorway-sites'), COLORWAY_SITES_NAME, esc_url(admin_url('theme-install.php?search=colorway'))),
                            'dismissible' => true,
                            'dismissible-time' => WEEK_IN_SECONDS,
                        )
                );
            }
        }

        /**
         * Loads textdomain for the plugin.
         *
         * @since 1.0.1
         */
        function load_textdomain() {
            load_plugin_textdomain('colorway-sites');
        }

        /**
         * Admin Notices
         *
         * @since 1.0.5
         * @return void
         */
        function admin_notices() {

            if (!defined('COLORWAY_THEME_SETTINGS')) {
                return;
            }

            add_action('plugin_action_links_' . COLORWAY_SITES_BASE, array($this, 'action_links'));
        }

        /**
         * Show action links on the plugin screen.
         *
         * @param   mixed $links Plugin Action links.
         * @return  array
         */
        function action_links($links) {
            $action_links = array(
                'settings' => '<a href="' . admin_url('themes.php?page=colorway-sites') . '" aria-label="' . esc_attr__('See Library', 'colorway-sites') . '">' . esc_html__('See Library', 'colorway-sites') . '</a>',
            );

            return array_merge($action_links, $links);
        }

        /**
         * Setter for $api_url
         *
         * @since  1.0.0
         */
        public static function set_api_url() {
            self::$api_url = apply_filters('colorway_sites_api_url', 'https://www.inkthemes.com/wp-json/wp/v2/');
        }

        /**
         * Enqueue admin scripts.
         *
         * @since  1.0.5    Added 'getUpgradeText' and 'getUpgradeURL' localize variables.
         *
         * @since  1.0.0
         *
         * @param  string $hook Current hook name.
         * @return void
         */
        public function admin_enqueue($hook = '') {

            if ('appearance_page_colorway-sites' !== $hook) {
                return;
            }

            global $is_IE, $is_edge;

            if ($is_IE || $is_edge) {
                wp_enqueue_script('colorway-sites-eventsource', COLORWAY_SITES_URI . 'inc/assets/js/eventsource.min.js', array('jquery', 'wp-util', 'updates'), COLORWAY_SITES_VER, true);
            }

            // API.
            wp_register_script('colorway-sites-api', COLORWAY_SITES_URI . 'inc/assets/js/colorway-sites-api.js', array('jquery'), COLORWAY_SITES_VER, true);

            // Admin Page.
            wp_enqueue_style('colorway-sites-admin', COLORWAY_SITES_URI . 'inc/assets/css/admin.css', COLORWAY_SITES_VER, true);
            wp_enqueue_script('colorway-sites-admin-page', COLORWAY_SITES_URI . 'inc/assets/js/admin-page.js', array('jquery', 'wp-util', 'updates'), COLORWAY_SITES_VER, true);
            wp_enqueue_script('colorway-sites-render-grid', COLORWAY_SITES_URI . 'inc/assets/js/render-grid.js', array('wp-util', 'colorway-sites-api', 'imagesloaded', 'jquery'), COLORWAY_SITES_VER, true);
            wp_enqueue_script('colorway-sites-video-popup', COLORWAY_SITES_URI . 'inc/assets/js/video-popup.js', COLORWAY_SITES_VER, true);

            $data = array(
                'ApiURL' => self::$api_url,
                'filters' => array(
                    'page_builder' => array(
                        'title' => __('Page Builder', 'colorway-sites'),
                        'slug' => 'site-page-builder',
                        'trigger' => 'colorway-api-category-loaded',
                    ),
                    'categories' => array(
                        'title' => __('Categories', 'colorway-sites'),
                        'slug' => 'site-category',
                        'trigger' => 'colorway-api-category-loaded',
                    ),
                ),
            );
            wp_localize_script('colorway-sites-api', 'ColorwaySitesAPI', $data);

            // Use this for premium demos.
            $request_params = apply_filters(
                    'colorway_sites_api_params', array(
                'purchase_key' => '',
                'site_url' => '',
                'par-page' => 100,
                    )
            );

            $data = apply_filters(
                    'colorway_sites_localize_vars', array(
                'sites' => $request_params,
                'settings' => array(),
                    )
            );

            wp_localize_script('colorway-sites-render-grid', 'colorwayRenderGrid', $data);

            $data = apply_filters(
                    'colorway_sites_localize_vars', array(
                'debug' => ( ( defined('WP_DEBUG') && WP_DEBUG ) || isset($_GET['debug']) ) ? true : false,
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                'siteURL' => site_url(),
                'getProText' => __('Purchase', 'colorway-sites'),
                'getProURL' => esc_url('https://www.inkthemes.com/market/colorway-wp-theme/#pricing'),
                'getUpgradeText' => __('Upgrade', 'colorway-sites'),
                'getUpgradeURL' => esc_url('https://www.inkthemes.com/market/colorway-wp-theme/#pricing'),
                '_ajax_nonce' => wp_create_nonce('colorway-sites'),
                'requiredPlugins' => array(),
                'strings' => array(
                    'warningBeforeCloseWindow' => __('Warning!  Colorway Site Import process is not complete. Don\'t close the window until import process complete. Do you still want to leave the window?', 'colorway-sites'),
                    'importFailedBtnSmall' => __('Error!', 'colorway-sites'),
                    'importFailedBtnLarge' => __('Error! Read Possibilities.', 'colorway-sites'),
                    'importFailedURL' => esc_url('https://www.inkthemes.com/documentation'),
                    'viewSite' => __('Done! View Site', 'colorway-sites'),
                    'btnActivating' => __('Activating', 'colorway-sites') . '&hellip;',
                    'btnActive' => __('Active', 'colorway-sites'),
                    'importFailBtn' => __('Import failed.', 'colorway-sites'),
                    'importFailBtnLarge' => __('Import failed. See error log.', 'colorway-sites'),
                    'importDemo' => __('Import This Site', 'colorway-sites'),
                    'importingDemo' => __('Importing..', 'colorway-sites'),
                    'DescExpand' => __('Read more', 'colorway-sites') . '&hellip;',
                    'DescCollapse' => __('Hide', 'colorway-sites'),
                    'responseError' => __('There was a problem receiving a response from server.', 'colorway-sites'),
                    'searchNoFound' => __('No Demos found, Try a different search.', 'colorway-sites'),
                    'importWarning' => __("Executing Demo Import will make your site similar as ours. Please bear in mind -\n\n1. It is recommended to run import on a fresh WordPress installation.\n\n2. Importing site does not delete any pages or posts. However, it can overwrite your existing content.\n\n3. Copyrighted media will not be imported. Instead it will be replaced with placeholders.", 'colorway-sites'),
                ),
                'log' => array(
                    'installingPlugin' => __('Installing plugin ', 'colorway-sites'),
                    'installed' => __('Successfully plugin installed!', 'colorway-sites'),
                    'activating' => __('Activating plugin ', 'colorway-sites'),
                    'activated' => __('Successfully plugin activated ', 'colorway-sites'),
                    'bulkActivation' => __('Bulk plugin activation...', 'colorway-sites'),
                    'activate' => __('Successfully plugin activate - ', 'colorway-sites'),
                    'activationError' => __('Error! While activating plugin  - ', 'colorway-sites'),
                    'bulkInstall' => __('Bulk plugin installation...', 'colorway-sites'),
                    'api' => __('Site API ', 'colorway-sites'),
                    'importing' => __('Importing..', 'colorway-sites'),
                    'processingRequest' => __('Processing requests...', 'colorway-sites'),
                    'importCustomizer' => __('1) Importing "Customizer Settings"...', 'colorway-sites'),
                    'importCustomizerSuccess' => __('Successfully imported customizer settings!', 'colorway-sites'),
                    'importXMLPrepare' => __('2) Preparing "XML" Data...', 'colorway-sites'),
                    'importXMLPrepareSuccess' => __('Successfully set XML data!', 'colorway-sites'),
                    'importXML' => __('3) Importing "XML"...', 'colorway-sites'),
                    'importXMLSuccess' => __('Successfully imported XML!', 'colorway-sites'),
                    'importOptions' => __('4) Importing "Options"...', 'colorway-sites'),
                    'importOptionsSuccess' => __('Successfully imported Options!', 'colorway-sites'),
                    'importWidgets' => __('5) Importing "Widgets"...', 'colorway-sites'),
                    'importWidgetsSuccess' => __('Successfully imported Widgets!', 'colorway-sites'),
                    'serverConfiguration' => esc_url('https://www.inkthemes.com/documentation'),
                    'success' => __('Site imported successfully! visit : ', 'colorway-sites'),
                    'gettingData' => __('Getting Site Information..', 'colorway-sites'),
                    'importingCustomizer' => __('Importing Customizer Settings..', 'colorway-sites'),
                    'importXMLPreparing' => __('Setting up import data..', 'colorway-sites'),
                    'importingXML' => __('Importing Pages, Posts & Media..', 'colorway-sites'),
                    'importingOptions' => __('Importing Site Options..', 'colorway-sites'),
                    'importingWidgets' => __('Importing Widgets..', 'colorway-sites'),
                    'importComplete' => __('Import Complete..', 'colorway-sites'),
                    'preview' => __('Previewing ', 'colorway-sites'),
                    'importLogText' => __('See Error Log &rarr;', 'colorway-sites'),
                ),
                    )
            );

            wp_localize_script('colorway-sites-admin-page', 'colorwaySitesAdmin', $data);
        }

        /**
         * Load all the required files in the importer.
         *
         * @since  1.0.0
         */
        private function includes() {

            require_once COLORWAY_SITES_DIR . 'inc/classes/class-colorway-sites-notices.php';
            require_once COLORWAY_SITES_DIR . 'inc/classes/class-colorway-sites-page.php';
            require_once COLORWAY_SITES_DIR . 'inc/classes/compatibility/class-colorway-sites-compatibility.php';
            require_once COLORWAY_SITES_DIR . 'inc/classes/class-colorway-sites-white-label.php';
            require_once COLORWAY_SITES_DIR . 'inc/classes/class-colorway-sites-importer.php';
        }

        /*
         * Licence Checker Function
         */

        function check_license() {
            ob_clean();
            $response = array();
            require_once COLORWAY_SITES_DIR . 'inc/Am/LicenseChecker.php';
            $license_key = trim(get_option('colorway-sites_license_key'));

            if (!strlen($license_key)) { //option value is blank then check for posted licence value              
                if (empty($_POST['keyinput'])) {
                    $response['message'] = __('Please enter a license key', 'colorway-sites');
                    $response['status'] = false;
                } else {
                    $license_key = preg_replace('/[^A-Za-z0-9-_]/', '', trim($_POST['keyinput']));
                    $checker = new Am_LicenseChecker($license_key, 'https://www.inkthemes.com/members/softsale/api', sha1('INKINK'));
                    if (!$checker->checkLicenseKey()) {
                        $response['message'] = __('Use a valid license key, please check your InkThemes account', 'colorway-sites');
                        $response['status'] = false;
                    } else { // license key verified! save it into the file
                        update_option('colorway-sites_license_key', $license_key);
                        $license_key = trim(get_option('colorway-sites_license_key'));
                        $response['message'] = $checker->getMessage() . ' & Verified Successfully!';
                        $response['status'] = true;
                        update_option('colorway-sites_license_trial', true);
                    }
                }                
            } else {
                    // now second, optional stage - check activation and binding of application     
                    $prev_activation_cache = $activation_cache; // store previous value to detect change
                    $checker = new Am_LicenseChecker($license_key, 'https://www.inkthemes.com/members/softsale/api', sha1('INKINK'));
                    $ret = empty($activation_cache) ?
                            $checker->activate($activation_cache) : // explictly bind license to new installation
                            $checker->checkActivation($activation_cache); // just check activation for subscription expriation, etc.
                    // in any case we need to store results to avoid repeative calls to remote api
                    if ($prev_activation_cache != $activation_cache)
                        update_option('colorway-sites_license_activation_cache', $activation_cache);
                    if (!$ret){
                        $response['message'] = __("Your trial period has been expired. <br/> Click on purchase to updrade.<br/><br/> <button class='button-primary' id='reset_license'>Reset License</button>", "colorway-sites");
                        $response['status'] = $checker->getCode();
                        update_option('colorway-sites_license_trial', false);
                    }else{
                        $response['message'] = __('License Key Already Exists,<br/> Click Import This Site Button<br/><br/>', 'colorway-sites');
                        $response['message'] .= __('Do you want to reset? <button class="button-primary" id="reset_license">Reset License</button>', 'colorway-sites');
                        $response['status'] = 'exists';
                    }
                    
               /* $chtm = get_remainning_trial_days();
                if ($chtm > 15) {
                        $response['message'] = __('Trial Period has Expired.<br/> Click on purchase to updrade.<br/><br/>', 'colorway-sites');
                        $response['message'] .= __('<a href=" https://www.inkthemes.com/market/colorway-wp-theme/" target="_blank"><button class="button-primary" id="">Purchase</button></a>', 'colorway-sites');                   
                         //reset_license('delete_key');
                        delete_option('colorway-sites_license_key');
                    } else {
                        $response['message'] = __('License Key Already Exists,<br/> Click Import This Site Button<br/><br/>', 'colorway-sites');
                        $response['message'] .= __('Do you want to reset? <button class="button-primary" id="reset_license">Reset License</button>', 'colorway-sites');
                        $response['status'] = 'exists';
                        $checker = new Am_LicenseChecker($license_key, 'https://www.inkthemes.com/members/softsale/api', sha1('INKINK'));
                        $response['validity'] = $checker->getCode();
                    
                }*/
            }
            // now second, optional stage - check activation and binding of application     
           /* $prev_activation_cache = $activation_cache; // store previous value to detect change
            $checker = new Am_LicenseChecker($license_key, 'https://www.inkthemes.com/members/softsale/api', sha1('INKINK'));
            $ret = empty($activation_cache) ?
                    $checker->activate($activation_cache) : // explictly bind license to new installation
                    $checker->checkActivation($activation_cache); // just check activation for subscription expriation, etc.
            // in any case we need to store results to avoid repeative calls to remote api
            if ($prev_activation_cache != $activation_cache)
                update_option('colorway-sites_license_activation_cache', $activation_cache);
            if (!$ret){
                $response['message'] = "Activation failed: (" . $checker->getCode() . ") " . $checker->getMessage();
           // if ($checker->getCode() == 'ok')
                $response['status'] = $checker->getCode();
                }*/
            echo json_encode($response);
            die();
        }

        /*
         * Reset Stored License and Activation keys
         * 
         */

        function reset_license() {
            ob_clean();
            require_once COLORWAY_SITES_DIR . 'inc/Am/LicenseChecker.php';
            $is_delete = $_POST['deleteLicense'];
            if ($is_delete) {
                $activation_cache = trim(get_option('colorway-sites_license_activation_cache'));
                $license_key = trim(get_option('colorway-sites_license_key'));
                $checker = new Am_LicenseChecker($license_key, 'https://www.inkthemes.com/members/softsale/api', sha1('INKINK'));
                $checker->deactivate($activation_cache);
                delete_option('colorway-sites_license_activation_cache');
                delete_option('colorway-sites_license_key');
                delete_option('colorway-sites_is_activated');
                _e('License has been removed', 'colorway-pro');
            }
            die();
        }

        /* Required Plugin Activate
         *
         * @since 1.0.0
         */

        public function required_plugin_activate() {

            if (!current_user_can('install_plugins') || !isset($_POST['init']) || !$_POST['init']) {
                wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => __('No plugin specified', 'colorway-sites'),
                        )
                );
            }

            $data = array();
            $plugin_init = ( isset($_POST['init']) ) ? esc_attr($_POST['init']) : '';
            $colorway_site_options = ( isset($_POST['options']) ) ? json_decode(stripslashes($_POST['options'])) : '';
            $enabled_extensions = ( isset($_POST['enabledExtensions']) ) ? json_decode(stripslashes($_POST['enabledExtensions'])) : '';

            $data['colorway_site_options'] = $colorway_site_options;
            $data['enabled_extensions'] = $enabled_extensions;

            $activate = activate_plugin($plugin_init, '', false, true);

            if (is_wp_error($activate)) {
                wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => $activate->get_error_message(),
                        )
                );
            }

            do_action('colorway_sites_after_plugin_activation', $plugin_init, $data);

            wp_send_json_success(
                    array(
                        'success' => true,
                        'message' => __('Plugin Successfully Activated', 'colorway-sites'),
                    )
            );
        }

        /**
         * Required Plugin
         *
         * @since 1.0.0
         * @return void
         */
        public function required_plugin() {

            // Verify Nonce.
            check_ajax_referer('colorway-sites', '_ajax_nonce');

            $response = array(
                'active' => array(),
                'inactive' => array(),
                'notinstalled' => array(),
                'filename' => array(),
            );

            if (!current_user_can('customize')) {
                wp_send_json_error($response);
            }

            $required_plugins = ( isset($_POST['required_plugins']) ) ? $_POST['required_plugins'] : array();

            if (count($required_plugins) > 0) {
                foreach ($required_plugins as $key => $plugin) {
                    $plugin = explode("/", $plugin);
                    /**
                     * Has Pro Version Support?
                     * And
                     * Is Pro Version Installed?
                     */
                    $plugin_pro = self::pro_plugin_exist($plugin[0]);
                    if ($plugin_pro) {

                        // Pro - Active.
                        if (is_plugin_active($plugin_pro)) {
                            $response['active'][] = $plugin_pro;

                            // Pro - Inactive.
                        } else {
                            $response['inactive'][] = $plugin_pro;
                        }
                    } else {

                        // Lite - Installed but Inactive.
                        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin[0]) && is_plugin_inactive($plugin[0])) {

                            $response['inactive'][] = $plugin[0];
                            $response['filename'][] = $plugin[1];

                            // Lite - Not Installed.
                        } elseif (!file_exists(WP_PLUGIN_DIR . '/' . $plugin[0])) {

                            $response['notinstalled'][] = $plugin[0];
                            $response['filename'][] = $plugin[1];

                            // Lite - Active.
                        } else {
                            $response['active'][] = $plugin[0];
                            $response['filename'][] = $plugin[1];
                        }
                    }
                }
            }

            // Send response.
            wp_send_json_success($response);
        }

        /**
         * Has Pro Version Support?
         * And
         * Is Pro Version Installed?
         *
         * Check Pro plugin version exist of requested plugin lite version.
         *
         * Eg. If plugin 'BB Lite Version' required to import demo. Then we check the 'BB Agency Version' is exist?
         * If yes then we only 'Activate' Agency Version. [We couldn't install agency version.]
         * Else we 'Activate' or 'Install' Lite Version.
         *
         * @since 1.0.1
         *
         * @param  string $lite_version Lite version init file.
         * @return mixed               Return false if not installed or not supported by us
         *                                    else return 'Pro' version details.
         */
        public static function pro_plugin_exist($lite_version = '') {

            // Lite init => Pro init.
            $plugins = apply_filters(
                    'colorway_sites_pro_plugin_exist', array(
                'beaver-builder-lite-version/fl-builder.php' => array(
                    'slug' => 'bb-plugin',
                    'init' => 'bb-plugin/fl-builder.php',
                    'name' => 'Beaver Builder Plugin',
                ),
                'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' => array(
                    'slug' => 'bb-ultimate-addon',
                    'init' => 'bb-ultimate-addon/bb-ultimate-addon.php',
                    'name' => 'Ultimate Addon for Beaver Builder',
                ),
                    ), $lite_version
            );

            if (isset($plugins[$lite_version])) {

                // Pro plugin directory exist?
                if (file_exists(WP_PLUGIN_DIR . '/' . $plugins[$lite_version]['init'])) {
                    return $plugins[$lite_version];
                }
            }

            return false;
        }

    }

    /**
     * Kicking this off by calling 'get_instance()' method
     */
    Colorway_Sites::get_instance();

endif;
