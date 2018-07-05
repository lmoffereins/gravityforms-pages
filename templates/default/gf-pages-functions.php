<?php

/**
 * Gravity Forms Pages Theme-Compat Functions
 *
 * Override this logic with your own gf-pages-functions.php inside your theme.
 *
 * @package Gravity Forms Pages
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GF_Pages_Default' ) ) :
/**
 * Loads default Gravity Forms Pages theme compatibility functionality
 *
 * @since 1.0.0
 */
class GF_Pages_Default {

	/**
	 * Setup the class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Load the theme styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		// Bail when not an a plugin page
		if ( ! is_gf_pages() )
			return;

		gf_pages_enqueue_style( 'gravityforms-pages', 'css/gravityforms-pages.css', array(), gf_pages_get_version(), 'screen' );
	}
}

// Load it up
new GF_Pages_Default();

endif; // class_exists
