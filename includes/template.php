<?php

/**
 * Gravity Forms Pages Template Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Template
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if current page is any form page
 *
 * @since 1.0.0
 *
 * @uses WP_Query
 * @return boolean Page is form page
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
 * @uses WP_Query
 * @return boolean Page is single form page
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
 * @uses WP_Query
 * @return boolean Page is form archive page
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

/** Form Loop *****************************************************************/

/**
 * Setup the form query
 *
 * @since 1.0.0
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

/**
 * Output the forms template part
 *
 * @since 1.0.0
 *
 * @see get_template_part()
 *
 * @uses locate_template()
 * @uses load_template()
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */
function gf_pages_get_template_part( $slug, $name = null ) {

	// WP core hook
	do_action( "get_template_part_{slug}", $slug, $name );

	$name = (string) $name;
	if ( '' === $name )
		return;

	// Only search for slug-name construct
	$template = "{$slug}-{$name}.php";
	$located  = locate_template( apply_filters( 'gf_pages_get_template_part', array( $template ), $slug, $name ) );

	// Load plugin file if not found in theme
	if ( '' == $located )
		$located = gf_pages()->template_dir . $template;

	// Output template
	if ( file_exists( $located ) )
		load_template( $located, false );
}

/** Single Form ***************************************************************/

/**
 * Output the current form ID
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_id() To get the form ID
 */
function gf_pages_form_id() {
	echo gf_pages_get_form_id();
}

	/**
	 * Get the current form ID
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @return int Form ID
	 */
	function gf_pages_get_form_id() {
		$form    = gf_pages_get_form();
		$form_id = 0;

		if ( ! empty( $form ) ) {
			$form_id = $form->id;
		}

		return (int) apply_filters( 'gf_pages_get_form_id', $form_id, $form );
	}

/**
 * Output the current form title
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_title() To get the form title
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_title( $form = '' ) {
	echo gf_pages_get_form_title( $form = '' );
}

	/**
	 * Get the current form title
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form title
	 */
	function gf_pages_get_form_title( $form = '' ) {
		$form  = gf_pages_get_form( $form );
		$title = '';

		if ( ! empty( $form ) ) {
			$title = $form->title;
		}

		return apply_filters( 'gf_pages_get_form_title', $title, $form );
	}

/**
 * Output the current form content
 *
 * Auto parses the form shortcode.
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_content() To get the form content
 *
 * @param bool $title Optional. Whether to add the form title. Defaults to false.
 * @param bool $description Optional. Whether to add the form description. Defaults to true.
 * @param bool $ajax Optional. Whether to add ajax functionality. Defaults to false.
 */
function gf_pages_form_content( $title = false, $description = true, $ajax = false ) {
	echo gf_pages_get_form_content( $title, $description, $ajax, true );
}

	/**
	 * Get the current form content
	 *
	 * Builds the form shortcode and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param bool $title Optional. Whether to add the form title. Defaults to false.
	 * @param bool $description Optional. Whether to add the form description. Defaults to true.
	 * @param bool $ajax Optional. Whether to add ajax functionality. Defaults to false.
	 * @param bool $parse Optional. Whether to parse the shortcode. Deafults to false.
	 * @return string Form content
	 */
	function gf_pages_get_form_content( $title = false, $description = true, $ajax = false, $parse = false ) {
		$form    = gf_pages_get_form();
		$content = '';

		// Build shortcode
		if ( ! empty( $form ) ) {

			// Start shortcode
			$content = '[gravityforms id="' . $form->id . '"';

			// Attributes
			if ( ! $title )
				$content .= ' title="false"';
			if ( ! $description )
				$content .= ' description="false"';
			if ( $ajax || gf_pages_force_ajax() )
				$content .= ' ajax="true"';

			// End shortcode
			$content .= ']';

			// Parse shortcode
			if ( $parse )
				$content = do_shortcode( $content );
		}

		return apply_filters( 'gf_pages_get_form_content', $content, $form, $title, $description, $ajax, $parse );
	}

/**
 * Output the current form excerpt from form description
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_excerpt() To get the form excerpt
 * @param object|int $form Optional. Form data or form ID
 * @param int $length Optional. Length of the excerpt. Defaults to 200 letters
 */
