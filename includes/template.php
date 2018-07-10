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
 * Add checks for plugin conditions to parse_query action
 *
 * @since 1.0.0
 *
 * @todo Overpaging (where no forms are) crashes 404
 * @todo 1 forms per page returns not-found with correct form content on last item
 *
 * @param WP_Query $posts_query
 */
function gf_pages_parse_query( $posts_query ) {

	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() )
		return;

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail if in admin
	if ( is_admin() )
		return;

	// Get plugin
	$plugin = gf_pages();

	// Get query variables
	$is_form_archive = $posts_query->get( gf_pages_get_archive_rewrite_id() );
	$is_form         = $posts_query->get( gf_pages_get_form_rewrite_id()    );

	// Form archives
	if ( $is_form_archive ) {

		// 404 and bail when to hide archive page or when Forms are not returned in query
		if ( gf_pages_hide_form_archive() || ! gf_pages_query_forms() ) {
			gf_pages_do_404();
			return;
		}

		// We are on an archive page
		$posts_query->gf_pages_is_form_archive = true;
		$posts_query->is_archive               = true;

		// Make sure 404 is not set
		$posts_query->is_404 = false;

		// Correct is_home variable
		$posts_query->is_home = false;

		// Define query result
		$posts_query->found_posts   = $plugin->form_query->found_forms;
		$posts_query->max_num_pages = $plugin->form_query->max_num_pages;

	// Single Form
	} elseif ( ! empty( $is_form ) ) {

		// Get Form, by slug when using pretty permalinks
		$get_by   = ( '' !== get_option( 'permalink_structure' ) ) ? 'slug' : 'id';
		$the_form = gf_pages_get_form( $is_form, $get_by );

		// 404 and bail when Form does not exist or should be hidden
		if ( ! $the_form || gf_pages_hide_form( $the_form ) ) {
			gf_pages_do_404();
			return;
		}

		// Set gf_pages_form for future reference
		$posts_query->set( 'gf_pages_form', $the_form->id );

		// Looking at a single form
		$posts_query->gf_pages_is_form = true;

		// Make sure 404 is not set
		$posts_query->is_404 = false;

		// Correct is_home variable
		$posts_query->is_home = false;

		// Mock a form's WP_Post properties, used in WP_Query
		$the_form->ID        = 0;
		$the_form->post_type = '';

		// Set queried object vars
		$posts_query->queried_object    = $the_form;
		$posts_query->queried_object_id = $the_form->id;
	}
}

/**
 * Overwrite the main WordPress query
 *
 * @since 1.0.0
 *
 * @param string $request SQL query
 * @param WP_Query $query Query object
 * @return string SQL query
 */
function gf_pages_filter_wp_query( $request, $query ) {
	global $wpdb;

	// Bail when this is not the main query
	if ( ! $query->is_main_query() )
		return $request;

	// When displaying plugin pages...
	if ( is_gf_pages() ) {

		// ... query for nothing and your chicks for free
		$request = "SELECT 1 FROM {$wpdb->posts} WHERE 0=1";
	}

	return $request;
}

/**
 * Stop WordPress performing a DB query for its main loop
 *
 * @since 1.0.0
 *
 * @param null $retval Current return value
 * @param WP_Query $query Query object
 * @return null|array
 */
function gf_pages_bypass_wp_query( $retval, $query ) {

	// Bail when this is not the main query
	if ( ! $query->is_main_query() )
		return $retval;

	// When displaying plugin pages...
	if ( is_gf_pages() ) {

		// ... return something other than a null value to bypass WP_Query
		$retval = array();
	}

	return $retval;
}

/**
 * Trigger a 404.
 *
 * @see bp_do_404()
 *
 * @since 1.0.0
 *
 * @uses WP_Query $wp_query
 *
 * @param string $redirect If 'remove_canonical_direct', remove WordPress' "helpful"
 *                         redirect_canonical action. Default: 'remove_canonical_redirect'.
 */
function gf_pages_do_404( $redirect = 'remove_canonical_direct' ) {
	global $wp_query;

	// Mock a non-existent post type query in order to remove
	// any suggestions of an existing post type query.
	$wp_query->set( 'post_type', '_' );

	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();

	if ( 'remove_canonical_direct' === $redirect ) {
		remove_action( 'template_redirect', 'redirect_canonical' );
	}
}

/** Is_* **********************************************************************/

