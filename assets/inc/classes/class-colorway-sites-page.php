<?php
/**
 * ColorWay Sites Page
 *
 * @since 1.0.6
 * @package ColorWay Sites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Colorway_Sites_Page' ) ) {

	/**
	 *  ColorWay Admin Settings
	 */
	class Colorway_Sites_Page {

		/**
		 * View all actions
		 *
		 * @since 1.0.6
		 * @var array $view_actions
		 */
		static public $view_actions = array();

		/**
		 * Menu page title
		 *
		 * @since 1.0.6
		 * @var array $menu_page_title
		 */
		static public $menu_page_title = COLORWAY_SITES_NAME;

		/**
		 * Plugin slug
		 *
		 * @since 1.0.6
		 * @var array $plugin_slug
		 */
		static public $plugin_slug = 'colorway-sites';

		/**
		 * Default Menu position
		 *
		 * @since 1.0.6
		 * @var array $default_menu_position
		 */
		static public $default_menu_position = 'themes.php';

		/**
		 * Parent Page Slug
		 *
		 * @since 1.0.6
		 * @var array $parent_page_slug
		 */
		static public $parent_page_slug = 'general';

		/**
		 * Current Slug
		 *
		 * @since 1.0.6
		 * @var array $current_slug
		 */
		static public $current_slug = 'general';

		/**
		 * Constructor
		 */
		function __construct() {

			if ( ! is_admin() ) {
				return;
			}

			add_action( 'after_setup_theme', __CLASS__ . '::init_admin_settings', 99 );
		}

		/**
		 * Admin settings init
		 */
		static public function init_admin_settings() {

			self::$menu_page_title = apply_filters( 'colorway_sites_menu_page_title', __( 'ColorWay Sites', 'colorway-sites' ) );

			if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], self::$plugin_slug ) !== false ) {

				// Let extensions hook into saving.
				self::save_settings();
			}

			add_action( 'admin_menu', __CLASS__ . '::add_admin_menu', 100 );

			add_action( 'colorway_sites_menu_general_action', __CLASS__ . '::general_page' );
		}

		/**
		 * Save All admin settings here
		 */
		static public function save_settings() {

			// Only admins can save settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Let extensions hook into saving.
			do_action( 'colorway_sites_save_settings' );
		}

		/**
		 * Init Nav Menu
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.6
		 */
		static public function init_nav_menu( $action = '' ) {

			if ( '' !== $action ) {
				self::render_tab_menu( $action );
			}
		}

		/**
		 * Render tab menu
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.6
		 */
		static public function render_tab_menu( $action = '' ) {
			?>
			<div id="colorway-sites-menu-page">
				<?php self::render( $action ); ?>
			</div>
			<?php
		}

		/**
		 * View actions
		 *
		 * @since 1.0.11
		 */
		static public function get_view_actions() {

			if ( empty( self::$view_actions ) ) {

				self::$view_actions = apply_filters(
					'colorway_sites_menu_item', array()
				);
			}

			return self::$view_actions;
		}

		/**
		 * Prints HTML content for tabs
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.6
		 */
		static public function render( $action ) {

			?>
			<div class="nav-tab-wrapper">
				<h1 class='colorway-sites-title'> <?php echo esc_html( self::$menu_page_title ); ?></h1>
                                <button class="button button-primary youtube-link" youtubeid="8G8sZYNJGm0"><?php _e('Watch Tutorial Video','colorway-sites'); ?></button>
				<?php
				$view_actions = self::get_view_actions();

				foreach ( $view_actions as $slug => $data ) {

					if ( ! $data['show'] ) {
						continue;
					}

					$url = self::get_page_url( $slug );

					if ( $slug == self::$parent_page_slug ) {
						update_option( 'colorway_parent_page_url', $url );
					}

					$active = ( $slug == $action ) ? 'nav-tab-active' : '';
					?>
						<a class='nav-tab <?php echo esc_attr( $active ); ?>' href='<?php echo esc_url( $url ); ?>'> <?php echo esc_html( $data['label'] ); ?> </a>
				<?php } ?>
			</div><!-- .nav-tab-wrapper -->

			<?php
			// Settings update message.
			if ( isset( $_REQUEST['message'] ) && ( 'saved' == $_REQUEST['message'] || 'saved_ext' == $_REQUEST['message'] ) ) {
				?>
					<span id="message" class="notice notice-success is-dismissive"><p> <?php esc_html_e( 'Settings saved successfully.', 'colorway-sites' ); ?> </p></span>
				<?php
			}

		}

		/**
		 * Get and return page URL
		 *
		 * @param string $menu_slug Menu name.
		 * @since 1.0.6
		 * @return  string page url
		 */
		static public function get_page_url( $menu_slug ) {

			$parent_page = self::$default_menu_position;

			if ( strpos( $parent_page, '?' ) !== false ) {
				$query_var = '&page=' . self::$plugin_slug;
			} else {
				$query_var = '?page=' . self::$plugin_slug;
			}

			$parent_page_url = admin_url( $parent_page . $query_var );

			$url = $parent_page_url . '&action=' . $menu_slug;

			return esc_url( $url );
		}

		/**
		 * Add main menu
		 *
		 * @since 1.0.6
		 */
		static public function add_admin_menu() {

			$parent_page    = self::$default_menu_position;
			$page_title     = self::$menu_page_title;
			$capability     = 'manage_options';
			$page_menu_slug = self::$plugin_slug;
			$page_menu_func = __CLASS__ . '::menu_callback';

			add_theme_page( $page_title, $page_title, $capability, $page_menu_slug, $page_menu_func );
		}

		/**
		 * Menu callback
		 *
		 * @since 1.0.6
		 */
		static public function menu_callback() {

			$current_slug = isset( $_GET['action'] ) ? esc_attr( $_GET['action'] ) : self::$current_slug;

			$active_tab   = str_replace( '_', '-', $current_slug );
			$current_slug = str_replace( '-', '_', $current_slug );

			?>
			<div class="colorway-sites-menu-page-wrapper">
				<?php self::init_nav_menu( $active_tab ); ?>
				<?php do_action( 'colorway_sites_menu_' . esc_attr( $current_slug ) . '_action' ); ?>
			</div>
			<?php
		}

		/**
		 * Include general page
		 *
		 * @since 1.0.6
		 */
		static public function general_page() {
			require_once COLORWAY_SITES_DIR . 'inc/includes/admin-page.php';
		}
	}

	new Colorway_Sites_Page;

}// End if.