function gf_pages_form_excerpt( $form = '', $length = 200 ) {
	echo gf_pages_get_form_excerpt( $form );
}

	/**
	 * Get the current form excerpt from form description
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @param int $length Optional. Length of the excerpt. Defaults to 200 letters
	 * @return string Form excerpt
	 */
	function gf_pages_get_form_excerpt( $form = '', $length = 200 ) {
		$form    = gf_pages_get_form( $form );
		$length  = (int) $length;
		$excerpt = '';

		// var_dump( maybe_serialize( gf_get_form_meta( $form->id ) ) );

		if ( ! empty( $form ) ) {
			$excerpt = trim( strip_tags( $form->description ) );

			// Multibyte support
			if ( function_exists( 'mb_strlen' ) ) {
				$excerpt_length = mb_strlen( $excerpt );
			} else {
				$excerpt_length = strlen( $excerpt );
			}

			if ( ! empty( $length ) && ( $excerpt_length > $length ) ) {
				$excerpt  = substr( $excerpt, 0, $length - 1 );
				$excerpt .= '&hellip;';
			}
		}

		return apply_filters( 'gf_pages_get_form_excerpt', $excerpt, $form, $length );
	}

/**
 * Output the current form post date
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_post_date()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_post_date( $form = '' ) {
	echo gf_pages_get_form_post_date( $form );
}

	/**
	 * Get the current form post date
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form post date
	 */
	function gf_pages_get_form_post_date( $form = '' ) {
		$form = gf_pages_get_form( $form );
		$date = '';

		if ( ! empty( $form ) ) {
			$date = $form->date_created;
		}

		return apply_filters( 'gf_pages_get_form_post_date', $date, $form );
	}

/**
 * Output the form open date
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_open_date()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_open_date( $form = '' ) {
	echo gf_pages_get_form_open_date( $form );
}

	/**
	 * Get the current form open date
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form open date
	 */
	function gf_pages_get_form_open_date( $form = '' ) {
		$form = gf_pages_get_form( $form );
		$date = '';

		if ( ! empty( $form ) ) {

			// Form is scheduled and has defined start date
			if ( isset( $form->scheduleForm ) && $form->scheduleForm && ! empty( $form->scheduleStart ) ) {

				// Create readable date format
				$start = $form->scheduleStart . ' ' . $form->scheduleStartHour . ':' . $form->scheduleStartMinute . ' ' . $form->scheduleStartAmpm;
				$date  = DateTime::createFromFormat( 'm/d/Y g:i a', $start )->format( 'Y-m-d H:i:s' );
			} else {
				$date = gf_pages_get_form_post_date();
			}
		}

		return apply_filters( 'gf_pages_get_form_open_date', $date, $form );
	}

/**
 * Output the form close date
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_close_date()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_close_date( $form = '' ) {
	echo gf_pages_get_form_close_date( $form );
}

	/**
	 * Get the current form close date
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form close date
	 */
	function gf_pages_get_form_close_date( $form = '' ) {
		$form = gf_pages_get_form( $form );
		$date = '';

		if ( ! empty( $form ) ) {

			// Form is scheduled and has defined end date
			if ( isset( $form->scheduleForm ) && $form->scheduleForm && ! empty( $form->scheduleEnd ) ) {

				// Create readable date format
				$end  = $form->scheduleEnd . ' ' . $form->scheduleEndHour . ':' . $form->scheduleEndMinute . ' ' . $form->scheduleEndAmpm;
				$date = DateTime::createFromFormat( 'm/d/Y g:i a', $end )->format( 'Y-m-d H:i:s' );
			}
		}

		return apply_filters( 'gf_pages_get_form_close_date', $date, $form );
	}

/**
 * Output HTML with meta information for the current form-date/time
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_posted_on()
 */