/**
 * Check if current page is a form archive page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query To check if WP_Query::gf_pages_is_form_archive is true
 * @return bool Page is form archive page
 */
function gf_pages_is_form_archive() {
	global $wp_query;

	// Default to false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->gf_pages_is_form_archive ) && ( true === $wp_query->gf_pages_is_form_archive ) ) {
		$retval = true;
	}

	return (bool) $retval;
}

/**
 * Check if current page is a single form page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query To check if WP_Query::gf_pages_is_form is true
 *
 * @param bool $singular Optional. Whether to check for a singular page. Defaults to false.
 * @return bool Page is single form page
 */
function gf_pages_is_form( $singular = false ) {
	global $wp_query;

	// Default to false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->gf_pages_is_form ) && ( true === $wp_query->gf_pages_is_form ) ) {
		$retval = true;

	// Or we're in the forms loop
	} elseif ( ! $singular && gf_pages_in_the_form_loop() ) {
		$retval = true;
	}

	return (bool) $retval;
}

/**
 * Add form specific classes to the body class
 *
 * @since 1.0.0
 *
 * @param array $wp_classes Body classes
 * @param array $custom_classes Additional classes
 * @return array Body classes
 */
function gf_pages_body_class( $wp_classes, $custom_classes = false ) {

	// Define local var
	$form_classes = array();

	/** Pages *****************************************************************/

	if ( gf_pages_is_form_archive() ) {
		$form_classes[] = 'archive';
		$form_classes[] = 'form-archive';

	} elseif ( gf_pages_is_form() ) {
		$form_classes[] = 'single';
		$form_classes[] = 'single-form';
		$form_classes[] = 'formid-' . gf_pages_get_form_id();
	}

	/** Clean up **************************************************************/

	// Add plugin class when on a plugin page
	if ( ! empty( $form_classes ) ) {
		$form_classes[] = 'gravityforms-pages';
	}

	// Merge WP classes with plugin classes and remove duplicates
	$classes = array_unique( array_merge( (array) $wp_classes, $form_classes ) );

	return $classes;
}

/**
 * Use the is_() functions to return if on any plugin page
 *
 * @since 1.0.0
 *
 * @return bool On a plugin page
 */
function is_gf_pages() {

	// Default to false
	$retval = false;

	/** Pages *****************************************************************/

	if ( gf_pages_is_form_archive() ) {
		$retval = true;

	} elseif ( gf_pages_is_form() ) {
		$retval = true;
	}

	return (bool) $retval;
}

/** Theme *********************************************************************/

/**
 * Filter the theme's template for supporting themes
 *
 * @since 1.0.0
 *
 * @param string $template Path to template file
 * @return string Path to template file
 */
function gf_pages_template_include_theme_supports( $template = '' ) {

	// Define local var
	$_template = '';

	// Form archives
	if (     gf_pages_is_form_archive() && ( $_template = gf_pages_get_form_archive_template() ) ) :

	// Single Form
	elseif ( gf_pages_is_form()         && ( $_template = gf_pages_get_form_template()         ) ) :
	endif;

	// Set included template file
	if ( ! empty( $_template ) ) {
		$template = gf_pages_set_template_included( $_template );

		// Provide dummy post global, but theme compat is not active
		gf_pages_theme_compat_reset_post();
		gf_pages_set_theme_compat_active( false );
	}

	return $template;
}

/**
 * Set the included template
 *
 * @since 1.0.0
 *
 * @param string|bool $template Path to template file. Defaults to false.
 * @return string|bool Path to template file. False if empty.
 */
function gf_pages_set_template_included( $template = false ) {
	gf_pages()->theme_compat->gf_pages_template = $template;

	return gf_pages()->theme_compat->gf_pages_template;
}

/**
 * Return whether a template is included
 *
 * @since 1.0.0
 *
 * @return bool Template is included.
 */
function gf_pages_is_template_included() {
	return ! empty( gf_pages()->theme_compat->gf_pages_template );
}

/**
 * Retreive path to a template
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_{$type}_template'
 *
 * @param string $type Filename without extension.
 * @param array $templates Optional. Template candidates.
 * @return string Path to template file
 */
function gf_pages_get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	// Fallback file
	if ( empty( $templates ) ) {
		$templates = array( "{$type}.php" );
	}

	// Locate template file
	$template = gf_pages_locate_template( $templates );

	return apply_filters( "gf_pages_{$type}_template", $template );
}

