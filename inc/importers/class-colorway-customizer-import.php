<?php
/**
 * Customizer Data importer class.
 *
 * @since  1.0.0
 * @package  Colorway Addon
 */
defined('ABSPATH') or exit;

/**
 * Customizer Data importer class.
 *
 * @since  1.0.0
 */
class Colorway_Customizer_Import {

    /**
     * Instance of Colorway_Customizer_Import
     *
     * @since  1.0.0
     * @var Colorway_Customizer_Import
     */
    private static $_instance = null;

    /**
     * Instantiate Colorway_Customizer_Import
     *
     * @since  1.0.0
     * @return (Object) Colorway_Customizer_Import
     */
    public static function instance() {

        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Import customizer options.
     *
     * @since  1.0.0
     *
     * @param  (Array) $options customizer options from the demo.
     */
    public function import($options) {
 
        // Add Custom CSS.
        if (isset($options['wp_css'])) {
            wp_update_custom_css_post($options['wp_css']);
        }

        if (isset($options['options'])) {
            $ink_options = array();
            $get_options = array();
            
            //getting inkthemes_options all keys with values
            foreach ($options['options'] as $key => $val) {             
                preg_match_all("/\\[(.*?)\\]/", $key, $matches);               
                if (isset($matches)) {
                    $ink_options[$matches[1][0]] = $val;
                }
            }
           
            //getting basic WP option keys with values
            $get_options = array_diff($options['options'], $ink_options);                  
            
            foreach($get_options as $k=>$v){
                update_option($k, $v);
            }
           
            $prev_values = get_option('inkthemes_options');
            if (empty($prev_values) || !isset($prev_values)) {
                update_option('inkthemes_options', $ink_options);
            }            
            
            
        }
        if (isset($options['mods'])) {
                update_option('theme_mods_colorway', $options['mods']);
        }
    }

    /**
     * Import  Colorway Setting's
     *
     * Download & Import images from  Colorway Customizer Settings.
     *
     * @since 1.0.10
     *
     * @param  array $options  Colorway Customizer setting array.
     * @return void
     */
    static public function _import_settings($options = array()) {
        foreach ($options as $key => $val) {

            if (Colorway_Sites_Helper::_is_image_url($val)) {

                $data = Colorway_Sites_Helper::_sideload_image($val);

                if (!is_wp_error($data)) {
                    $options[$key] = $data->url;
                }
            }
        }

        // Updated settings.
        update_option('colorway-settings', $options);
    }

}