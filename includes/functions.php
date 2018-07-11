<?php

/**
 * Gravity Forms Pages Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Main
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Versions ***********************************************************/

/**
 * Output the plugin version
 *
 * @since 1.0.0
 */
function gf_pages_version() {
	echo gf_pages_get_version();
}

	/**
	 * Return the plugin version
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin version
	 */
	function gf_pages_get_version() {
		return gf_pages()->version;
	}

/**
 * Output the plugin database version
 *
 * @since 1.0.0
 */
function gf_pages_db_version() {
	echo gf_pages_get_db_version();
}

	/**
	 * Return the plugin database version
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin version
	 */
	function gf_pages_get_db_version() {
		return gf_pages()->db_version;
	}

/**
 * Output the plugin database version directly from the database
 *
 * @since 1.0.0
 */
function gf_pages_db_version_raw() {
	echo gf_pages_get_db_version_raw();
}

	/**
	 * Return the plugin database version directly from the database
	 *
	 * @since 1.0.0
	 *
	 * @return string The current plugin version
	 */
	function gf_pages_get_db_version_raw() {
		return get_option( 'gf_pages_db_version', '' );
	}

/** Slugs *********************************************************************/

/**
 * Get the forms slug
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_get_forms_slug'
 *
 * @param string $default Optional. Default value
 * @return string Form slug
 */
