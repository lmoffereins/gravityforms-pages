<?php

/**
 * Gravity Forms Pages Functions
 * 
 * @package Gravity Forms Pages
 * @subpackage Main
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Slugs *********************************************************************/

/**
 * Get the single form slug
 *
 * @since 1.0.0
 *
 * @param string $default Optional. Default value
 * @return string Form slug
 */
function gf_pages_get_single_form_slug( $default = 'form' ) {
	return apply_filters( 'gf_pages_get_single_form_slug', get_option( '_gf_pages_form_slug', $default ) );
}

/**
 * Get the form archive slug
 * 
 * @since 1.0.0
 * 
 * @param string $default Optional. Default value
 * @return string Form archive slug
 */
function gf_pages_get_form_archive_slug( $default = 'forms' ) {
	return apply_filters( 'gf_pages_get_form_archive_slug', get_option( '_gf_pages_form_archive_slug', $default ) );
}

/**
 * Get the slug used for paginated requests
 *
 * @since 1.0.0
 *
 * @uses WP_Rewrite
 * @return string Paged slug
 */
function gf_pages_get_paged_slug() {
	global $wp_rewrite;
	return $wp_rewrite->pagination_base;
}

/** Rewrite *******************************************************************/

/**
 * Get the form rewrite ID
 *
 * @since 1.0.0
 * 
 * @return string Form rewrite ID
 */
function gf_pages_get_form_rewrite_id() {
	return gf_pages()->form_id;
}

/**
 * Get the form archive rewrite ID
 *
 * @since 1.0.0
 * 
 * @return string Form archive rewrite ID
 */
function gf_pages_get_archive_rewrite_id() {
	return gf_pages()->archive_id;
}

/**
 * Get the id used for paginated requests
 *
 * @since 1.0.0
 * 
 * @return string Paged rewrite ID
 */
function gf_pages_get_paged_rewrite_id() {
	return gf_pages()->paged_id;
}

/** Options *******************************************************************/

/**
 * Get the forms per page option
 *
 * @since 1.0.0
 * 
 * @param int $default Optional. Default value
 * @return int Forms per page
 */
function gf_pages_get_forms_per_page( $default = 10 ) {
	return (int) apply_filters( 'gf_pages_get_forms_per_page', get_option( '_gf_pages_forms_per_page', $default ) );
}

/**
 * Return whether to hide the form archive
 *
 * @since 1.0.0
 * 
 * @param bool $default Optional. Default value
 * @return bool Hide form archive
 */
function gf_pages_hide_form_archive( $default = false ) {
	return (bool) apply_filters( 'gf_pages_hide_form_archive', get_option( '_gf_pages_hide_form_archive', $default ) );
}

/**
 * Return whether to hide the single form
 *
 * This is not an option actually.
 *
 * @since 1.0.0
 * 
 * @param object $form Form data
 * @return bool Hide single form
 */
function gf_pages_hide_single_form( $form = '' ) {
	if ( ! is_object( $form ) )
		$form = gf_pages_get_form( $form );

	return (bool) apply_filters( 'gf_pages_hide_single_form', false, $form );
}

/**
 * Return whether to hide closed forms
 *
 * @since 1.0.0
 * 
 * @param bool $default Optional. Default value
 * @return bool Hide closed forms
 */
function gf_pages_hide_closed_forms( $default = false ) {
	return (bool) apply_filters( 'gf_pages_hide_closed_forms', get_option( '_gf_pages_hide_closed_forms', $default ) );
}

/**
 * Return whether to force form ajax
 *
 * @since 1.0.0
 * 
 * @param bool $default Optional. Default value
 * @return bool Force ajax
 */
function gf_pages_force_ajax( $default = false ) {
	return (bool) apply_filters( 'gf_pages_force_ajax', get_option( '_gf_pages_force_ajax', $default ) );
}

/**
 * Return whether to show form view counts
 *
 * @since 1.0.0
 * 
 * @param bool $default Optional. Default value
 * @return bool Show view count
 */
function gf_pages_show_view_count( $default = false ) {
	return (bool) apply_filters( 'gf_pages_show_view_count', get_option( '_gf_pages_show_view_count', $default ) );
}

/**
 * Return whether to show form entry counts
 *
 * @since 1.0.0
 * 
 * @param bool $default Optional. Default value
 * @return bool Show entry count
 */
function gf_pages_show_entry_count( $default = false ) {
	return (bool) apply_filters( 'gf_pages_show_entry_count', get_option( '_gf_pages_show_entry_count', $default ) );
}

/** Form Names ****************************************************************/

