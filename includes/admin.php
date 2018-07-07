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
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Plugin
		add_filter( 'plugin_action_links',   array( $this, 'plugin_action_links'     ), 10, 2 );

		// Settings
		add_action( 'admin_menu',            array( $this, 'admin_menu'              ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'   )     );
		add_action( 'gf_pages_admin_init',   array( $this, 'admin_register_settings' )     );
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
			$links['settings'] = '<a href="' . esc_url( add_query_arg( 'page', 'gf-pages', admin_url( 'admin.php' ) ) ) . '">' . esc_html_x( 'Settings', 'Plugin action link', 'gravityforms-pages' ) . '</a>';
		}

		return $links;
	}

	/** Settings ********************************************************/

	/**
	 * Add the plugin admin menu
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {

		// Get GF parent menu
		$parent = RGForms::get_parent_menu( apply_filters( 'gform_addon_navigation', array() ) );

		// Add the plugin menu page
		$hook = add_submenu_page( $parent['name'], esc_html_x( 'Forms Pages', 'Admin page title', 'gravityforms-pages' ), esc_html_x( 'Pages', 'Admin menu title', 'gravityforms-pages' ), $this->minimum_capability, 'gf-pages', array( $this, 'admin_page' ) );

		// Add hooks
		add_action( "admin_head-$hook", array( $this, 'admin_head' ) );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {

		// Enqueue Gravity Forms admin style that GF didn't register
		wp_enqueue_style( 'gforms-admin', GFCommon::get_base_url() . '/css/admin.css' );
	}

	/**
	 * Load additional code in the admin page head
	 *
	 * @since 1.0.0
	 */
	public function admin_head() { ?>

		<style type="text/css">
			input, textarea {
				padding: 3px;
			}
		</style>

		<?php
	}

	/**
	 * Output the plugin admin page
	 *
	 * @since 1.0.0
	 */
	public function admin_page() { ?>

		<div class="wrap">
			<h1><?php echo esc_html_x( 'Forms Pages', 'Admin page heading', 'gravityforms-pages' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'gf-pages' ); ?>
				<?php do_settings_sections( 'gf-pages' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>

		<?php
	}

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