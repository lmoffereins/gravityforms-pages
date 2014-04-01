<?php

/**
 * The Gravity Forms Pages Plugin
 *
 * @package Gravity Forms Pages
 * @subpackage Main
 *
 * @version Gravity Forms 1.6.9
 */

/**
 * Plugin Name:       Gravity Forms Pages
 * Plugin URI:        https://github.com/lmoffereins/gravityforms-pages/
 * Description:       Auto-create form pages for Gravity Forms
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins
 * Version:           1.0.0
 * Text Domain:       gravityforms-pages
 * GitHub Plugin URI: lmoffereins/gravityforms-pages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'GravityForms_Pages' ) ) :
/**
 * Main Gravity Forms Pages Class
 *
 * @since 1.0.0
 */
class GravityForms_Pages {

	/** Singleton *************************************************************/

	/**
	 * Main GF Pages Instance
	 *
	 * @since 1.0.0
	 * 
	 * @return GF Pages instance
	 */
	public static function instance() {

		// Store locally
		static $instance = null;
		
		// Only run when not previously run
		if ( null === $instance ) {
			$instance = new GravityForms_Pages;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Dummy constructor
	 *
	 * @see GravityForms_Pages::instance()
	 * @see gf_pages()
	 */
	public function __construct() { /* Nothing here */ }
	
	/** Setup *****************************************************************/

	/**
	 * Define default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version      = '1.0.0';

		/** Paths *************************************************************/
		
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Templates
		$this->template_dir = trailingslashit( $this->plugin_dir . 'templates' );
		$this->template_url = trailingslashit( $this->plugin_url . 'templates' );

		/** Identifiers *******************************************************/

		$this->form_id      = apply_filters( 'gf_pages_form_id',    'gf_pages_form'    );
		$this->archive_id   = apply_filters( 'gf_pages_archive_id', 'gf_pages_archive' );
		$this->paged_id     = apply_filters( 'gf_pages_paged_id',   'paged'            );

		/** Query *************************************************************/

		$this->current_form = new stdClass(); // Current form 
		$this->form_query   = new stdClass(); // Form query

		/** Misc **************************************************************/

		$this->domain       = 'gravityforms-pages';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( $this->includes_dir . 'functions.php' );
		require( $this->includes_dir . 'hooks.php'     );
		require( $this->includes_dir . 'query.php'     );
		require( $this->includes_dir . 'settings.php'  );
		require( $this->includes_dir . 'template.php'  );
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Rewrite Rules
		add_action( 'init',                  array( $this, 'add_rewrite_tags'        ), 20    );
		add_action( 'init',                  array( $this, 'add_rewrite_rules'       ), 30    );
		add_action( 'init',                  array( $this, 'add_permastructs'        ), 40    );

		// Queries
		add_action( 'parse_query',           array( $this, 'parse_query'             )        );

		// Template
		add_action( 'template_include',      array( $this, 'template_include'        )        );
		add_action( 'wp_title',              array( $this, 'wp_title'                ), 10, 3 );
		add_action( 'body_class',            array( $this, 'body_class'              ), 10, 2 );
		add_action( 'admin_bar_menu',        array( $this, 'admin_bar_menu'          ), 90    );

		// Admin Functions
		add_action( 'admin_menu',            array( $this, 'admin_menu'              ), 11    );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'   )        );
		add_action( 'admin_init',            array( $this, 'admin_register_settings' )        );
	}

	/** Rewrite Rules *********************************************************/

	/**
	 * Add the form-specific rewrite tags
	 * 
	 * @since 1.0.0
	 */
	public function add_rewrite_tags() {
		add_rewrite_tag( '%' . gf_pages_get_form_rewrite_id()    . '%', '([^/]+)' ); // Form Page tag
		add_rewrite_tag( '%' . gf_pages_get_archive_rewrite_id() . '%', '([^/]+)' ); // Form Archive page tag
	}

	/**
	 * Add form-specific rewrite rules for uri's that are not setup
	 * for us by way of custom post types or taxonomies. This includes:
	 * - Single forms
	 * - Forms archive
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form_archive_slug()
	 * @uses gf_pages_get_paged_slug()
	 * @uses gf_pages_get_archive_rewrite_id()
	 * @uses gf_pages_get_paged_rewrite_id()
	 */
	public function add_rewrite_rules() {

		// Priority
		$priority     = 'top';

		// Slugs
		$archive_slug = gf_pages_get_form_archive_slug(); // Prepend $wp_rewrite->root ?
		$paged_slug   = gf_pages_get_paged_slug();

		// Unique rewrite ID
		$form_id      = gf_pages_get_form_rewrite_id();
		$archive_id   = gf_pages_get_archive_rewrite_id();
		$paged_id     = gf_pages_get_paged_rewrite_id();

		// Rewrite rules
		$root_rule    = '/?$';
		$paged_rule   = '/' . $paged_slug . '/?([0-9]{1,})/?$';

		// Form Archive
		add_rewrite_rule( $archive_slug . $root_rule,  'index.php?' . $archive_id . '=1',                              $priority );
		add_rewrite_rule( $archive_slug . $paged_rule, 'index.php?' . $archive_id . '=1&' . $paged_id .'=$matches[1]', $priority );
	}

	/**
	 * Add permalink stucture for archive-style destinations.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_permastruct()
	 */
	public function add_permastructs() {

		// Get unique ID
		$form_id   = gf_pages_get_form_rewrite_id();
		
		// Get root slug
		$form_slug = gf_pages_get_single_form_slug();

		// Form Permastruct
		add_permastruct( $form_id, $form_slug . '/%' . $form_id . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => false,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );
	}

	/** Queries ***************************************************************/

	/**
	 * Add checks for GF Pages conditions to parse_query action
	 *
	 * If it's a form page, WP_Query::gf_pages_is_single_form is set to true and
	 * the query var 'gf_pages_form_id' with the form's id is added.
	 * If it's a form archive page, WP_Query::gf_pages_is_form_archive is set to true.
	 * In addition, on form/form archive pages, WP_Query::home is set to false
	 * and WP_Query::gf_pages_is_form is set to true. 
	 *
	 * @since 1.0.0
	 * 
	 * @param WP_Query $posts_query
	 *
	 * @todo Check capabilities
	 * @todo Overpaging (where no forms are) crashes 404
	 * @todo 1 forms per page returns not-found with correct form content on last item
	 */
	public function parse_query( $posts_query ) {

		// Bail if $posts_query is not the main loop
		if ( ! $posts_query->is_main_query() )
			return;

		// Bail if filters are suppressed on this query
		if ( true === $posts_query->get( 'suppress_filters' ) )
			return;

		// Bail if in admin
		if ( is_admin() )
			return;

		// Get query variables
		$gf_pages_form = $posts_query->get( gf_pages_get_form_rewrite_id() );

		// Single Form
		if ( ! empty( $gf_pages_form ) ) {

			/** Find Form *********************************************************/

			// Setup the default form variable
			$the_form = false;

			// If using pretty permalinks, use the slug
			if ( get_option( 'permalink_structure' ) ) {

				// Try slug
				$the_form = gf_pages_get_form_by_slug( $gf_pages_form );
			}

			// No form found by slug, so try the ID if it's numeric
			if ( empty( $the_form ) && is_numeric( $gf_pages_form ) ) {
				$the_form = gf_pages_get_form( $gf_pages_form );
			}

			// 404 and bail if form is not found
			if ( empty( $the_form->id ) ) {
				$posts_query->set_404();
				return;
			}

			/** Form Exists *******************************************************/

			// 404 and bail if to hide single form
			if ( gf_pages_hide_single_form( $the_form ) ) {
				$posts_query->set_404();
				return;				
			}

			// Looking at a single form
			$posts_query->gf_pages_is_single_form = true;
			$posts_query->gf_pages_is_form        = true;

			// Make sure 404 is not set
			$posts_query->is_404 = false;

			// Correct is_home variable
			$posts_query->is_home = false;

			// Set is_singular variable
			$posts_query->is_singular = true;

			// Set gf_pages_form_id for future reference
			$posts_query->set( 'gf_pages_form_id', $the_form->id );

			// Set global current form
			gf_pages()->current_form = $the_form;

		// Archive Page
		} elseif ( isset( $posts_query->query_vars[ gf_pages_get_archive_rewrite_id() ] ) ) {
			
			// 404 and bail if to hide archive page
			if ( gf_pages_hide_form_archive() ) {
				$posts_query->set_404();
				return;			
			}

			// We are on an archive page
			$posts_query->gf_pages_is_form_archive = true;
			$posts_query->gf_pages_is_form         = true;

			// Make sure 404 is not set
			$posts_query->is_404 = false;

			// Set is_archive variable
			$posts_query->is_archive = true;

			// Correct is_home variable
			$posts_query->is_home = false;
		}
	}

	/** Template **************************************************************/

	/**
	 * Replace and load the template for form pages
	 *
	 * @since 1.0.0
	 * 
	 * @param string $template Template file
	 * @return string Template
	 */
	public function template_include( $template = '' ) {

		// Setup local vars
		$file = '';

		// Single Form
		if ( gf_pages_is_single_form() ) {
			$file      = 'single-form.php';
			$templates = apply_filters( 'gf_pages_get_single_form_template', array(
				'page-' . $file,
				$file,
				'gravityforms/page-' . $file,
				'gravityforms/' . $file,
			) );

		// Form Archive
		} elseif ( gf_pages_is_form_archive() ) {
			$file      = 'archive-form.php';
			$templates = apply_filters( 'gf_pages_get_form_archive_template', array(
				'page-' . $file,
				$file,
				'gravityforms/page-' . $file,
				'gravityforms/' . $file,
			) );
		}


		// Locate template
		if ( ! empty( $file ) ) {
			$template = locate_template( $templates );
			if ( ! $template )
				$template = $this->template_dir . $file;
		}

		return apply_filters( 'gf_pages_template_include', $template, $file );
	}

	/**
	 * Return the forms page title element
	 *
	 * @since 1.0.0
	 * 
	 * @param string $title Page title
	 * @param string $sep Separator
	 * @param string $seplocation Page title direction
	 * @return string $title
	 */
	public function wp_title( $title, $sep, $seplocation ) {

		// Single Form
		if ( gf_pages_is_single_form() ) {
			$single = gf_pages_get_form_title();
			$form   = __('Form', 'gravityforms-pages');

			if ( 'right' == $seplocation ) {
				$title = $single . " $sep " . $form . " $sep " . $title;
			} else {
				$title .= " $sep " . $form . " $sep " . $single;
			}

		// Form Archive
		} elseif ( gf_pages_is_form_archive() ) {
			$archive = gf_pages_get_form_archive_title();

			if ( 'right' == $seplocation ) {
				$title = $archive . " $sep " . $title;
			} else {
				$title .= " $sep " . $archive;
			}
		}

		return apply_filters( 'gf_pages_wp_title', $title, $sep, $seplocation );
	}

	/**
	 * Add form specific classes to the body class 
	 *
	 * @since 1.0.0
	 * 
	 * @param array $classes Body classes
	 * @return array Classes
	 */
	public function body_class( $wp_classes = array(), $custom_classes = false ) {
		$gf_pages_classes = array();

		// Single Form
		if ( gf_pages_is_single_form() ) {
			$gf_pages_classes[] = 'form-' . gf_pages_get_form_slug();
			$gf_pages_classes[] = 'form-' . gf_pages_get_form_id();
			$gf_pages_classes[] = 'single';
			$gf_pages_classes[] = 'single-form';
			$gf_pages_classes[] = 'singular';

		// Form Archive
		} elseif ( gf_pages_is_form_archive() ) {
			$gf_pages_classes[] = 'archive';
			$gf_pages_classes[] = 'form-archive';
			$gf_pages_classes[] = 'forms';
		}

		if ( ! empty( $gf_pages_classes ) ) {	
			$gf_pages_classes[] = 'form';
			$gf_pages_classes[] = 'gfp-form';
		}

		// Merge WP classes with GFP classes and remove duplicates
		$classes = array_unique( array_merge( $wp_classes, $gf_pages_classes ) );

		return apply_filters( 'gf_pages_body_class', $classes, $gf_pages_classes, $wp_classes, $custom_classes );
	}

	/**
	 * Add 'Edit Form' admin bar menu item for single forms
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_is_single_form()
	 * @uses WP_Admin_Bar::remove_menu()
	 * @uses GFCommon::current_user_can_any()
	 * @uses WP_Admin_Bar::add_menu()
	 * @uses gf_pages_get_edit_form_url()
	 * 
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar_menu( $wp_admin_bar ) {

		// Bail if not on single form page
		if ( ! gf_pages_is_single_form() )
			return;

		// Remove 'Edit Post' menu - hacky!
		$wp_admin_bar->remove_menu( 'edit' );

		// Bail if user cannot edit forms
		if ( ! GFCommon::current_user_can_any( 'gforms_edit_forms' ) )
			return;

		// Add 'Edit Form' menu
		$wp_admin_bar->add_menu( array(
			'id'     => 'edit',
			'title'  => __('Edit Form', 'gravityforms-pages'),
			'href'   => gf_pages_get_edit_form_url()
		) );
	}

	/** Admin *****************************************************************/

	/**
	 * Add the plugin admin menu
	 *
	 * @since 1.0.0
	 *
	 * @uses RGForms::get_parent_menu()
	 * @uses add_submenu_page()
	 */
	public function admin_menu() {

		// Get GF parent menu
		$parent = RGForms::get_parent_menu( apply_filters( 'gform_addon_navigation', array() ) );

		// Add the plugin menu page
		$hook = add_submenu_page( $parent['name'], __('Forms Pages', 'gravityforms-pages'), __('Pages', 'gravityforms-pages' ), 'gravityforms-pages-settings', 'gf_pages', array( $this, 'admin_page' ) );

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
	public function admin_head() {
	?>

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
	public function admin_page() {
	?>

		<div class="wrap">

            <div id="gravity-settings-icon" class="icon32"><br></div>
			<h2><?php _e('Forms Pages', 'gravityforms-pages'); ?></h2>

			<form method="post" action="options.php">
				<?php settings_fields( 'gf_pages' ); ?>
				<?php do_settings_sections( 'gf_pages' ); ?>
				<?php submit_button(); ?>
			</form>

		</div>

	<?php
	}

	/**
	 * Register plugin settings
	 *
	 * @since 1.0.0
	 *
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @uses register_setting()
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
 * Main function responsible for loading plugin functionality and returning
 * the plugin main object.
 * 
 * @since 1.0.0
 * 
 * @return GF Pages instance
 */
function gf_pages() {
	return GravityForms_Pages::instance();
}

// Get it runnin'
gf_pages();

endif; // class_exists