/**
 * Since Gravity Forms requires form titles to be unique, we don't
 * have to do the checks for unique form names/slugs, and we can use
 * form slugs right out of the Gravity Forms box.
 *
 * NOTE: This is not entirely true since Gravity Forms only does
 * checks with strtolower(), but we'll leave it at that for now.
 */

/**
 * Get the form's slug
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form()
 * @uses sanitize_title_with_dashes()
 * 
 * @param object $form Form data
 * @return string Form slug
 */
function gf_pages_get_form_slug( $form = '' ) {
	if ( ! is_object( $form ) )
		$form = gf_pages_get_form( $form );

	$slug = '';

	// Bail if no form found
	if ( ! empty( $form ) ) {

		// Get slug from title
		$slug = sanitize_title_with_dashes( $form->title );
	}

	return apply_filters( 'gf_pages_get_form_slug', $slug, $form );
}

/**
 * Get the form by given slug
 *
 * @since 1.0.0
 * 
 * @param string $slug Form slug or title
 * @return bool|array False when form not found, array otherwise
 */
function gf_pages_get_form_by_slug( $slug = '' ) {

	// Bail if empty slug given
	if ( empty( $slug ) )
		return false;

	// Clean slug
	$slug = sanitize_title_with_dashes( $slug );

	// Assume not found
	$form = false;

	// Walk all forms. This can be expensive!
	// GF does the same with RGFormsModel::get_form_id()
	foreach ( RGFormsModel::get_forms() as $_form ) {
		if ( sanitize_title_with_dashes( $_form->title ) == $slug ) {
			$form = gf_pages_get_form( $_form->id );
			break;
		}
	}

	return apply_filters( 'gf_pages_get_form_by_slug', $form, $slug );
}

/** Form **********************************************************************/

/**
 * Return the requested form data or default to current form
 *
 * @since 1.0.0
 *
 * @param int|object $form_id Optional. Defaults to current form
 * @return object Form data
 */
function gf_pages_get_form( $form_id = 0 ) {

	// Form data object given
	if ( is_object( $form_id ) && isset( $form_id->id ) ) {
		$form    = $form_id;
		$form_id = $form->id;

	// Query requested form
	} elseif ( is_numeric( $form_id ) && ! empty( $form_id ) ) {
		$form    = (object) array_merge( (array) gf_get_form( $form_id ), (array) gf_get_form_meta( $form_id ) );
		$form_id = (int) $form_id;

	// Get current form
	} elseif ( ! empty( gf_pages()->current_form ) ) {
		$form    = gf_pages()->current_form;
		$form_id = $form->id;

	// Query page form
	} elseif ( get_query_var( 'gf_pages_form_id' ) ) {
		$form_id = get_query_var( 'gf_pages_form_id' );
		$form    = (object) array_merge( (array) gf_get_form( $form_id ), (array) gf_get_form_meta( $form_id ) );

	// Nothing found
	} else {
		$form    = new stdClass();
	}

	return apply_filters( 'gf_pages_get_form', $form, $form_id );
}

/** Query *********************************************************************/

/**
 * Assist pagination by returning correct page number
 *
 * @since 1.0.0
 *
 * @uses get_query_var() To get the 'paged' value
 * @return int Current page number
 */
function gf_pages_get_paged() {
	global $wp_query;

	// Check the query var
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );

	// Check query paged
	} elseif ( ! empty( $wp_query->query['paged'] ) ) {
		$paged = $wp_query->query['paged'];
	}

	// Paged found
	if ( !empty( $paged ) )
		return (int) $paged;

	// Default to first page
	return 1;
}

/** Helpers *******************************************************************/

if ( ! function_exists( 'gf_get_form' ) ) {
	/**
	 * Get the form by given ID
	 *
	 * @since 1.0.0
	 * 
	 * @param int $form_id Form ID
	 * @return object The form
	 */
	function gf_get_form( $form_id = 0 ) {

		// Bail if no form ID
		if ( empty( $form_id ) )
			return new stdClass();

		// Get the form data
		$form = RGFormsModel::get_form( $form_id );

		return (object) apply_filters( 'gf_get_form', $form, $form_id );
	}
}

if ( ! function_exists( 'gf_get_form_meta' ) ) {
	/**
	 * Get the form meta data by given ID
	 *
	 * @since 1.0.0
	 * 
	 * @param int $form_id Form ID
	 * @return object The form meta data
	 */
	function gf_get_form_meta( $form_id = 0 ) {

		// Bail if no form ID
		if ( empty( $form_id ) )
			return array();

		// Get the form meta data
		$form = RGFormsModel::get_form_meta( $form_id );

		return (object) apply_filters( 'gf_get_form_meta', $form, $form_id );
	}
}

