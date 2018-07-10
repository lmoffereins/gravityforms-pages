<?php

/**
 * The Gravity Forms Pages Plugin
 *
 * Tested with Gravity Forms 1.6.9
 *
 * @package Gravity Forms Pages
 * @subpackage Main
 */

/**
 * Plugin Name:       Gravity Forms Pages
 * Plugin URI:        https://github.com/lmoffereins/gravityforms-pages/
 * Description:       List and display (single) Gravity Forms forms in your theme
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
		$this->db_version   = 10000;

		/** Paths *************************************************************/

		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Themes
		$this->themes_dir   = trailingslashit( $this->plugin_dir . 'templates' );
		$this->themes_url   = trailingslashit( $this->plugin_url . 'templates' );

		/** Identifiers *******************************************************/

		$this->form_id      = apply_filters( 'gf_pages_form_id',    'gf_pages_form'    );
		$this->archive_id   = apply_filters( 'gf_pages_archive_id', 'gf_pages_archive' );
		$this->paged_id     = apply_filters( 'gf_pages_paged_id',   'paged'            );

		/** Query *************************************************************/

		$this->form_query   = new stdClass(); // Main Form query

		/** Misc **************************************************************/

		$this->theme_compat = new stdClass();
		$this->extend       = new stdClass();
		$this->domain       = 'gravityforms-pages';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Core
		require( $this->includes_dir . 'actions.php'      );
		require( $this->includes_dir . 'forms.php'        );
		require( $this->includes_dir . 'functions.php'    );
		require( $this->includes_dir . 'template.php'     );
		require( $this->includes_dir . 'theme-compat.php' );
		require( $this->includes_dir . 'sub-actions.php'  );
		require( $this->includes_dir . 'update.php'       );

		// Admin
		if ( is_admin() ) {
			require( $this->includes_dir . 'admin.php'    );
			require( $this->includes_dir . 'settings.php' );
		}

		// Extensions
		require( $this->includes_dir . 'extend/wordpress-seo.php' );
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'gf_pages_activation'   );
		add_action( 'deactivate_' . $this->basename, 'gf_pages_deactivation' );

		// Bail when plugins is being deactivated
		if ( gf_pages_is_deactivation() )
			return;

		// Rewrite Rules
		add_action( 'gf_pages_init',   array( $this, 'add_rewrite_tags'  ), 20 );
		add_action( 'gf_pages_init',   array( $this, 'add_rewrite_rules' ), 30 );
		add_action( 'gf_pages_init',   array( $this, 'add_permastructs'  ), 40 );
	}

	/** Rewrite Rules *********************************************************/

	/**
	 * Add the form-specific rewrite tags
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_tags() {
		add_rewrite_tag( '%' . gf_pages_get_form_rewrite_id()    . '%', '([^/]+)' ); // Form Page tag
		add_rewrite_tag( '%' . gf_pages_get_archive_rewrite_id() . '%', '([^/]+)' ); // Form archives page tag
	}

	/**
	 * Add form-specific rewrite rules for uri's that are not setup
	 * for us by way of custom post types or taxonomies. This includes:
	 * - Forms archive
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_rules() {

		// Priority
		$priority   = 'top';

		// Slugs
		$forms_slug = gf_pages_get_forms_slug(); // Prepend $wp_rewrite->root?
		$paged_slug = gf_pages_get_paged_slug();

		// Unique rewrite ID
		$archive_id = gf_pages_get_archive_rewrite_id();
		$paged_id   = gf_pages_get_paged_rewrite_id();

		// Rewrite rules
		$root_rule  = '/?$';
		$paged_rule = '/' . $paged_slug . '/?([0-9]{1,})/?$';

		// Form Archive
		add_rewrite_rule( $forms_slug . $root_rule,  'index.php?' . $archive_id . '=1',                              $priority );
		add_rewrite_rule( $forms_slug . $paged_rule, 'index.php?' . $archive_id . '=1&' . $paged_id .'=$matches[1]', $priority );
	}

	/**
	 * Add permalink stucture for archive-style single form destinations.
	 *
	 * @since 1.0.0
	 */
	public function add_permastructs() {

		// Get unique ID
		$form_id = gf_pages_get_form_rewrite_id();

		// Get root slug
		$forms_slug = gf_pages_get_forms_slug();

		// Form Permastruct
		add_permastruct( $form_id, $forms_slug . '/%' . $form_id . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => false,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );
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