function gf_pages_form_posted_on() {
	echo gf_pages_get_form_posted_on();
}

	/**
	 * Get the HTML with meta information for the current form-date/time
	 *
	 * @since 1.0.0
	 *
	 * @see _s_posted_on()
	 *
	 * @return string Form posted HTML
	 */
	function gf_pages_get_form_posted_on() {
		$posted_on = sprintf( __( 'Opened on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>', 'gravityforms-pages'), //<span class="byline"> by <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'gravityforms-pages' ),
			esc_url( gf_pages_get_form_url() ),
			esc_attr( mysql2date( get_option( 'time_format' ), gf_pages_get_form_open_date(), true ) ),
			esc_attr( mysql2date( 'c', gf_pages_get_form_open_date(), true ) ),
			esc_html( mysql2date( get_option( 'date_format'), gf_pages_get_form_open_date(), true ) )
			// esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			// esc_attr( sprintf( __( 'View all posts by %s', 'gravityforms-pages' ), get_the_author() ) ),
			// get_the_author()
		);

		return apply_filters( 'gf_pages_get_form_posted_on', $posted_on );
	}

/**
 * Return whether the form is open for entries
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form is open
 */
function gf_pages_is_form_open( $form = '' ) {
	$form = gf_pages_get_form( $form );
	$open = true;

	if ( ! empty( $form ) ) {

		// Is form schedule enabled
		if ( isset( $form->scheduleForm ) && $form->scheduleForm ) {

			// We're here before opening hours
			if ( time() < gf_pages_get_form_open_date( $form ) )
				$open = false;
		}
	}

	return (bool) apply_filters( 'gf_pages_is_form_open', $open, $form );
}

/**
 * Return whether the form is not open for entries anymore
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form is closed
 */
function gf_pages_is_form_closed( $form = '' ) {
	$form   = gf_pages_get_form( $form );
	$closed = false;

	if ( ! empty( $form ) ) {

		// Is form schedule enabled
		if ( isset( $form->scheduleForm ) && $form->scheduleForm ) {

			// We're past due date
			if ( time() > gf_pages_get_form_close_date( $form ) )
				$closed = true;
		}
	}

	return (bool) apply_filters( 'gf_pages_is_form_closed', $closed, $form );
}

/**
 * Output the current form link
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_link()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_link( $form = '' ) {
	echo gf_pages_get_form_link( $form );
}

	/**
	 * Get the current form link
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form link
	 */
	function gf_pages_get_form_link( $form = '' ) {
		$form = gf_pages_get_form( $form );
		$link = '';

		if ( ! empty( $form ) ) {
			$link = sprintf( '<a href="%s">%s</a>', gf_pages_get_form_url( $form ), gf_pages_get_form_title( $form ) );
		}

		return apply_filters( 'gf_pages_get_form_link', $link, $form );
	}

/**
 * Output the current form url
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_url()
 * @param object|int $form Form data or form ID
 */
function gf_pages_form_url( $form = '' ) {
	echo gf_pages_get_form_url( $form );
}

	/**
	 * Get the current form url
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Form data or form ID
	 * @return string Form url
	 */
	function gf_pages_get_form_url( $form = '' ) {
		global $wp_rewrite;

		$form = gf_pages_get_form( $form );
		$url  = '';

		if ( ! empty( $form ) ) {

			// Using pretty permalinks
			if ( $wp_rewrite->using_permalinks() ) {
				$url = home_url( trailingslashit( $wp_rewrite->root . gf_pages_get_single_form_slug() . '/' . gf_pages_get_form_slug( $form ) ) );

			// Unpretty permalinks
			} else {
				$url = add_query_arg( gf_pages_get_form_rewrite_id(), $form->id, home_url( '/' ) );
			}
		}

		return apply_filters( 'gf_pages_get_form_url', $url, $form );
	}

/**
 * Output the edit form link
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_link()
 * @param string $text Default link text
 * @param string $before HTML before link
 * @param string $after HTML after link
 */
