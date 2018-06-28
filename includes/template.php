<?php

/**
 * Gravity Forms Pages Template Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Template
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Query *********************************************************************/

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
	if ( ! isset( $form->view_count ) )
		$form->view_count = 0;

	// Default lead count
	if ( ! isset( $form->lead_count ) )
		$form->lead_count = 0;

	return apply_filters( 'gf_pages_sanitize_form', $form );
}

/**
 * Whether there are more forms available in the loop
 *
 * @since 1.0.0
 *
 * @uses GF_Pages::form_query::have_posts()
 * @return object Form information
 */
function gf_pages_forms() {

	// Put into variable to check against next
	$have_posts = gf_pages()->form_query->have_posts();

	// Reset the post data when finished
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();
	}

	return $have_posts;
}

/**
 * Loads up the current form in the loop
 *
 * @since 1.0.0
 *
 * @uses GF_Pages::form_query::the_post()
 * @return object Form information
 */
function gf_pages_the_form() {
	return gf_pages()->form_query->the_post();
}

/**
 * Mimic WP's setup_postdata for the current form
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'gf_pages_the_form'
 *
 * @param object $form Form data.
 * @return bool True when finished
 */
function gf_pages_setup_postdata( $form ) {
	global $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;

	$id = (int) $form->id;

	// $authordata = get_userdata($post->post_author);

	$currentday = mysql2date('d.m.y', $form->date_created, false);
	$currentmonth = mysql2date('m', $form->date_created, false);
	$numpages = 1;
	$multipage = 0;
	$page = get_query_var('page');
	if ( ! $page )
		$page = 1;

	/* Handle form paging
	$content = $form->post_content;
	if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
		if ( $page > 1 )
			$more = 1;
		$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
		$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
		$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
		// Ignore nextpage at the beginning of the content.
		if ( 0 === strpos( $content, '<!--nextpage-->' ) )
			$content = substr( $content, 15 );
		$pages = explode('<!--nextpage-->', $content);
		$numpages = count($pages);
		if ( $numpages > 1 )
			$multipage = 1;
	} else {
		$pages = array( $form->post_content );
	}
	*/

	do_action_ref_array( 'gf_pages_the_form', array( &$form ) );

	return true;
}

/**
 * Setup the form query
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_form_pagination'
 * @uses apply_filters() Calls 'gf_pages_has_forms'
 *
 * @param array $args Optional. Query arguments
 * @return array Queried forms
 */
function gf_pages_has_forms( $args = array() ) {
	global $wp_rewrite;

	// Default argument array
	$default = array(
		'order'          => 'DESC',                        // 'ASC', 'DESC'
		'posts_per_page' => gf_pages_get_forms_per_page(), // Forms per page
		'paged'          => gf_pages_get_paged(),          // Page Number
		'max_num_pages'  => false,                         // Maximum number of pages to show
		'is_active'      => true,                          // Only active forms
	);

	/** Setup *****************************************************************/

	// Parse arguments against default values
	$r = wp_parse_args( $args, $default );

	// Get GF Pages
	$gfp = gf_pages();

	// Call the query
	$gfp->form_query = new GFP_Form_Query( $r );

	// Limited the number of pages shown
	if ( ! empty( $r['max_num_pages'] ) )
		$gfp->form_query->max_num_pages = $r['max_num_pages'];

	// If no limit to posts per page, set it to the current post_count
	if ( -1 === $r['posts_per_page'] )
		$r['posts_per_page'] = $gfp->form_query->post_count;

	// Add pagination values to query object
	$gfp->form_query->posts_per_page = $r['posts_per_page'];
	$gfp->form_query->paged          = $r['paged'];

	// Only add pagination if query returned results
	if ( ( (int) $gfp->form_query->post_count || (int) $gfp->form_query->found_posts ) && (int) $gfp->form_query->posts_per_page ) {

		// Limit the number of forms shown based on maximum allowed pages
		if ( ( ! empty( $r['max_num_pages'] ) ) && $gfp->form_query->found_posts > $gfp->form_query->max_num_pages * $gfp->form_query->post_count )
			$gfp->form_query->found_posts = $gfp->form_query->max_num_pages * $gfp->form_query->post_count;

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $wp_rewrite->using_permalinks() ) {

			// Single form
			if ( gf_pages_is_single_form() ) {
				$base = gf_pages_get_form_url( get_query_var( 'gf_pages_form_id' ) );

			// Form archive
			} elseif ( gf_pages_is_form_archive() ) {
				$base = gf_pages_get_form_archive_url();

			// Default
			} else {
				$base = get_permalink();
			}

			// Use pagination base
			$base = trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );

		// Unpretty pagination
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Pagination settings with filter
		$gf_pages_form_pagination = apply_filters( 'gf_pages_form_pagination', array (
			'base'      => $base,
			'format'    => '',
			'total'     => $r['posts_per_page'] === $gfp->form_query->found_posts ? 1 : ceil( (int) $gfp->form_query->found_posts / (int) $r['posts_per_page'] ),
			'current'   => (int) $gfp->form_query->paged,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'mid_size'  => 1
		) );

		// Add pagination to query object
		$gfp->form_query->pagination_links = paginate_links( $gf_pages_form_pagination );

		// Remove first page from pagination
		$gfp->form_query->pagination_links = str_replace( $wp_rewrite->pagination_base . "/1/'", "'", $gfp->form_query->pagination_links );
	}

	// Return object
	return apply_filters( 'gf_pages_has_forms', $gfp->form_query->have_posts(), $gfp->form_query );
}

/** Is_* **********************************************************************/

/**
 * Check if current page is any form page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query
 *
 * @uses apply_filters() Calls 'gf_pages_is_form'
 * @return bool Page is form page
 */
function gf_pages_is_form() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Either single form or form archive
	if ( gf_pages_is_single_form() || gf_pages_is_form_archive() )
		$retval = true;

	return (bool) apply_filters( 'gf_pages_is_form', $retval );
}

/**
 * Check if current page is a single form page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query
 *
 * @uses apply_filters() Calls 'gf_pages_is_single_form'
 * @return bool Page is single form page
 */
function gf_pages_is_single_form() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->gf_pages_is_single_form ) && ( true === $wp_query->gf_pages_is_single_form ) )
		$retval = true;

	// Check $_GET
	if ( empty( $retval ) && isset( $_REQUEST[ gf_pages_get_form_rewrite_id() ] ) && empty( $_REQUEST[ gf_pages_get_form_rewrite_id() ] ) )
		$retval = true;

	return (bool) apply_filters( 'gf_pages_is_single_form', $retval );
}

/**
 * Check if current page is a form archive page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query
 *
 * @uses apply_filters() Calls 'gf_pages_is_form_archive'
 * @return bool Page is form archive page
 */
function gf_pages_is_form_archive() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->gf_pages_is_form_archive ) && ( true === $wp_query->gf_pages_is_form_archive ) )
		$retval = true;

	// Check $_GET
	if ( empty( $retval ) && isset( $_REQUEST[ gf_pages_get_archive_rewrite_id() ] ) && empty( $_REQUEST[ gf_pages_get_archive_rewrite_id() ] ) )
		$retval = true;

	return (bool) apply_filters( 'gf_pages_is_form_archive', $retval );
}