/**
 * Locate and return the Form archive page template
 *
 * @since 1.0.0
 *
 * @return string Path to template file
 */
function gf_pages_get_form_archive_template() {
	$templates = array(
		'archive-gf-pages-form.php', // Generic Form archive
		'archive-gf-pages.php',      // Gravity Forms Pages archive
		'gf-pages-forms.php',        // Gravity Forms Pages Forms
	);

	return gf_pages_get_query_template( 'gf-pages-forms', $templates );
}

/**
 * Locate and return the single Form page template
 *
 * @since 1.0.0
 *
 * @return string Path to template file
 */
function gf_pages_get_form_template() {
	$form_id   = gf_pages_get_form_id();
	$templates = array(
		'gf-pages-form-' . $form_id . '.php', // Single Form ID
		'single-gf-pages-form.php',           // Single Form
		'gf-pages-form.php',                  // Gravity Forms Pages Form
	);

	return gf_pages_get_query_template( 'gf-pages-form', $templates );
}

/**
 * Locate and return the generic plugin page template
 *
 * @since 1.0.0
 *
 * @return string Path to template file
 */
function gf_pages_get_theme_compat_template() {
	$templates = array(
		'gf-pages-compat.php',
		'gf-pages.php'
	);

	// Use archive.php for archive pages
	if ( gf_pages_is_form_archive() ) {
		$templates[] = 'archive.php';
	}

	// Append generic templates
	$templates = array_merge( $templates, array(
		'generic.php',
		'single.php',
		'page.php',
		'index.php'
	) );

	return gf_pages_get_query_template( 'gf-pages-compat', $templates );
}

/** Archives ******************************************************************/

/**
 * Return whether we're in a custom query loop
 *
 * @since 1.0.0
 *
 * @return bool Are we in a custom query loop?
 */
