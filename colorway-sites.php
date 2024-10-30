<?php
/**
 * Plugin Name: Colorway Sites
 * Plugin URI: https://www.inkthemes.com/
 * Description: Import free sites build with Colorway Theme.
 * Version: 2.6.7
 * Author: InkThemes.com
 * Author URI: https://www.inkthemes.com
 * Text Domain: colorway-sites
 *
 * @package Colorway Sites
 */
/**
 * Set constants.
 */
if (!defined('COLORWAY_SITES_NAME')) {
    define('COLORWAY_SITES_NAME', __('Colorway Sites', 'colorway-sites'));
}

if (!defined('COLORWAY_SITES_VER')) {
    define('COLORWAY_SITES_VER', '2.6');
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
     * Colorway Sites Setup
     *
     * @since 1.0.5
     */
    function colorway_sites_setup() {
        require_once(dirname(__FILE__) . '/inc/classes/class-colorway-sites.php');
        require_once dirname( __FILE__ ) . '/inc/includes/recommended-plugins.php';
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
 * Deactivating Colorway Sites FE plugin upon installation of Colorway Sites Plugin
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
   
/*
 * Multisite admin notice
 */  
 if ( is_admin() && is_multisite() ) {
	add_action( 'admin_notices', 'colorway_sites_activation_notice' );
}

function colorway_sites_activation_notice(){
    ?>
    <div class="updated notice is-dismissible">
        <p style="font-weight:500">Since you are using a multisite network. Kindly, Download the following recommended plugins manually and upload & activate them on your WordPress network sites.</p>
        <table>
        <tr><td>Colorway Elementor Addons WordPress Plugin</td><td><a href="https://www.inkthemes.com/wp-content/uploads/colorway-addons.zip" class="button-primary">Download</a></td></tr>
        <tr><td>Appointup WordPress Plugin</td><td><a href="https://www.inkthemes.com/wp-content/uploads/appointup.zip" class="button-primary">Download</a></td></tr>
        <tr><td>Leadup WordPress Plugin</td><td><a href="https://www.inkthemes.com/wp-content/uploads/leadup.zip" class="button-primary">Download</a></td></tr>
</table>
    </div>
    <?php
}

/*
*Admin notices
*/
/*
function colorway_sites_lite_tracking_admin_notice() {
    global $current_user;
    $user_id = $current_user->ID;
    $val = get_option('colorway-sites_license_trial');
    $license_key = get_option('colorway-sites_license_key');

    //Check that the user hasn't already clicked to ignore the message 

    if (!get_user_meta($user_id, 'wp_theme_tracking_ignore_notice')) {
        if ($license_key!='') { 
        ?>
        <div class="notice notice-error" style="position:relative;">              

            <a href="<?php echo admin_url('themes.php?page=colorway-sites'); ?>" >  
                <button type="button" class="notice-dismiss is-dismissible">
                    <span class="screen-reader-text">Dismiss</span>
                </button>
            </a>
             <?php 
             if ($val == true) { ?>
                <p><?php _e('<span style="color:red;font-weight:bold;">Notice:</span> You are using the 15 days trial version of the colorway sites - Upgrade to pro version and continue your services un-interupted.<br/>'); ?></p><p><a target="_blank" href="https://www.inkthemes.com/members/signup/tXo82s57J" class="button button-primary"><?php _e('Colorway Sites Pro'); ?></a></p>
            <?php } else { ?>
                <p><?php _e('<span style="color:red;font-weight:bold;">Notice:</span> Your trial period has expired. Upgrade to Colorway sites pro version to continue using all features.'); ?></p><p><a target="_blank" href="https://www.inkthemes.com/members/signup/tXo82s57J" class="button button-primary"><?php _e('Colorway Sites Pro'); ?></a></p>
             <?php } ?>
        </div>

        <?php
    }
    }
}

add_action('admin_notices', 'colorway_sites_lite_tracking_admin_notice');
*/