function gf_pages_edit_form_link( $text = '', $before = '', $after = '' ) {
	echo gf_pages_get_edit_form_link( $text, $before, $after );
}

	/**
	 * Get the edit form link
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Default link text
	 * @param string $before HTML before link
	 * @param string $after HTML after link
	 * @return string Edit form link
	 */
	function gf_pages_get_edit_form_link( $text = '', $before = '', $after = '' ) {
		$form = gf_pages_get_form();
		$link = '';

		if ( ! empty( $form ) && GFCommon::current_user_can_any( 'gforms_edit_forms' ) ) {
			if ( empty( $text ) )
				$text = gf_pages_get_form_title();
			$link = sprintf( '%s<a href="%s" title="%s">%s</a>%s', $before, gf_pages_get_edit_form_url(), sprintf( __('Edit form %s', 'gravityforms-pages'), gf_pages_get_form_title() ), $text, $after );
		}

		return apply_filters( 'gf_pages_get_edit_form_link', $link, $form, $text, $before, $after );
	}

/**
 * Output the edit form url
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_edit_form_url()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_edit_form_url( $form = '' ) {
	echo gf_pages_get_edit_form_url( $form );
}

	/**
	 * Get the edit form url
	 *
	 * @since 1.0.0
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Edit form url
	 */
	function gf_pages_get_edit_form_url( $form = '' ) {
		$form = gf_pages_get_form( $form );
		$url  = '';

		if ( ! empty( $form ) ) {
			$url = add_query_arg( array( 'page' => 'gf_edit_forms', 'id' => $form->id ), admin_url( '/admin.php' ) );
		}

		return apply_filters( 'gf_pages_get_edit_form_url', $url, $form );
	}

/**
 * Output the current form view count
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_view_count()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_view_count( $form = '' ) {
	echo gf_pages_get_form_view_count( $form );
}

	/**
	 * Get the current form view count
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form view count
	 */
	function gf_pages_get_form_view_count( $form = '' ) {
		$form  = gf_pages_get_form( $form );
		$count = false;

		if ( ! empty( $form ) ) {
			$count = $form->view_count;
		}

		return (int) apply_filters( 'gf_pages_get_form_view_count', $count, $form );
	}

/**
 * Output the current form lead count
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_entry_count()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_entry_count( $form = '' ) {
	echo gf_pages_get_form_entry_count( $form );
}

	/**
	 * Get the current form lead count
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @todo Fix corrupt lead count, empty when it should not
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form lead count
	 */
	function gf_pages_get_form_entry_count( $form = '' ) {
		$form  = gf_pages_get_form( $form );
		$count = false;

		if ( ! empty( $form ) ) {
			$count = $form->lead_count;
		}

		return (int) apply_filters( 'gf_pages_get_form_entry_count', $count, $form );
	}

/**
 * Output the form entry count display
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_display_entry_count()
 * @param object|int $form Optional. Form data or form ID
 */
function gf_pages_form_display_entry_count( $form = '' ) {
	echo gf_pages_get_form_display_entry_count( $form );
}

	/**
	 * Get the form entry count display
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 * @uses gf_pages_get_form_entry_count()
	 * @uses gf_pages_has_form_entry_limit()
	 * @uses gf_pages_get_form_entry_limit()
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form entry count display
	 */
	function gf_pages_get_form_display_entry_count( $form = '' ) {
		$form   = gf_pages_get_form( $form );
		$retval = '';

		if ( ! empty( $form ) ) {
			$count = gf_pages_get_form_entry_count( $form );
			$class = apply_filters( 'gf_pages_form_display_entry_count_class', 'entry-count', $form );
			$args  = array();

			// Form has entry limit
			if ( gf_pages_has_form_entry_limit( $form ) ) {
				$text   = __('%s of %s Entries', 'gravityforms-pages');
				$args[] = sprintf( '<span class="%s">%d</span>', $class, $count );
				$args[] = sprintf( '<span class="entry-count-limit">%d</span>', gf_pages_get_form_entry_limit( $form ) );

			// No limit
			} else {
				$text   = _n('%s Entry', '%s Entries', $count, 'gravityforms-pages');
				$args[] = sprintf( '<span class="%s">%d</span>', $class, $count );
			}

			// Merge entry count text
			$retval = vsprintf( $text, $args );
		}

		return apply_filters( 'gf_pages_get_form_display_entry_count', $retval, $form );
	}

/** Form Archive **************************************************************/

/**
 * Output the current form archive title
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_archive_title() To get the form archive title
 */
