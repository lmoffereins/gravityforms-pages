<?php

/**
 * Gravity Forms Pages Extension for GravityForms Submit Once
 *
 * @package Gravity Forms Pages
 * @subpackage GravityForms Submit Once
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GF_Pages_GravityForms_Submit_Once' ) ) :
/**
 * The Gravity Forms WorsPress SEO class
 *
 * @since 1.0.3
 */
class GF_Pages_GravityForms_Submit_Once {

	/**
	 * Setup this class
	 *
	 * @since 1.0.3
	 */
	public function __construct() {

		// Bail when GravityForms Submit Once is not active. Checking the function,
		// because the plugin has no init sub-action of its own.
		if ( ! function_exists( 'gravityforms_submit_once' ) )
			return;

		// Bail when the version is not supported
		if ( version_compare( gravityforms_submit_once()->version, '1.2.1', '<=' ) )
			return;

		$this->setup_actions();
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.3
	 */
	private function setup_actions() {
		add_filter( 'gf_pages_hide_form', array( $this, 'hide_form' ), 10, 2 );
	}

	/** Public methods **************************************************/

	/**
	 * Modify whether to hide the form
	 *
	 * @since 1.0.3
	 *
	 * @param bool        $hide_form Hide the form?
	 * @param object|bool $form Form object or false when not found
	 * @return bool Hide the form?
	 */
	public function hide_form( $hide_form, $form ) {

		// Bail when form is already hidden or closed forms are still shown
		if ( $hide_form || ! gf_pages_hide_closed_forms() ) {
			return $hide_form;
		}

		// Form is marked as submit-once
		if ( $form && gravityforms_submit_once()->is_submit_once_form( $form->id ) ) {

			// When considering closed forms, hide form for current user when it is submitted once before
			if ( gravityforms_submit_once()->has_user_submitted_once( $form->id ) ) {
				$hide_form = true;
			}
		}

		return $hide_form;
	}
}

/**
 * Setup the extension logic for GravityForms Submit Once
 *
 * @since 1.0.3
 *
 * @uses GF_Pages_GravityForms_Submit_Once
 */
function gf_pages_gravityforms_submit_once() {
	gf_pages()->extend->gravityforms_submit_once = new GF_Pages_GravityForms_Submit_Once;
}

endif; // class_exists
