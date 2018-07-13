<?php

/**
 * Gravity Forms Pages Extension for WP SEO
 *
 * @package Gravity Forms Pages
 * @subpackage WP SEO
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GF_Pages_WPSEO' ) ) :
/**
 * The Gravity Forms WorsPress SEO class
 *
 * @since 1.0.0
 */
class GF_Pages_WPSEO {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bail when WP SEO is not active. Checking the constant,
		// because the plugin has no init sub-action of its own.
		if ( ! defined( 'WPSEO_VERSION' ) )
			return;

		$this->setup_actions();
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		add_filter( 'wpseo_breadcrumb_links', array( $this, 'breadcrumb_links' ) );
	}

	/** Public methods **************************************************/

	/**
	 * Modify the collection of page crumb links
	 *
	 * @since 1.0.0
	 *
	 * @param array $crumbs Breadcrumb links
	 * @return array Breadcrumb links
	 */
	public function breadcrumb_links( $crumbs ) {

		// Plugin page. Fully overwrite crumb paths
		if ( is_gf_pages() ) {

			// Define plugin crumb presets
			$_crumbs = array(

				// Form archives
				'forms' => array(
					'text'       => gf_pages_get_form_archive_title(),
					'url'        => gf_pages_hide_form_archive() ? false : gf_pages_get_form_archive_url(),
					'allow_html' => false,
				)
			);

			// Always append Form archvies just after home
			array_splice( $crumbs, 1, 0, array( $_crumbs['forms'] ) );

			// Define local variable(s)
			$last = count( $crumbs ) - 1;

			// Form archives
			if ( gf_pages_is_form_archive() ) {
				// Nothing to do

			// Single Form
			} elseif ( gf_pages_is_form() ) {

				// Set Form crumb
				$crumbs[] = array(
					'text'       => gf_pages_get_form_title(),
					'allow_html' => false
				);
			}

			$crumbs = array_values( $crumbs );
		}

		return $crumbs;
	}
}

/**
 * Setup the extension logic for WP SEO
 *
 * @since 1.0.0
 *
 * @uses GF_Pages_WPSEO
 */
function gf_pages_wpseo() {
	gf_pages()->extend->wpseo = new GF_Pages_WPSEO;
}

endif; // class_exists
