<?php

/**
 * Gravity Forms Pages Admin Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GravityForms_Pages_Admin' ) ) :
/**
 * The Gravity Forms Pages Admin class
 *
 * @since 1.0.0
 */
class GravityForms_Pages_Admin {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Define class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->minimum_capability = 'manage_options';
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Core
		require( gf_pages()->includes_dir . 'admin-functions.php' );

		// Utility
		require( gf_pages()->includes_dir . 'classes/class-gf-pages-walker-nav-menu-checklist.php' );
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Plugin
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

		// Settings
		add_action( 'gf_pages_admin_init', array( $this, 'admin_register_settings' ) );
		add_action( 'gf_pages_admin_init', array( $this, 'register_settings_page'  ) );

		// Forms
		add_filter( 'gform_form_actions', array( $this, 'form_actions' ), 10, 2 );

		// Nav menus
		add_action( 'load-nav-menus.php',        array( $this, 'nav_menu_add_metabox' ), 10 );
		add_action( 'wp_ajax_menu-quick-search', 'gf_pages_wp_ajax_menu_quick_search',    0 ); // Run before WP's default handler
	}

	/** Public methods **************************************************/

	/**
	 * Modify the plugin action links
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Plugin action links
	 * @param string $basename Plugin basename
	 * @return array Plugin action links
	 */
	public function plugin_action_links( $links, $basename ) {

		// Add plugin action links for this plugin
		if ( gf_pages()->basename === $basename && current_user_can( $this->minimum_capability ) ) {

			// Settings page
			$links['settings'] = '<a href="' . esc_url( add_query_arg( array( 'page' => 'gf_settings', 'subview' => 'gf-pages' ), admin_url( 'admin.php' ) ) ) . '">' . esc_html_x( 'Settings', 'Plugin action link', 'gravityforms-pages' ) . '</a>';
		}

		return $links;
	}

	/** Settings ********************************************************/

	/**
	 * Register plugin settings
	 *
	 * @since 1.0.0
	 */
	public static function admin_register_settings() {

		// Bail if no sections available
		$sections = gf_pages_admin_get_settings_sections();
		if ( empty( $sections ) )
			return false;

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only add section and fields if section has fields
			$fields = gf_pages_admin_get_settings_fields_for_section( $section_id );
			if ( empty( $fields ) )
				continue;

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $section['page'] );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
					add_settings_field( $field_id, $field['title'], $field['callback'], $section['page'], $section_id, $field['args'] );
				}

				// Register the setting
				register_setting( $section['page'], $field_id, $field['sanitize_callback'] );
			}
		}
	}

	/**
	 * Register a GF settings page
	 *
	 * @since 1.0.0
	 */
	public function register_settings_page() {

		// Bailw hen the user cannot edit settings
		if ( ! current_user_can( $this->minimum_capability ) )
			return;

		// Register GF settings tab
		RGForms::add_settings_page( array(
			'name'      => 'gf-pages',
			'tab_label' => esc_html_x( 'Pages', 'Settings tab title' ),
			'title'     => esc_html_x( 'Forms Pages', 'Settings page title' ),
			'handler'   => array( $this, 'settings_page' )
		) );
	}

	/**
	 * Display contents of the settings page
	 *
	 * @since 1.0.0
	 */
	public function settings_page() { ?>

		<h3><?php echo esc_html_x( 'Forms Pages', 'Admin page heading', 'gravityforms-pages' ); ?></h3>

		<form method="post" action="options.php">
			<?php settings_fields( 'gf-pages' ); ?>
			<?php do_settings_sections( 'gf-pages' ); ?>
			<?php submit_button(); ?>
		</form>

		<?php
	}

	/** Form ************************************************************/

	/**
	 * Modify the form actions in the form list table
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Form actions
	 * @param int $form_id Form ID
	 * @return array Form actions
	 */
	public function form_actions( $actions, $form_id ) {

		// Get the form
		$form = gf_pages_get_form( $form_id );

		/**
		 * Add View link when the Form is available as a page
		 *
		 * GF has no easy way of conditionally showing the View link, so we're
		 * just showing the link based on the current condition when the forms
		 * list is rendered.
		 */
		if ( $form && gf_pages_show_form( $form ) ) {
			$actions['view'] = array(
				'label'    => esc_html__( 'View', 'gravityforms-pages' ),
				'title'    => esc_html__( 'View this form', 'gravityforms-pages' ),
				'url'      => gf_pages_get_form_url( $form ),
				'priority' => 450,
			);
		}

		return $actions;
	}

	/** Nav Menus *******************************************************/

	/**
	 * Register nav menu metaboxes
	 *
	 * @since 1.0.0
	 */
	public function nav_menu_add_metabox() {
		add_meta_box( 'gravityforms-pages-nav-menu', esc_html_x( 'Forms', 'Nav menu metabox title', 'gravityforms-pages' ), 'gf_pages_admin_nav_menu_metabox', 'nav-menus', 'side', 'default' );
	}
}

/**
 * Setup the extension logic for BuddyPress
 *
 * @since 1.0.0
 *
 * @uses GravityForms_Pages_Admin
 */
function gf_pages_admin() {
	gf_pages()->admin = new GravityForms_Pages_Admin;
}

endif; // class_exists