function gf_pages_get_forms_slug( $default = 'forms' ) {
	return apply_filters( 'gf_pages_get_forms_slug', get_option( '_gf_pages_forms_slug', $default ) );
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

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since 1.0.0
 */
function gf_pages_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/** Options *******************************************************************/

/**
 * Get the forms per page option
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_get_forms_per_page'
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
 * @uses apply_filters() Calls 'gf_pages_hide_form_archive'
 *
 * @param bool $default Optional. Default value
 * @return bool Hide form archive
 */
function gf_pages_hide_form_archive( $default = false ) {
	return (bool) apply_filters( 'gf_pages_hide_form_archive', get_option( '_gf_pages_hide_form_archive', $default ) );
}

/**
 * Return whether to hide closed forms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_hide_closed_forms'
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
 * @uses apply_filters() Calls 'gf_pages_force_ajax'
 *
 * @param bool $default Optional. Default value
 * @return bool Force ajax
 */
function gf_pages_force_ajax( $default = false ) {
	return (bool) apply_filters( 'gf_pages_force_ajax', get_option( '_gf_pages_force_ajax', $default ) );
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
 * @uses apply_filters() Calls 'gf_pages_get_form_slug'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return string Form slug
 */
function gf_pages_get_form_slug( $form = '' ) {
	$form = gf_pages_get_form( $form );
	$slug = '';

	// Get slug from title
	if ( ! empty( $form ) ) {
		$slug = sanitize_title_with_dashes( $form->title );
	}

	return apply_filters( 'gf_pages_get_form_slug', $slug, $form );
}

/**
 * Get the form by given slug
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_get_form_by_slug'
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

	/**
	 * Walk all forms. This can be expensive, but GF does the
	 * same with GFFormsModel::get_form_id().
	 */
	foreach ( GFFormsModel::get_forms() as $_form ) {

		// Look for a matching slug until we have a match
		if ( sanitize_title_with_dashes( $_form->title ) === $slug ) {
			$form = gf_pages_get_form( $_form );
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
 * @param int|string|object $form Optional. Form object, ID or slug. Defaults to current form.
 * @param string $by Optional. How to query for the form. Defaults to 'id'.
 * @return object|bool Form data when found, else False.
 */
function gf_pages_get_form( $form = 0, $by = 'id' ) {

	// Default empty parameter to ...
	if ( empty( $form ) ) {

		// ... the Form in the loop
		if ( gf_pages_in_the_form_loop() ) {
			$form = gf_pages()->form_query->form;

		// ... the queried object
		} elseif ( gf_pages_is_form() ) {
			$form = get_queried_object();

		// ... the query var on Form pages
		} elseif ( get_query_var( 'gf_pages_form' ) ) {
			$form = gf_pages_get_form_object( (int) get_query_var( 'gf_pages_form' ) );
		}

	// Get Form by the slug
	} elseif ( is_string( $form ) && 'slug' === $by ) {
		$form = gf_pages_get_form_by_slug( $form );

	// Get Form by ID
	} elseif ( is_numeric( $form ) && 'id' === $by ) {
		$form = gf_pages_get_form_object( $form );

	// Form data object provided
	} elseif ( is_object( $form ) && isset( $form->id ) ) {
		$form = gf_pages_get_form_object( $form->id );
	}

	// Reduce error to false
	if ( ! is_object( $form ) || is_wp_error( $form ) ) {
		$form = false;
	}

	return $form;
}

/**
 * Return the full form data object
 *
 * @since 1.0.0
 *
 * @param int $form_id Form ID
 * @param bool $with_meta Optional. Whether to return form meta as well. Defaults to true.
 * @return object|bool Form data or False when not found
 */
function gf_pages_get_form_object( $form_id, $with_meta = true ) {

	// Bail when there's no form ID
	if ( empty( $form_id ) ) {
		return false;
	}

	// Get the form data
	if ( ! is_object( $form_id ) || ! isset( $form->id ) ) {
		$form = GFFormsModel::get_form( (int) $form_id );
	} else {
		$form = $form_id;
		$form_id = $form->id;
	}

	// Combine form data
	if ( $form && $with_meta && ! isset( $form->display_meta ) ) {
		$form = (object) array_merge( (array) $form, (array) GFFormsModel::get_form_meta( $form_id ) );

		// Sanitize form
		$form = gf_pages_sanitize_form( $form );
	}

	return $form;
}

/**
 * Sanitizes a raw form and sets it up for further usage
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_sanitize_form'
 *
 * @param object $form Raw form
 * @return object Form
 */
function gf_pages_sanitize_form( $form ) {

	// Unserialize and attach meta
	if ( isset( $form->display_meta ) ) {
		$meta = GFFormsModel::unserialize( $form->display_meta );

		// Unset meta array
		unset( $form->display_meta );

		// Set meta properties
		foreach ( $meta as $key => $value ) {
			$form->$key = $value;
		}
	}

	// Default view count
	if ( ! isset( $form->view_count ) ) {
		$views = wp_list_filter( GFFormsModel::get_view_count_per_form(), array( 'form_id' => $form->id ) );
		$views = reset( $views );

		$form->view_count = $views ? (int) $views->view_count : 0;
	}

	// Default lead count
	if ( ! isset( $form->lead_count ) ) {
		$form->lead_count = (int) GFFormsModel::get_lead_count( $form->id, null );
	}

	return apply_filters( 'gf_pages_sanitize_form', $form );
}

/**
 * Return whether to hide the form
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_hide_form'
 *
 * @param object $form Optional. Form data. Defaults to the current form.
 * @return bool Hide the form?
 */
function gf_pages_hide_form( $form = '' ) {

	// Get form
	$form   = gf_pages_get_form( $form );
	$retval = false;

	if ( $form ) {

		// Hide inactive forms
		if ( gf_pages_is_form_inactive( $form ) ) {
			$retval = true;

		// Hide not open forms
		} elseif ( ! gf_pages_is_form_open( $form ) ) {
			$retval = true;

		// Hide closed forms
		} elseif ( gf_pages_hide_closed_forms() && gf_pages_is_form_closed( $form ) ) {
			$retval = true;

		// Hide forms for not logged-in users
		} elseif ( gf_pages_form_requires_login( $form ) && ! is_user_logged_in() ) {
			$retval = true;
		}
	}

	return (bool) apply_filters( 'gf_pages_hide_form', $retval, $form );
}

/**
 * Return whether to show the form
 *
 * @since 1.0.0
 *
 * @param object $form Optional. Form data. Defaults to the current form.
 * @return bool Show the form?
 */
function gf_pages_show_form( $form = '' ) {
	return ! gf_pages_hide_form( $form );
}

/**
 * Query and return forms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_get_forms'
 *
 * @param array $args Query arguments, supports these args:
 *  - number: The number of forms to query. Accepts -1 for all forms. Defaults to -1.
 *  - paged: The number of the current page for pagination.
 *  - count: Whether to return the form count. Defaults to false.
 *  - show_active: Whether to return active (true) or inactive (false) forms only. Accepts null for either status. Defaults to true.
 *  - orderby: The database column to order the results by. Defaults to 'date_created'.
 *  - order: Designates ascending or descending of ordered forms. Defaults to 'DESC'.
 *  - s: Search terms that could match a form's title.
 * @return array Form objects
 */
function gf_pages_get_forms( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'number'      => -1,
		'paged'       => 1,
		'count'       => false,
		'show_active' => true,
		'orderby'     => 'date_created',
		'order'       => 'DESC',
		's'           => ''
	) );

	// Query forms the GF way: fetch all
	if ( ! empty( $r['s'] ) ) {
		$forms = GFFormsModel::search_forms( $r['s'], $r['show_active'], $r['orderby'], $r['order'] );
	} else {
		$forms = GFFormsModel::get_forms( $r['show_active'], $r['orderby'], $r['order'] );
	}

	// Setup form objects
	$forms = array_map( 'gf_pages_get_form', $forms );

	// Remove unavailable forms
	$forms = array_filter( $forms, 'gf_pages_show_form' );

	// Return count early
	if ( $r['count'] ) {
		return count( $forms );
	}

	// Paginate the GF way, after the query
	if ( $r['number'] > 0 ) {
		$r['paged'] = absint( $r['paged'] );
		if ( $r['paged'] == 0 ) {
			$r['paged'] = 1;
		}
		$r['offset'] = absint( ( $r['paged'] - 1 ) * $r['number'] );

		$forms = array_slice( $forms, $r['offset'], $r['number'] );
	}

	return (array) apply_filters( 'gf_pages_get_forms', $forms, $r );
}

/**
 * Return the total found rows for the form query arguments
 *
 * @since 1.0.0
 *
 * @param array $query_args Original form query arguments.
 * @return int Total found rows
 */
function gf_pages_query_forms_found_rows( $query_args ) {

	// Remove paging arguments
	unset( $query_args['offset'], $query_args['paged'] );

	// Define count query args
	$query_args['count']  = true;
	$query_args['number'] = -1;

	// Run count query
	$count = gf_pages_get_forms( $query_args );

	return (int) $count;
}

/** Misc **********************************************************************/

/**
 * Modify the admin bar menu items
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function gf_pages_admin_bar_menu( $wp_admin_bar ) {

	// When on a single form page
	if ( gf_pages_is_form( true ) ) {

		// When the user can edit forms
		if ( GFCommon::current_user_can_any( 'gforms_edit_forms' ) ) {

			// Add 'Edit Form' menu item
			$wp_admin_bar->add_menu( array(
				'id'     => 'edit',
				'title'  => esc_html__( 'Edit Form', 'gravityforms-pages' ),
				'href'   => gf_pages_get_form_edit_url()
			) );
		}
	}
}
