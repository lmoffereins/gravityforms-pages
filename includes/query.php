<?php

/**
 * Gravity Forms Pages Form Query
 *
 * @package Gravity Forms Pages
 * @subpackage Query
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'GFP_Form_Query' ) ) :
/**
 * Custom Form Query
 *
 * @since 1.0.0
 */
class GFP_Form_Query extends WP_Query {

	/**
	 * Return forms as forms, not as posts
	 *
	 * @since 1.0.0
	 *
	 * @return array List of forms
	 */
	function get_posts() {
		global $wpdb, $user_ID, $_wp_using_ext_object_cache;

		$this->parse_query();

		do_action_ref_array('pre_get_posts', array(&$this));

		// Shorthand.
		$q = &$this->query_vars;

		// Fill again in case pre_get_posts unset some vars.
		$q = $this->fill_query_vars($q);

		// Set a flag if a pre_get_posts hook changed the query vars.
		$hash = md5( serialize( $this->query_vars ) );
		if ( $hash != $this->query_vars_hash ) {
			$this->query_vars_changed = true;
			$this->query_vars_hash = $hash;
		}
		unset($hash);

		// First let's clear some variables
		$distinct = '';
		$whichauthor = '';
		$whichmimetype = '';
		$where = '';
		$limits = '';
		$join = '';
		$search = '';
		$groupby = '';
		$fields = '';
		$post_status_join = false;
		$page = 1;

		// Get the form tables
		$form_table = RGFormsModel::get_form_table_name();
		$meta_table = RGFormsModel::get_meta_table_name();

		if ( !isset($q['suppress_filters']) )
			$q['suppress_filters'] = false;

		if ( !isset($q['cache_results']) ) {
			if ( wp_using_ext_object_cache() )
				$q['cache_results'] = false;
			else
				$q['cache_results'] = true;
		}

		$q['posts_per_page'] = (int) $q['posts_per_page'];
		if ( $q['posts_per_page'] < -1 )
			$q['posts_per_page'] = abs($q['posts_per_page']);
		else if ( $q['posts_per_page'] == 0 )
			$q['posts_per_page'] = 1;

		// If true, forcibly turns off SQL_CALC_FOUND_ROWS even when limits are present.
		if ( isset($q['no_found_rows']) )
			$q['no_found_rows'] = (bool) $q['no_found_rows'];
		else
			$q['no_found_rows'] = false;

		// Queried fields
		switch ( $q['fields'] ) {
			case 'ids':
				$fields = "$form_table.id";
				break;
			case 'meta':
				$fields = "$meta_table.display_meta";
				break;
			default:
				$fields = "$form_table.*, $meta_table.display_meta";
		}

		// Fetch queried form id
		if ( get_query_var( 'gf_pages_form_id' ) )
			$q['p'] = get_query_var( 'gf_pages_form_id' );

		// If a form number is specified, load that form
		if ( $q['p'] ) {
			$where .= " AND {$form_table}.id = " . $q['p'];
		} elseif ( $q['post__in'] ) {
			$post__in = implode(',', array_map( 'absint', $q['post__in'] ));
			$where .= " AND {$form_table}.id IN ($post__in)";
		} elseif ( $q['post__not_in'] ) {
			$post__not_in = implode(',',  array_map( 'absint', $q['post__not_in'] ));
			$where .= " AND {$form_table}.id NOT IN ($post__not_in)";
		}

		// Only query active forms
		$where .= " AND $form_table.is_active = 1";

		// Apply filters on where and join prior to paging so that any
		// manipulations to them are reflected in the paging by day queries.
		if ( !$q['suppress_filters'] ) {
			$where = apply_filters_ref_array('gf_pages_forms_where', array( $where, &$this ) );
			$join = apply_filters_ref_array('gf_pages_forms_join', array( $join, &$this ) );
		}

		if ( empty($q['order']) || ((strtoupper($q['order']) != 'ASC') && (strtoupper($q['order']) != 'DESC')) )
			$q['order'] = 'DESC';

		// Order by
		if ( empty($q['orderby']) ) {
			$orderby = "$form_table.date_created " . $q['order'];
		} elseif ( 'none' == $q['orderby'] ) {
			$orderby = '';
		} else {
			// Used to filter values
			$allowed_keys = array('id', 'title', 'date_created', 'is_active' );
			$q['orderby'] = urldecode($q['orderby']);
			$q['orderby'] = addslashes_gpc($q['orderby']);

			$orderby_array = array();
			foreach ( explode( ' ', $q['orderby'] ) as $i => $orderby ) {
				// Only allow certain values for safety
				if ( ! in_array($orderby, $allowed_keys) )
					continue;

				switch ( $orderby ) {
					default:
						$orderby = "$form_table." . $orderby;
				}

				$orderby_array[] = $orderby;
			}
			$orderby = implode( ',', $orderby_array );

			if ( empty( $orderby ) )
				$orderby = "$form_table.date_created ".$q['order'];
			else
				$orderby .= " {$q['order']}";
		}

		// Paging
		if ( empty($q['nopaging']) && !$this->is_singular ) {
			$page = absint($q['paged']);
			if ( !$page )
				$page = 1;

			if ( empty($q['offset']) ) {
				$pgstrt = ($page - 1) * $q['posts_per_page'] . ', ';
			} else { // we're ignoring $page and using 'offset'
				$q['offset'] = absint($q['offset']);
				$pgstrt = $q['offset'] . ', ';
			}
			$limits = 'LIMIT ' . $pgstrt . $q['posts_per_page'];
		}

		// Join form and meta table
		$join = "JOIN $meta_table ON ( $form_table.id = $meta_table.form_id )";

		// Collect query pieces
		$pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );

