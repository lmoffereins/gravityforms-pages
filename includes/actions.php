<?php

/**
 * Gravity Forms Pages Actions
 *
 * @package Gravity Forms Pages
 * @subpackage Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Sub-actions ***************************************************************/

add_action( 'init',                      'gf_pages_init',              10 );
add_action( 'admin_init',                'gf_pages_admin_init',        10 );
add_action( 'after_setup_theme',         'gf_pages_after_setup_theme', 10 );

/** Utility *******************************************************************/

add_action( 'gf_pages_activation',       'gf_pages_delete_rewrite_rules', 10 );
add_action( 'gf_pages_deactivation',     'gf_pages_delete_rewrite_rules', 10 );

/** Query *********************************************************************/

add_action( 'parse_query',               'gf_pages_parse_query',         2    ); // Early for overrides
add_filter( 'gf_pages_hide_single_form', 'gf_pages_single_form_filter', 10, 2 );
add_filter( 'gf_pages_forms_where',      'gf_pages_forms_where_paged',  10, 2 );
// add_filter( 'gf_pages_the_forms',        'gf_pages_the_forms_filter',   10, 2 );

/** Post **********************************************************************/

add_filter( 'gf_pages_get_form_excerpt', 'wptexturize',        6  );
add_filter( 'gf_pages_get_form_excerpt', 'convert_chars',      8  );
add_filter( 'gf_pages_get_form_excerpt', 'capital_P_dangit',   10 );
add_filter( 'gf_pages_get_form_excerpt', 'convert_smilies',    20 );
add_filter( 'gf_pages_get_form_excerpt', 'force_balance_tags', 30 );
add_filter( 'gf_pages_get_form_excerpt', 'wpautop',            40 );

/** Admin *********************************************************************/

if ( is_admin() ) {
	add_action( 'gf_pages_init',         'gf_pages_admin',          10 );
	add_action( 'gf_pages_admin_init',   'gf_pages_setup_updater', 999 );
}

/** Template Filters **********************************************************/

/**
 * These filters are only here for potential use. They provide ways
 * to display forms with default post/page template files by filtering
 * the standard template tags that are used for posts and pages.
 */

/**
 * Filter the post title to return form title
 *
 * @since 1.0.0
 *
 * @param string $title Item title
 * @return string Form title
 */
function gf_pages_form_title_filter( $title = '' ) {

	// Bail if not in the loop
	if ( ! in_the_loop() )
		return $title;

	$form_id = get_query_var( 'gf_pages_form_id' );

	// Bail if not a form
	if ( empty( $form_id ) )
		return $title;

	// Get form title
	$title = gf_pages_get_form_title( $form_id );

	return apply_filters( 'gf_pages_form_title_filter', $title, $form_id );
}

/**
 * Filter the post content to return form content
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_form_content_filter'
 *
 * @param string $content Item content
 * @return string Form content
 */
function gf_pages_form_content_filter( $content = '' ) {
	global $wp_query;

	// Bail if not in the loop
	if ( ! in_the_loop() )
		return $content;

	$form_id = get_query_var( 'gf_pages_form_id' );

	// Bail if not a form
	if ( empty( $form_id ) )
		return $content;

	// Get form content
	$content = gf_pages_get_form_content( $form_id );

	return apply_filters( 'gf_pages_form_content_filter', $content, $form_id );
}

/**
 * Filter the post excerpt to return form excerpt
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_form_excerpt_filter'
 *
 * @param string $excerpt Item excerpt
 * @return string Form excerpt
 */
function gf_pages_form_excerpt_filter( $excerpt = '' ) {

	// Bail if not in the loop
	if ( ! in_the_loop() )
		return $excerpt;

	$form_id = get_query_var( 'gf_pages_form_id' );

	// Bail if not a form
	if ( empty( $form_id ) )
		return $excerpt;

	// Get form excerpt
	$excerpt = gf_pages_get_form_excerpt( $form_id );

	return apply_filters( 'gf_pages_form_excerpt_filter', $excerpt, $form_id );
}

/** Query Filters *************************************************************/