function gf_pages_form_archive_title() {
	echo gf_pages_get_form_archive_title();
}

	/**
	 * Get the current form archive title
	 *
	 * @since 1.0.0
	 *
	 * @uses gf_pages_get_form()
	 *
	 * @return string Form archive title
	 */
	function gf_pages_get_form_archive_title() {
		$title = __('Form Archives', 'gravityforms-pages');

		return apply_filters( 'gf_pages_get_form_archive_title', $title );
	}


/**
 * Output the archive form link
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_link()
 */
function gf_pages_form_archive_link() {
	echo gf_pages_get_form_archive_link();
}

	/**
	 * Get the archive form link
	 *
	 * @since 1.0.0
	 *
	 * @return string Form link
	 */
	function gf_pages_get_form_archive_link() {
		$link = sprintf( '<a href="%s">%s</a>', gf_pages_get_form_archive_url(), gf_pages_get_form_archive_title() );

		return apply_filters( 'gf_pages_get_form_archive_link', $link );
	}

/**
 * Output the archive form url
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_archive_url()
 */
function gf_pages_form_archive_url() {
	echo gf_pages_get_form_archive_url();
}

	/**
	 * Get the archive form url
	 *
	 * @since 1.0.0
	 *
	 * @return string Form url
	 */
	function gf_pages_get_form_archive_url() {
		global $wp_rewrite;

		// Using pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = home_url( trailingslashit( $wp_rewrite->root . gf_pages_get_form_archive_slug() . '/' ) );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( gf_pages_get_form_archive_rewrite_id(), 1, home_url( '/' ) );
		}

		return apply_filters( 'gf_pages_get_form_archive_url', $url );
	}

/** Form Navigation ***********************************************************/

/**
 * Display navigation to next/previous pages when applicable
 *
 * @since 1.0.0
 *
 * @see _s_content_nav()
 *
 * @uses gf_pages_get_next_forms_link()
 * @uses gf_pages_next_forms_link()
 * @uses gf_pages_get_previous_forms_link()
 * @uses gf_pages_previous_forms_link()
 *
 * @param string $nav_id Navigation element id
 */
function gf_pages_content_nav( $nav_id ) {

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( gf_pages_is_single_form() )
		return;

	// Don't print empty markup in archives if there's only one page.
	if ( gf_pages()->form_query->max_num_pages < 2 )
		return; ?>

	<nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="navigation-paging">
		<h1 class="screen-reader-text"><?php _e( 'Form navigation', 'gravityforms-pages' ); ?></h1>

		<?php if ( gf_pages_get_next_forms_link() ) : ?>
		<div class="nav-previous"><?php gf_pages_next_forms_link( __( '<span class="meta-nav">&larr;</span> Older forms', 'gravityforms-pages' ) ); ?></div>
		<?php endif; ?>

		<?php if ( gf_pages_get_previous_forms_link() ) : ?>
		<div class="nav-next"><?php gf_pages_previous_forms_link( __( 'Newer forms <span class="meta-nav">&rarr;</span>', 'gravityforms-pages' ) ); ?></div>
		<?php endif; ?>

	</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
	<?php
}

/**
 * Output the next forms link
 *
 * @since 1.0.0
 *
 * @see next_posts_link()
 *
 * @uses gf_pages_get_next_forms_link()
 * @param string $label Content for link text
 * @param int $max_page Optional. Max pages
 */
function gf_pages_next_forms_link( $label = null, $max_page = 0 ) {
	echo gf_pages_get_next_forms_link( $label, $max_page );
}

	/**
	 * Get the next forms link
	 *
	 * @since 1.0.0
	 *
	 * @see get_next_posts_link()
	 *
	 * @uses get_pagenum_link()
	 *
	 * @param string $label Content for link text
	 * @param int $max_page Optional. Max pages
	 * @return string Next forms link
	 */
	function gf_pages_get_next_forms_link( $label = null, $max_page = 0 ) {
		global $paged;

		if ( ! $max_page )
			$max_page = gf_pages()->form_query->max_num_pages;

		if ( ! $paged )
			$paged = 1;

		$nextpage = intval( $paged ) + 1;

		if ( null === $label )
			$label = __('Next Page &raquo;', 'gravityforms-pages');

		if ( ! gf_pages_is_single_form() && $nextpage <= $max_page ) {
			$attr = apply_filters( 'next_posts_link_attributes', '' );
			return '<a href="' . get_pagenum_link( $nextpage, false ) . "\" $attr>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . '</a>';
		}
	}