		// Apply post-paging filters on where and join. Only plugins that
		// manipulate paging queries should use these hooks.
		if ( !$q['suppress_filters'] ) {
			$where		= apply_filters_ref_array( 'gf_pages_forms_where_paged', array( $where,    &$this ) );
			$groupby	= apply_filters_ref_array( 'gf_pages_forms_groupby',     array( $groupby,  &$this ) );
			$join		= apply_filters_ref_array( 'gf_pages_forms_join_paged',  array( $join,     &$this ) );
			$orderby	= apply_filters_ref_array( 'gf_pages_forms_orderby',     array( $orderby,  &$this ) );
			$distinct	= apply_filters_ref_array( 'gf_pages_forms_distinct',    array( $distinct, &$this ) );
			$limits		= apply_filters_ref_array( 'gf_pages_form_limits',       array( $limits,   &$this ) );
			$fields		= apply_filters_ref_array( 'gf_pages_forms_fields',      array( $fields,   &$this ) );

			// Filter all clauses at once, for convenience
			$clauses = (array) apply_filters_ref_array( 'gf_pages_forms_clauses', array( compact( $pieces ), &$this ) );
			foreach ( $pieces as $piece )
				$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';
		}

		// Announce current selection parameters. For use by caching plugins.
		do_action( 'posts_selection', $where . $groupby . $orderby . $limits . $join );

		// Filter again for the benefit of caching plugins. Regular plugins should use the hooks above.
		if ( !$q['suppress_filters'] ) {
			$where		= apply_filters_ref_array( 'gf_pages_forms_where_request',    array( $where,    &$this ) );
			$groupby	= apply_filters_ref_array( 'gf_pages_forms_groupby_request',  array( $groupby,  &$this ) );
			$join		= apply_filters_ref_array( 'gf_pages_forms_join_request',     array( $join,     &$this ) );
			$orderby	= apply_filters_ref_array( 'gf_pages_forms_orderby_request',  array( $orderby,  &$this ) );
			$distinct	= apply_filters_ref_array( 'gf_pages_forms_distinct_request', array( $distinct, &$this ) );
			$fields		= apply_filters_ref_array( 'gf_pages_forms_fields_request',   array( $fields,   &$this ) );
			$limits		= apply_filters_ref_array( 'gf_pages_form_limits_request',    array( $limits,   &$this ) );

			// Filter all clauses at once, for convenience
			$clauses = (array) apply_filters_ref_array( 'gf_pages_forms_clauses_request', array( compact( $pieces ), &$this ) );
			foreach ( $pieces as $piece )
				$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';
		}

