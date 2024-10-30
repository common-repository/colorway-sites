<?php
/**
 * Plugin Name: ColorWay Sites
 * Plugin URI: https://www.inkthemes.com/
 * Description: Import free sites build with ColorWay Theme.
 * Version: 2.6.4
 * Author: InkThemes.com
 * Author URI: https://www.inkthemes.com
 * Text Domain: colorway-sites
 *
 * @package ColorWay Sites
 */
/**
 * Set constants.
 */
if (!defined('COLORWAY_SITES_NAME')) {
    define('COLORWAY_SITES_NAME', __('ColorWay Sites', 'colorway-sites'));
}

if (!defined('COLORWAY_SITES_VER')) {
    define('COLORWAY_SITES_VER', '1.9');
}

if (!defined('COLORWAY_SITES_FILE')) {
    define('COLORWAY_SITES_FILE', __FILE__);
}

if (!defined('COLORWAY_SITES_BASE')) {
    define('COLORWAY_SITES_BASE', plugin_basename(COLORWAY_SITES_FILE));
}

if (!defined('COLORWAY_SITES_DIR')) {
    define('COLORWAY_SITES_DIR', plugin_dir_path(COLORWAY_SITES_FILE));
}

if (!defined('COLORWAY_SITES_URI')) {
    define('COLORWAY_SITES_URI', plugins_url('/', COLORWAY_SITES_FILE));
}

if (!function_exists('colorway_sites_setup')) :

    /**
     * ColorWay Sites Setup
     *
     * @since 1.0.5
     */
    function colorway_sites_setup() {
        require_once(dirname(__FILE__) . '/inc/classes/class-colorway-sites.php');
    }

    add_action('plugins_loaded', 'colorway_sites_setup');

endif;

function colorway_map_unrestricted_upload_filter($caps, $cap) {
  if ($cap == 'unfiltered_upload') {
    $caps = array();
    $caps[] = $cap;
  }

  return $caps;
}

add_filter('map_meta_cap', 'colorway_map_unrestricted_upload_filter', 0, 2);
/*
 * Deactivating ColorWay Sites FE plugin upon installation of ColorWay Sites Plugin
 */
register_activation_hook(__FILE__,'colorway_sites_fe_deactivate'); 
  function colorway_sites_fe_deactivate(){
     $dependent = 'colorway-sites-FE/colorway-sites-FE.php';
     if( is_plugin_active($dependent) ){
          add_action('update_option_active_plugins', 'colorway_sites_fe_deactivate_plugin');
     }
 }

   function colorway_sites_fe_deactivate_plugin(){
       $dependent = 'colorway-sites-FE/colorway-sites-FE.php';
       deactivate_plugins($dependent);
   }