/**
 * Output the previous forms link
 *
 * @since 1.0.0
 *
 * @see previous_posts_link()
 *
 * @uses gf_pages_get_previous_forms_link()
 * @param string $label Content for link text
 * @param int $max_page Optional. Max pages
 */
function gf_pages_previous_forms_link( $label = null, $max_page = 0 ) {
	echo gf_pages_get_previous_forms_link( $label, $max_page );
}

	/**
	 * Get the previous forms link
	 *
	 * @since 1.0.0
	 *
	 * @see get_previous_posts_link()
	 *
	 * @uses get_pagenum_link()
	 *
	 * @param string $label Content for link text
	 * @param int $max_page Optional. Max pages
	 * @return string Next forms link
	 */
	function gf_pages_get_previous_forms_link( $label = null, $max_page = 0 ) {
		global $paged;

		$prevpage = intval( $paged ) - 1;

		if ( $prevpage < 1 )
			$prevpage = 1;

		if ( null === $label )
			$label = __('&laquo; Previous Page', 'gravityforms-pages');

		if ( ! gf_pages_is_single_form() && $paged > 1 ) {
			$attr = apply_filters( 'previous_posts_link_attributes', '' );
			return '<a href="' . get_pagenum_link( $prevpage, false ) . "\" $attr>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . '</a>';
		}
	}

/** Form Settings Tags ********************************************************/

/**
 * Output the form class HTML tag
 *
 * @since 1.0.0
 *
 * @uses gf_pages_get_form_class()
 * @param array $classes Optional. Additional classes.
 */
function gf_pages_form_class( $classes = array() ) {
	echo gf_pages_get_form_class( $classes );
}

	/**
	 * Get the form class HTML tag
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Optional. Additional classes.
	 * @return string Form class tag
	 */
	function gf_pages_get_form_class( $classes = array() ) {
		if ( ! is_array( $classes ) )
			$classes = explode( ' ', $classes );

		// Get form specific classes
		if ( $form = gf_pages_get_form() && ! empty( $form ) )
			$classes = array_merge( $classes, explode( ' ', $form->cssClass ) );

		// Setup base classes
		$classes[] = 'form';
		$classes[] = 'gfp-form';

		// Form is active
		if ( gf_pages_is_form_active() )
			$classes[] = 'form-active';
		else
			$classes[] = 'form-inactive';

		// Form is closed
		if ( gf_pages_is_form_closed() )
			$classes[] = 'form-closed';

		// Form is not yet open
		if ( ! gf_pages_is_form_open() )
			$classes[] = 'form-not-open';

		// Login required
		if ( gf_pages_form_requires_login() )
			$classes[] = 'form-requires-login';

		// Form has user entry
		if ( gf_pages_has_form_user_entry() )
			$classes[] = 'form-user-entry';
		else
			$classes[] = 'form-no-user-entry';

		// Form has entry limit
		if ( gf_pages_has_form_entry_limit() )
			$classes[] = 'form-entry-limit';

		// Honeypot enabled
		if ( gf_pages_is_form_honeypot_enabled() )
			$classes[] = 'form-honeypot';

		// Animation enabled
		if ( gf_pages_is_form_animation_enabled() )
			$classes[] = 'form-animation';

		// Make classes filterable
		$classes = apply_filters( 'gf_pages_form_class', $classes );

		// Build class tag
		$class = '';
		if ( ! empty( $classes ) )
			$class = 'class="' . implode( ' ', array_unique( $classes ) ) . '"';

		return apply_filters( 'gf_pages_get_form_class', $class );
	}

/**
 * Return whether the form is active
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form is active
 */
function gf_pages_is_form_active( $form = '' ) {
	$form   = gf_pages_get_form( $form );
	$active = false;

	if ( ! empty( $form ) ) {
		$active = $form->is_active;
	}

	return (bool) apply_filters( 'gf_pages_is_form_active', $active, $form );
}