		if ( ! empty($groupby) )
			$groupby = 'GROUP BY ' . $groupby;
		if ( !empty( $orderby ) )
			$orderby = 'ORDER BY ' . $orderby;

		$found_rows = '';
		if ( !$q['no_found_rows'] && !empty($limits) )
			$found_rows = 'SQL_CALC_FOUND_ROWS';

		// Setup the request
		$this->request = $old_request = "SELECT $found_rows $distinct $fields FROM $form_table $join WHERE 1=1 $where $orderby $limits";

		if ( !$q['suppress_filters'] ) {
			$this->request = apply_filters_ref_array( 'gf_pages_forms_request', array( $this->request, &$this ) );
		}

		$this->posts = $wpdb->get_results( $this->request );
		$this->set_found_posts( $q, $limits );

		// Add form entry and view counts
		if ( 'ids' != $q['fields'] ) {
			$lead_table_name = RGFormsModel::get_lead_table_name();
			$view_table_name = RGFormsModel::get_form_view_table_name();

			$entries = $wpdb->get_results( "SELECT form_id, count(id)  as lead_count FROM $lead_table_name GROUP BY form_id" );
			$views   = $wpdb->get_results( "SELECT form_id, sum(count) as view_count FROM $view_table_name GROUP BY form_id" );

			foreach ( $this->posts as &$form ) {
				foreach ( $entries as $entry ) {
					if ( $entry->form_id == $form->id )
						$form->lead_count = $entry->lead_count; break;
				}
				foreach ( $views as $view ) {
					if ( $view->form_id == $form->id )
						$form->view_count = $view->view_count; break;
				}
			}
		}

		// Convert to form objects
		if ( $this->posts )
			$this->posts = array_map( 'gf_pages_sanitize_form', $this->posts );

		// Mimic 'the_posts' filter
		if ( !$q['suppress_filters'] )
			$this->posts = apply_filters_ref_array('gf_pages_the_forms', array( $this->posts, &$this ) );

		if ( $this->posts ) {
			$this->post_count = count( $this->posts );

			// Check each instance is a valid form
			$this->posts = array_map( 'gf_pages_sanitize_form', $this->posts );

			// Update post cache uses 'posts' cache key!
			// if ( $q['cache_results'] )
				// update_post_caches( $this->posts, 'gravityforms-pages', false, false );

			$this->post = reset( $this->posts );
		} else {
			$this->post_count = 0;
			$this->posts = array();
		}

		return $this->posts;
	}

	/**
	 * Redefine setting up the current post
	 *
	 * Sets current post as the current form global.
	 *
	 * @since 1.0.0
	 */
	function the_post() {
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) // Loop has just started
			do_action_ref_array( 'loop_start', array( &$this ) );

		gf_pages()->current_form = $form = $this->next_post();
		// gf_pages_setup_postdata( $form );
	}

	/**
	 * Redefine restoring the current form global
	 *
	 * @since 1.0.0
	 */
	function reset_postdata() {
		// if ( ! empty( $this->post ) ) {
		// 	$GLOBALS['post'] = $this->post;
		// 	setup_postdata( $this->post );
		// }
	}
}

endif; // class_exists

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
		$meta = maybe_unserialize( $form->display_meta );

		// Unset meta array
		unset( $form->display_meta );

		// Set meta properties
		foreach ( $meta as $key => $value )
			$form->$key = $value;
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
	if ( empty( $have_posts ) )
		wp_reset_postdata();

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