function gf_pages_in_the_loop() {

	// Define return value
	$retval = false;

	// Form archives
	if ( gf_pages_is_form_archive() && gf_pages_in_the_form_loop() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Return the currently queried page number
 *
 * @since 1.0.0
 *
 * @return int Queried page number
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
	if ( ! empty( $paged ) )
		return (int) $paged;

	// Default to first page
	return 1;
}

/**
 * Modify the document title parts for plugin pages
 *
 * @since 1.0.0
 *
 * @param array $title Title parts
 * @return array Title parts
 */
function gf_pages_document_title_parts( $title = array() ) {

	// Define local var
	$_title = '';

	// Plugin page, not the archive page
	if ( is_gf_pages() && ! gf_pages_is_form_archive() ) {

		// Define parent title part
		$parent = array( 'parent' => esc_html_x( 'Forms', 'Plugin page title', 'gravityforms-pages' ) );

		// Insert 'Forms' part after title part, creates 'Title - Forms - Site'
		$title = array_slice( $title, 0, 1, true ) + $parent + array_slice( $title, 1, count( $title ) - 1, true );
	}

	// Root page
	if ( gf_pages_is_form_archive() ) {
		$_title = esc_html_x( 'Forms', 'Plugin page title', 'gravityforms-pages' );

	// Single Form
	} elseif ( gf_pages_is_form() ) {
		$_title = gf_pages_get_form_title();
	}

	// Overwrite document title
	if ( ! empty( $_title ) ) {
		$title['title'] = $_title;
	}

	return $title;
}

/**
 * Return the plugin's archive title
 *
 * @since 1.0.0
 *
 * @param string $title Archive title
 * @return string Archive title
 */
function gf_pages_get_the_archive_title( $title = '' ) {

	// Form archives
	if ( gf_pages_is_form_archive() ) {
		$title = esc_html_x( 'Forms', 'Plugin page title', 'gravityforms-pages' );

	// Single Form
	} elseif ( gf_pages_is_form() ) {
		$title = gf_pages_get_form_title();
	}

	return $title;
}

/**
 * Return the plugin's archive description
 *
 * @since 1.0.0
 *
 * @param string $description Archive description
 * @return string Archive description
 */
function gf_pages_get_the_archive_description( $description = '' ) {

	// Form archives
	if ( gf_pages_is_form_archive() ) {
		$description = esc_html__( 'This page lists all available forms for you.', 'gravityforms-pages' );

	// Single Form
	} elseif ( gf_pages_is_form() ) {
		$description = gf_pages_get_form_description();
	}

	return $description;
}

/**
 * Modify the item's CSS classes
 *
 * Applies to both post and term items.
 *
 * @since 1.0.0
 *
 * @param array $classes Item CSS classes
 * @return array Item CSS classes
 */
function gf_pages_filter_item_class( $classes ) {

	// When in Theme Compat mode and looping items on the page
	if ( gf_pages_is_theme_compat_active() && gf_pages_in_the_loop() ) {

		// Remove 'hentry' class, because when doing theme-compat
		// it messes with the common logic of theme styling
		if ( false !== ( $key = array_search( 'hentry', $classes ) ) ) {
			unset( $classes[ $key ] );
		}
	}

	return $classes;
}

/** Template Tags *************************************************************/

/**
 * Output the classes for the form div.
 *
 * @see post_class()
 *
 * @since 1.0.0
 *
 * @param string $class Optional. One or more classes to add to the class list.
 * @param object|int $form Optional. Form object or ID. Defaults to the current form.
 */
function gf_pages_form_class( $class = '', $form = 0 ) {
	echo 'class="' . join( ' ', gf_pages_get_form_class( $class, $form ) ) . '"';
}

/**
 * Return the classes for the form div.
 *
 * @see get_post_class()
 *
 * @since 1.0.0
 *
 * @param string $class Optional. One or more classes to add to the class list.
 * @param object|int $form Optional. Form object or ID. Defaults to the current form.
 * @return array Classes
 */
function gf_pages_get_form_class( $class = '', $form = 0 ) {
	$form    = gf_pages_get_form( $form );
	$classes = array();

	if ( $class ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_map( 'esc_attr', $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}

	if ( ! $form ) {
		return $classes;
	}

	// Get form specific classes
	if ( isset( $form->cssClass ) ) {
		$classes = array_merge( $classes, explode( ' ', $form->cssClass ) );
	}

	$classes[] = 'gravityforms-form';
	$classes[] = 'form-' . $form->id;

	// hentry for hAtom compliance
	$classes[] = 'hentry';

	// Form is (in)active
	$classes[] = gf_pages_is_form_active( $form ) ? 'form-active' : 'form-inactive';

	// Form is not open
	if ( ! gf_pages_is_form_open( $form ) ) {
		$classes[] = 'form-not-open';
	}

	// Form is closed
	if ( gf_pages_hide_closed_forms() && gf_pages_is_form_closed( $form ) ) {
		$classes[] = 'form-closed';
	}

	// Form requires login
	if ( gf_pages_form_requires_login( $form ) && ! is_user_logged_in() ) {
		$classes[] = 'form-login-required';
	}

	// Form user entry
	$classes[] = gf_pages_has_form_user_entry() ? 'form-user-entry' : 'form-no-user-entry';

	// Form has entry limit
	if ( gf_pages_has_form_entry_limit() ) {
		$classes[] = 'form-entry-limit';
	}

	// Honeypot enabled
	if ( gf_pages_is_form_honeypot_enabled() ) {
		$classes[] = 'form-honeypot';
	}

	// Animation enabled
	if ( gf_pages_is_form_animation_enabled() ) {
		$classes[] = 'form-animation';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filter the list of CSS classes for the current term.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes An array of term classes.
	 * @param array $class   An array of additional classes added to the term.
	 * @param object $form   Form data.
	 */
	$classes = apply_filters( 'gf_pages_form_class', $classes, $class, $form );

	return array_unique( $classes );
}

/**
 * Output navigation markup to next/previous plugin pages
 *
 * @see the_posts_navigation()
 *
 * @since 1.0.0
 *
 * @param array $args Arguments for {@see get_the_posts_navigation()}
 */
function gf_pages_the_posts_navigation( $args = array() ) {
	echo gf_pages_get_the_posts_navigation( $args );
}

	/**
	 * Return navigation markup to next/previous plugin pages
	 *
	 * @see get_the_posts_navigation()
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments for {@see get_the_posts_navigation()}
	 * @return string Navigation markup
	 */
	function gf_pages_get_the_posts_navigation( $args = array() ) {

		// Form archives
		if ( gf_pages_is_form_archive() ) {
			$args = array(
				'prev_text'          => esc_html__( 'Previous forms',   'gravityforms-pages' ),
				'next_text'          => esc_html__( 'Next forms',       'gravityforms-pages' ),
				'screen_reader_text' => esc_html__( 'Forms navigation', 'gravityforms-pages' )
			);
		}

		return get_the_posts_navigation( $args );
	}
