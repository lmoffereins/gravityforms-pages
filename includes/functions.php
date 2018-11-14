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
 * Return the form archive title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_form_archive_title'
 *
 * @param string $default Optional. Default value
 * @return string Form archive title
 */
function gf_pages_form_archive_title( $default = '' ) {
	return apply_filters( 'gf_pages_form_archive_title', get_option( '_gf_pages_form_archive_title', $default ) );
}

/**
 * Return the form archive description
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_form_archive_description'
 *
 * @param string $default Optional. Default value
 * @return string Form archive description
 */
function gf_pages_form_archive_description( $default = '' ) {
	return apply_filters( 'gf_pages_form_archive_description', get_option( '_gf_pages_form_archive_description', $default ) );
}

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
 * Return whether forms are available by default
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_default_availability'
 *
 * @param bool $default Optional. Default value
 * @return bool Default availability
 */
function gf_pages_default_availability( $default = true ) {
	return (bool) apply_filters( 'gf_pages_default_availability', get_option( '_gf_pages_default_availability', $default ) );
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
	 * same with {@see GFFormsModel::get_form_id()}.
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
	if ( ! is_object( $form_id ) ) {
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
 * Return wether the form page is available
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_is_form_page_available'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Is form page available?
 */
function gf_pages_is_form_page_available( $form ) {

	// Get form
	$form   = gf_pages_get_form( $form );
	$retval = gf_pages_default_availability();

	if ( $form && isset( $form->gf_pages_page_availability ) ) {
		$retval = (bool) $form->gf_pages_page_availability;
	}

	return (bool) apply_filters( 'gf_pages_is_form_page_available', $retval, $form );
}

/**
 * Return whether to hide the form
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_hide_form'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Hide the form?
 */
function gf_pages_hide_form( $form = '' ) {

	// Get form
	$form   = gf_pages_get_form( $form );
	$retval = false;

	if ( $form ) {

		// Hide unavailable forms
		if ( ! gf_pages_is_form_page_available( $form ) ) {
			$retval = true;

		// Hide inactive forms
		} elseif ( gf_pages_is_form_inactive( $form ) ) {
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
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
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
 *  - suppress_filters: Whether to suppress filters. Defaults to false.
 * @return array Form objects
 */
function gf_pages_get_forms( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'number'           => -1,
		'paged'            => 1,
		'count'            => false,
		'show_active'      => true,
		'orderby'          => 'date_created',
		'order'            => 'DESC',
		's'                => '',
		'suppress_filters' => false,
	) );

	// Query forms the GF way: fetch all
	if ( ! empty( $r['s'] ) ) {
		$forms = GFFormsModel::search_forms( $r['s'], $r['show_active'], $r['orderby'], $r['order'] );
	} else {
		$forms = GFFormsModel::get_forms( $r['show_active'], $r['orderby'], $r['order'] );
	}

	// Setup form objects
	$forms = array_map( 'gf_pages_get_form', $forms );

	if ( ! $r['suppress_filters'] ) {

		// Remove unavailable forms
		$forms = array_filter( $forms, 'gf_pages_show_form' );

		// Enable plugin filtering
		$forms = (array) apply_filters( 'gf_pages_get_forms', $forms, $r );
	}

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

	return $forms;
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

/** Menus *********************************************************************/

/**
 * Return the available custom plugin nav menu items
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_get_nav_menu_items'
 *
 * @return array Custom nav menu items
 */
function gf_pages_get_nav_menu_items() {

	// Setup items in cache
	if ( empty( gf_pages()->wp_nav_menu_items ) ) {

		// Setup nav menu items
		$items = (array) apply_filters( 'gf_pages_get_nav_menu_items', array(

			// Form archives
			'form-archive' => array(
				'title'       => gf_pages_get_form_archive_title(),
				'url'         => ! gf_pages_hide_form_archive() ? gf_pages_get_form_archive_url() : '',
				'type_label'  => esc_html_x( 'Form Archive', 'Nav menu item type label', 'gravityforms-pages' ),
				'is_current'  => gf_pages_is_form_archive(),
				'is_parent'   => gf_pages_is_form( true ),
				'allow_empty' => true
			),
		) );

		// Set default arguments
		foreach ( $items as $item_id => &$item ) {
			$item = wp_parse_args( $item, array(
				'id'          => $item_id,
				'title'       => '',
				'type'        => 'gravityforms-pages',
				'type_label'  => esc_html_x( 'Form Page', 'Nva menu item type label', 'gravityforms-pages' ),
				'url'         => '',
				'is_current'  => false,
				'is_parent'   => false,
				'is_ancestor' => false,
				'allow_empty' => false,
			) );
		}

		// Assign items to cache
		gf_pages()->wp_nav_menu_items = $items;
	}

	return gf_pages()->wp_nav_menu_items;
}

/**
 * Setup nav menu item for a form
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form object or ID. Defaults to the current form
 * @return WP_Post Nav menu item object
 */
function gf_pages_setup_form_nav_menu_item( $form = '' ) {

	// Get the form
	$form      = gf_pages_get_form( $form );
	$menu_item = new stdClass;

	if ( $form ) {
		$menu_item->ID               = $form->id;
		$menu_item->id               = 'form-' . $form->id;
		$menu_item->db_id            = 0;
		$menu_item->menu_item_parent = 0;
		$menu_item->object_id        = (int) $form->id;
		$menu_item->post_parent      = 0;
		$menu_item->type             = 'gravityforms-pages';
		$menu_item->object           = 'form';
		$menu_item->type_label       = esc_html_x( 'Form', 'Nav menu item type label', 'gravityforms-pages' );
		$menu_item->title            = gf_pages_get_form_title( $form );
		$menu_item->url              = gf_pages_get_form_url( $form );
		$menu_item->target           = '';
		$menu_item->attr_title       = '';
		$menu_item->description      = gf_pages_get_form_description( $form );
		$menu_item->classes          = array();
		$menu_item->xfn              = '';
	}

	return $menu_item;
}

/**
 * Setup details of nav menu item for plugin pages
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_setup_nav_menu_item'
 *
 * @param WP_Post $menu_item Nav menu item object
 * @return WP_Post Nav menu item object
 */
function gf_pages_setup_nav_menu_item( $menu_item ) {

	// Plugin item
	if ( 'gravityforms-pages' === $menu_item->type ) {

		// Form page
		if ( 'form' === $menu_item->object ) {
			$form = gf_pages_get_form( $menu_item->object_id );

			if ( $form ) {
				$menu_item->type_label = esc_html_x( 'Form', 'Nav menu item type label', 'gravityforms-pages' );
				$menu_item->title      = gf_pages_get_form_title( $form );
				$menu_item->url        = gf_pages_get_form_url( $form );

				// Set item classes
				if ( ! isset( $menu_item->classes ) ) {
					$menu_item->classes = array();
				}

				// This is the current page
				if ( gf_pages_is_form( true ) && gf_pages_get_form_id() === $form->id ) {
					$menu_item->classes[] = 'current_page_item';
					$menu_item->classes[] = 'current-menu-item';
				}
			}

			// Prevent rendering hidden form when not in the admin
			if ( ! $form || ( ! is_admin() && gf_pages_hide_form( $form ) ) ) {
				$menu_item->_invalid = true;
			}

		// Custom page
		} else {

			// This is a registered custom menu item
			if ( $item = wp_list_filter( gf_pages_get_nav_menu_items(), array( 'id' => $menu_item->object ) ) ) {
				$item = (object) reset( $item );

				// Set item details
				$menu_item->type_label = $item->type_label;
				$menu_item->url        = $item->url;

				// Set item classes
				if ( ! isset( $menu_item->classes ) ) {
					$menu_item->classes = array();
				}

				// This is the current page
				if ( $item->is_current ) {
					$menu_item->classes[] = 'current_page_item';
					$menu_item->classes[] = 'current-menu-item';

				// This is the parent page
				} elseif ( $item->is_parent ) {
					$menu_item->classes[] = 'current_page_parent';
					$menu_item->classes[] = 'current-menu-parent';

				// This is an ancestor page
				} elseif ( $item->is_ancestor ) {
					$menu_item->classes[] = 'current_page_ancestor';
					$menu_item->classes[] = 'current-menu-ancestor';
				}

				// Prevent rendering when there's no url
				if ( empty( $menu_item->url ) && ! $item->allow_empty ) {
					$menu_item->_invalid = true;
				}
			}
		}

		// Enable plugin filtering
		$menu_item = apply_filters( 'gf_pages_setup_nav_menu_item', $menu_item );
	}

	return $menu_item;
}

/**
 * Modify the sorted list of menu items
 *
 * @since 1.0.0
 *
 * @param  array $items Menu items
 * @param  array $args Arguments for `wp_nav_menu()`
 * @return array Menu items
 */
function gf_pages_nav_menu_objects( $items, $args ) {

	// When on a plugin page
	if ( is_gf_pages() ) {
		$posts_page = (int) get_option( 'page_for_posts' );

		foreach ( $items as $k => $item ) {

			// Remove the posts page's parent status/class. By default WordPress
			// appoints the posts page as parent for non-page pages. Please not.
			if ( $item->object_id == $posts_page && 'post_type' == $item->type && in_array( 'current_page_parent', $item->classes ) ) {
				unset( $items[ $k ]->classes[ array_search( 'current_page_parent', $item->classes ) ] );
			}
		}
	}

	return $items;
}

/**
 * Add plugin nav menu item types for the Customizer
 *
 * @since 1.0.0
 *
 * @param array $item_types Nav menu item types
 * @return array Nav menu item types
 */
function gf_pages_customize_nav_menu_available_item_types( $item_types = array() ) {

	// Define 'Forms' menu item type
	$item_types['gravityforms-pages'] = array(
		'title'  => esc_html_x( 'Forms', 'Customizer menu section title', 'gravityforms-pages' ),
		'type'   => 'gravityforms-pages',
		'object' => 'form',
	);

	return $item_types;
}

/**
 * Add plugin pages to the available menu items in the Customizer
 *
 * @since 1.0.0
 *
 * @param array $items The array of menu items.
 * @param string $type The object type.
 * @param string $object The object name.
 * @param int $page The current page number.
 * @return array Menu items
 */
function gf_pages_customize_nav_menu_available_items( $items, $type, $object, $page ) {

	// Plugin items. Always handling object=form
	if ( 'gravityforms-pages' === $type ) {
		$forms = gf_pages_get_forms( array(
			'number'           => 10,
			'paged'            => $page + 1,
			'suppress_filters' => true,
		) );

		foreach ( $forms as $form ) {
			$items[] = gf_pages_setup_form_nav_menu_item( $form );
		}

		// When on the first page
		if ( 0 === $page ) {
			$_items = gf_pages_get_nav_menu_items();

			// Prepend all items
			foreach ( array_reverse( $_items ) as $item_id => $item ) {

				// Redefine item details
				$item['id']     = $object . '-' . $item_id;
				$item['object'] = $item_id;

				// Prepend item
				array_unshift( $items, $item );
			}
		}
	}

	return $items;
}

/**
 * Add plugin pages to the searched menu items in the Customizer
 *
 * @since 1.0.0
 *
 * @param array $items The array of menu items.
 * @param array $args Includes 'pagenum' and 's' (search) arguments.
 * @return array Menu items
 */
function gf_pages_customize_nav_menu_searched_items( $items, $args ) {

	// Search query matches a part of the term 'forms'
	if ( false !== strpos( _x( 'forms', 'Nav menu search term', 'gravityforms-pages' ), strtolower( $args['s'] ) ) ) {

		// Append all custom items
		foreach ( gf_pages_get_nav_menu_items() as $item_id => $item ) {

			// Redefine item details
			$item['id']     = 'forms-' . $item_id;
			$item['object'] = $item_id;

			// Append item
			$items[] = $item;
		}
	}

	// Search matches form titles
	foreach ( gf_pages_get_forms( array(
		's'                => $args['s'],
		'suppress_filters' => true,
		'orderby'          => 'title',
		'order'            => 'ASC',
	) ) as $form ) {
		$items[] = gf_pages_setup_form_nav_menu_item( $form );
	}

	return $items;
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

	// When editing a single form in the admin
	if ( is_admin() && 'toplevel_page_gf_edit_forms' === get_current_screen()->id && isset( $_GET['id'] ) ) {

		// When the form is visible as a page			
		if ( gf_pages_show_form( $_GET['id'] ) ) {

			// Add 'View Form' menu item
			$wp_admin_bar->add_menu( array(
				'id'     => 'view',
				'title'  => esc_html__( 'View Form', 'gravityforms-pages' ),
				'href'   => gf_pages_get_form_url( $_GET['id'] )
			) );
		}
	}
}