/**
 * Filter to hide the single form and return a 404 'Not Found'
 *
 * @since 1.0.0
 *
 * @param bool $hide Whether to hide the single form
 * @param object $form Form data
 * @return bool Hide single form
 */
function gf_pages_single_form_filter( $hide, $form ) {

	// Form is not yet open
	if ( ! gf_pages_is_form_open( $form )

		// Form is closed
		|| ( gf_pages_hide_closed_forms() && gf_pages_is_form_closed( $form ) )

		// Login required
		|| ( gf_pages_form_requires_login( $form ) && ! is_user_logged_in() )
	) {
		$hide = true;
	}

	return $hide;
}

/**
 * Manipulate the where forms query clause
 *
 * Form meta is stored as a serialized array so to check
 * for existing form meta is to check for strings that
 * match the serialized meta key and expected meta value.
 *
 * This method isn't waterproof and only works for hard
 * black-or-white checks. It cannot do comparison checking.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_forms_where_not_like'
 *
 * @param string $where Where clause
 * @param GFP_Forms_Query $query
 * @return string Where clause
 */
function gf_pages_forms_where_paged( $where, $query ) {

	// Setup local var
	$matches = array();

	/*
	// Form is not yet open
	$matches['hide_not_open'] = array(
		'scheduleForm'        => true,
		'scheduleStart'       => 0, // Date in the future
		'scheduleStartHour'   => 0, // Hour of this ampm in the future
		'scheduleStartMinute' => 0, // Minute of this hour in the future
		'scheduleStartAmpm'   => 0, // Ampm of this day in the future
	);

	// Form is closed
	if ( gf_pages_hide_closed_forms() ) {
		$matches['hide_closed'] = array(
			'scheduleForm'      => true,
			'scheduleEnd'       => 0, // Date in the past
			'scheduleEndHour'   => 0, // Hour of this ampm in the past
			'scheduleEndMinute' => 0, // Minute of this hour in the past
			'scheduleEndAmpm'   => 0, // Ampm of this day in the past
		);
	}
	*/

	// Login required
	if ( ! is_user_logged_in() ) {
		$matches['requireLogin'] = true;
	}

	// Filter matches
	$not_like = apply_filters( 'gf_pages_forms_where_not_like', $matches );

	// Build clause
	if ( ! empty( $not_like ) ) {
		$_matches = array();

		foreach ( $not_like as $match_key => $match_value ) {

			// Match multiple fields
			if ( is_array( $match_value ) && count( $match_value ) > 1 ) {
				$and = array();

				// Serialize keys and values
				foreach ( $match_value as $and_key => $and_value ) {
					$and[] = "'%" . serialize( $and_key ) . serialize( $and_value ) . "%'";
				}

				// Build AND string
				$_matches[] = '(' . implode( ' AND ', $and ) . ')';

			// Match single field
			} else {

				// Get single array field
				if ( is_array( $match_value ) ) {
					$match_key   = key( $match_value );
					$match_value = $match_value[$match_key];
				}

				// Serialize key and value
				$_matches[] = "'%" . serialize( $match_key ) . serialize( $match_value ) . "%'";
			}
		}

		$not_like   = implode( ' OR ', $_matches );
		if ( count( $_matches ) > 1 )
			$not_like = '(' . $not_like . ')';

		// Build where clause
		$meta_table = GFFormsModel::get_meta_table_name();
		$where     .= sprintf( " AND $meta_table.display_meta NOT LIKE %s", $not_like );
	}

	return $where;
}

/**
 * Filter the forms query result before query end
 *
 * @since 1.0.0
 *
 * @param array $forms Forms
 * @param GFP_Form_Query $query
 * @return array Forms
 */
function gf_pages_the_forms_filter( $forms, $query ) {

	// Loop the queried forms
	foreach ( $forms as $k => $form ) {

		// Form is not yet open
		if ( ! gf_pages_is_form_open( $form )

		// Form is closed
		|| ( gf_pages_hide_closed_forms() && gf_pages_is_form_closed( $form ) )

		// Login required
		|| ( gf_pages_form_requires_login( $form ) && ! is_user_logged_in() )
			) {
			unset( $forms[$k] );
			continue;
		}
	}

	// Reorder
	$forms = array_values( $forms );

	return $forms;
}