/** Theme Compat **************************************************************/

/**
 * This fun little function fills up some WordPress globals with dummy data to
 * stop your average page template from complaining about it missing.
 *
 * @since 1.0.0
 *
 * @see bbp_theme_compat_reset_post()
 *
 * @global WP_Query $wp_query
 * @global object $post
 * @param array $args
 */
function gf_pages_theme_compat_reset_post( $args = array() ) {
	global $wp_query, $post;

	// Switch defaults if post is set
	if ( isset( $wp_query->post ) ) {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => $wp_query->post->ID,
			'post_status'           => $wp_query->post->post_status,
			'post_author'           => $wp_query->post->post_author,
			'post_parent'           => $wp_query->post->post_parent,
			'post_type'             => $wp_query->post->post_type,
			'post_date'             => $wp_query->post->post_date,
			'post_date_gmt'         => $wp_query->post->post_date_gmt,
			'post_modified'         => $wp_query->post->post_modified,
			'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
			'post_content'          => $wp_query->post->post_content,
			'post_title'            => $wp_query->post->post_title,
			'post_excerpt'          => $wp_query->post->post_excerpt,
			'post_content_filtered' => $wp_query->post->post_content_filtered,
			'post_mime_type'        => $wp_query->post->post_mime_type,
			'post_password'         => $wp_query->post->post_password,
			'post_name'             => $wp_query->post->post_name,
			'guid'                  => $wp_query->post->guid,
			'menu_order'            => $wp_query->post->menu_order,
			'pinged'                => $wp_query->post->pinged,
			'to_ping'               => $wp_query->post->to_ping,
			'ping_status'           => $wp_query->post->ping_status,
			'comment_status'        => $wp_query->post->comment_status,
			'comment_count'         => $wp_query->post->comment_count,
			'filter'                => $wp_query->post->filter,

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	} else {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => -9999,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	}

	// Bail if dummy post is empty
	if ( empty( $dummy ) ) {
		return;
	}

	// Set the $post global
	$post = new WP_Post( (object) $dummy );

	// Copy the new post global into the main $wp_query
	$wp_query->post       = $post;
	$wp_query->posts      = array( $post );

	// Prevent comments form from appearing
	$wp_query->post_count = 1;
	$wp_query->is_404     = $dummy['is_404'];
	$wp_query->is_page    = $dummy['is_page'];
	$wp_query->is_single  = $dummy['is_single'];
	$wp_query->is_archive = $dummy['is_archive'];
	$wp_query->is_tax     = $dummy['is_tax'];

	// Clean up the dummy post
	unset( $dummy );

	/**
	 * Force the header back to 200 status if not a deliberate 404
	 *
	 * @see http://bbpress.trac.wordpress.org/ticket/1973
	 */
	if ( ! $wp_query->is_404() ) {
		status_header( 200 );
	}
}

/**
 * Reset main query vars and filter 'the_content' to output a Gravity
 * Forms Pages template part as needed.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_template_include_theme_compat'
 *
 * @param string $template
 * @return string Template
 */
function gf_pages_template_include_theme_compat( $template = '' ) {

	// Single Form
	if ( gf_pages_is_single_form() ) {

		// Reset post
		gf_pages_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => gf_pages_get_form_title(),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => gf_pages_get_form_content(),
			'post_type'      => '',
			'post_status'    => 'publish',
			'is_archive'     => false,
			'comment_status' => 'closed'
		) );

	// Form Archive
	} elseif ( gf_pages_is_form_archive() ) {

		// Reset post
		gf_pages_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => gf_pages_get_form_archive_title(),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => gf_pages_buffer_template_part( 'content', 'archive-form' ),
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed'
		) );
	}

	return apply_filters( 'gf_pages_template_include_theme_compat', $template );
}