/**
 * Return whether the form has an entry limit
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form has entry limit
 */
function gf_pages_has_form_entry_limit( $form = '' ) {
	$form  = gf_pages_get_form( $form );
	$limit = false;

	if ( ! empty( $form ) && isset( $form->limitEntries ) ) {
		$limit = $form->limitEntries;
	}

	return (bool) apply_filters( 'gf_pages_has_form_entry_limit', $limit, $form );
}

	/**
	 * Return the form entry limit count
	 *
	 * @since 1.0.0
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return int Form entry limit
	 */
	function gf_pages_get_form_entry_limit( $form = '' ) {
		$form  = gf_pages_get_form( $form );
		$count = 0;

		if ( ! empty( $form ) && gf_pages_has_form_entry_limit( $form ) ) {
			$count = $form->limitEntriesCount;
		}

		return (int) apply_filters( 'gf_pages_get_form_entry_limit', $count, $form );
	}

	/**
	 * Return the form entry limit period
	 *
	 * @since 1.0.0
	 *
	 * @param object|int $form Optional. Form data or form ID
	 * @return string Form entry limit period
	 */
	function gf_pages_get_form_entry_limit_period( $form = '' ) {
		$form   = gf_pages_get_form( $form );
		$period = false;

		// <empty>, day, week, month, or year
		if ( ! empty( $form ) && gf_pages_has_form_entry_limit( $form ) ) {
			$period = $form->limitEntriesPeriod;
		}

		return apply_filters( 'gf_pages_get_form_entry_limit_period', $period, $form );
	}

/**
 * Return whether the form requires the user to be logged in
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form requires login
 */
function gf_pages_form_requires_login( $form = '' ) {
	$form     = gf_pages_get_form( $form );
	$required = false;

	if ( ! empty( $form ) && isset( $form->requireLogin ) ) {
		$required = $form->requireLogin;
	}

	return (bool) apply_filters( 'gf_pages_form_requires_login', $required, $form );
}

/**
 * Return whether the form has honeypot enabled
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form has honeypot enabled
 */
function gf_pages_is_form_honeypot_enabled( $form = '' ) {
	$form    = gf_pages_get_form( $form );
	$enabled = false;

	if ( ! empty( $form ) && isset( $form->enableHoneypot ) ) {
		$enabled = $form->enableHoneypot;
	}

	return (bool) apply_filters( 'gf_pages_is_form_honeypot_enabled', $enabled, $form );
}

/**
 * Return whether the form has animation enabled
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @return bool Form has animation enabled
 */
function gf_pages_is_form_animation_enabled( $form = '' ) {
	$form    = gf_pages_get_form( $form );
	$enabled = false;

	if ( ! empty( $form ) && isset( $form->enableAnimation ) ) {
		$enabled = $form->enableAnimation;
	}

	return (bool) apply_filters( 'gf_pages_is_form_animation_enabled', $enabled, $form );
}

/** Form Entry Tags ***********************************************************/

/**
 * Return whether the form has an entry from the user
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or form ID
 * @param int $user_id User ID
 * @return bool User has form filled
 */
function gf_pages_has_form_user_entry( $form = '', $user_id = 0 ) {
	global $wpdb;

	// Find form
	$form = gf_pages_get_form( $form );

	// Find user
	if ( empty( $user_id ) )
		$user_id = get_current_user_id();

	$has_entry = false;

	if ( ! empty( $form ) ) {
		$table = RGFormsModel::get_lead_table_name();

		// Logged in user
		if ( ! empty( $user_id ) ) {

			// Find user entry
			if ( $wpdb->query( $wpdb->prepare( "SELECT id FROM $table WHERE form_id = %d AND created_by = %d", $form->id, $user_id ) ) )
				$has_entry = true;

		// IP check
		} else {

			// Find user entry
			if ( $wpdb->query( $wpdb->prepare( "SELECT id FROM $table WHERE form_id = %d AND ip = %s", $form->id, RGFormsModel::get_ip() ) ) )
				$has_entry = true;
		}
	}

	return (bool) apply_filters( 'gf_pages_has_form_user_entry', $has_entry, $form, $user_id );
}



