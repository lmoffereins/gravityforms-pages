<?php

/**
 * Gravity Forms Pages Forms Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Query *********************************************************************/

/**
 * Setup and run the Forms query
 *
 * @since 1.0.0
 *
 * @param array $args Query arguments.
 * @return bool Has the query returned any results?
 */
function gf_pages_query_forms( $args = array() ) {

	// Get query object
	$query = gf_pages()->form_query;

	// Reset query defaults
	$query->in_the_loop  = false;
	$query->current_form = -1;
	$query->form_count   = 0;
	$query->form         = null;
	$query->forms        = array();

	// Define query args
	$r = wp_parse_args( $args, array(
		'number'      => gf_pages_get_forms_per_page(),
		'paged'       => gf_pages_get_paged(),
		'fields'      => 'all',
		'show_active' => true,
		'orderby'     => 'date_created',
		'order'       => 'DESC'
	) );

	// Pagination
	if ( (int) $r['number'] > 0 ) {
		$r['paged'] = absint( $r['paged'] );
		if ( $r['paged'] == 0 ) {
			$r['paged'] = 1;
		}
		$r['offset'] = absint( ( $r['paged'] - 1 ) * $r['number'] );
	} else {
		$r['number'] = -1;
	}

	// Run query to get the forms
	if ( method_exists( $query, 'query' ) ) {
		$query->query( $r );
	} else {
		$query->forms = gf_pages_get_forms( $r );
	}

	// Set query results
	$query->form_count = count( $query->forms );
	if ( $query->form_count > 0 ) {
		$query->form = $query->forms[0];
	}

	// Determine the total form count
	if ( isset( $r['offset'] ) && ! $query->form_count < $r['number'] ) {
		$query->found_forms = gf_pages_query_forms_found_rows( $r );
	} else {
		$query->found_forms = $query->form_count;
	}
	if ( $query->found_forms > $query->form_count ) {
		$query->max_num_pages = (int) ceil( $query->found_forms / $r['number'] );
	} else {
		$query->max_num_pages = 1;
	}

	// Return whether the query has returned results
	return gf_pages_has_forms();
}

/**
 * Return whether the query has Forms to loop over
 *
 * @since 1.0.0
 *
 * @return bool Query has Forms
 */
function gf_pages_has_forms() {

	// Get query object
	$query = gf_pages()->form_query;

	// Get array keys
	$form_keys = array_keys( $query->forms );

	// Current element is not the last
	$has_next = $query->form_count && $query->current_form < end( $form_keys );

	// We're in the loop when there are still elements
	if ( ! $has_next ) {
		$query->in_the_loop = false;

		// Clean up after the loop
		gf_pages_rewind_forms();
	}

	return $has_next;
}

/**
 * Setup next Volume in the current loop
 *
 * @since 1.0.0
 */
function gf_pages_the_form() {

	// Get query object
	$query = gf_pages()->form_query;

	// We're looping
	$query->in_the_loop = true;

	// Increase current form index
	$query->current_form++;

	// Get next form in list
	if ( isset( $query->forms[ $query->current_form ] ) ) {
		$query->form = $query->forms[ $query->current_form ];
	}
}

/**
 * Rewind the forms and reset form index
 *
 * @since 1.0.0
 */
function gf_pages_rewind_forms() {

	// Get query object
	$query = gf_pages()->form_query;

	// Reset current form index
	$query->current_form = -1;

	if ( $query->form_count > 0 ) {
		$query->form = $query->forms[0];
	}
}

/**
 * Return whether we're in the Form loop
 *
 * @since 1.0.0
 *
 * @return bool Are we in the Form loop?
 */
function gf_pages_in_the_form_loop() {
	return isset( gf_pages()->form_query->in_the_loop ) ? gf_pages()->form_query->in_the_loop : false;
}

/** Single Form ***************************************************************/

/**
 * Output the current form ID
 *
 * @since 1.0.0
 */
function gf_pages_the_form_id() {
	echo gf_pages_get_form_id();
}

	/**
	 * Get the current form ID
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_id'
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
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_title( $form = 0 ) {
	echo gf_pages_get_form_title( $form );
}

	/**
	 * Get the current form title
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_title'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form title
	 */
	function gf_pages_get_form_title( $form = 0 ) {
		$form  = gf_pages_get_form( $form );
		$title = '';

		if ( ! empty( $form ) ) {
			$title = $form->title;
		}

		return apply_filters( 'gf_pages_get_form_title', $title, $form );
	}

/**
 * Output the current form description
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_description( $form = 0 ) {
	echo gf_pages_get_form_description( $form );
}

	/**
	 * Get the current form description
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_description'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form description
	 */
	function gf_pages_get_form_description( $form = 0 ) {
		$form = gf_pages_get_form( $form );
		$desc = '';

		if ( ! empty( $form ) ) {
			$desc = $form->description;

			// Provide default description
			if ( empty( $desc ) ) {
				$desc = apply_filters( 'gf_pages_default_form_description', esc_html__( 'There is no description provided for this form.', 'gravityforms-pages' ), $form );
			}
		}

		return apply_filters( 'gf_pages_get_form_description', $desc, $form );
	}

/**
 * Output the current form content
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @param bool $title Optional. Whether to add the form title. Defaults to false.
 * @param bool $description Optional. Whether to add the form description. Defaults to true.
 * @param bool $ajax Optional. Whether to add ajax functionality. Defaults to false.
 */
function gf_pages_the_form_content( $form = 0, $title = false, $description = true, $ajax = false ) {
	echo gf_pages_get_form_content( $form, $title, $description, $ajax );
}

	/**
	 * Get the current form content
	 *
	 * Builds the form shortcode and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_content'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @param bool $title Optional. Whether to add the form title. Defaults to false.
	 * @param bool $description Optional. Whether to add the form description. Defaults to true.
	 * @param bool $ajax Optional. Whether to add ajax functionality. Defaults to false.
	 * @return string Form content
	 */
	function gf_pages_get_form_content( $form = 0, $title = false, $description = true, $ajax = false ) {
		$form    = gf_pages_get_form( $form );
		$content = '';

		// Build shortcode
		if ( ! empty( $form ) ) {

			// Start shortcode
			$content = '[gravityforms id="' . $form->id . '"';

			if ( ! $title ) {
				$content .= ' title="false"';
			}

			// With description
			if ( ! $description ) {
				$content .= ' description="false"';
			}

			// With AJAX
			if ( $ajax || gf_pages_force_ajax() ) {
				$content .= ' ajax="true"';
			}

			// End shortcode
			$content .= ']';
		}

		return apply_filters( 'gf_pages_get_form_content', $content, $form, $title, $description, $ajax );
	}

/**
 * Output the current form excerpt from form description
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @param int $length Optional. Length of the excerpt. Defaults to 55 words.
 */
function gf_pages_the_form_excerpt( $form = 0, $length = 55 ) {
	echo gf_pages_get_form_excerpt( $form );
}

	/**
	 * Get the current form excerpt from form description
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_excerpt'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @param int $length Optional. Length of the excerpt in words. Defaults to 55 words.
	 * @return string Form excerpt
	 */
	function gf_pages_get_form_excerpt( $form = 0, $length = 55 ) {
		$form    = gf_pages_get_form( $form );
		$length  = (int) $length;
		$desc    = gf_get_form_description( $form );
		$excerpt = '';

		// When form description is available
		if ( ! empty( $form ) && $desc ) {
			$excerpt = trim( strip_tags( $desc ) );
			$excerpt = wp_trim_words( $excerpt, $length );
		}

		return apply_filters( 'gf_pages_get_form_excerpt', $excerpt, $form, $length );
	}

/**
 * Output the current form post date
 *
 * @since 1.0.0
 *
 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_post_date( $format = 'Y-m-d', $form = 0 ) {
	echo gf_pages_get_form_post_date( $format, $form );
}

	/**
	 * Get the current form post date
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_post_date'
	 *
	 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form post date
	 */
	function gf_pages_get_form_post_date( $format = 'Y-m-d', $form = 0 ) {
		$form      = gf_pages_get_form( $form );
		$date      = false;
		$formatted = '';

		if ( ! empty( $form ) ) {
			$date = $form->date_created;

			if ( $date ) {
				$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
				$formatted = date_i18n( $format, $date->format( 'U' ) );
			}
		}

		return apply_filters( 'gf_pages_get_form_post_date', $formatted, $format, $form, $date );
	}

/**
 * Output the form open date
 *
 * @since 1.0.0
 *
 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_open_date( $format = 'Y-m-d', $form = 0 ) {
	echo gf_pages_get_form_open_date( $format, $form );
}

	/**
	 * Get the current form open date
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_open_date'
	 *
	 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form open date
	 */
	function gf_pages_get_form_open_date( $format = 'Y-m-d', $form = 0 ) {
		$form      = gf_pages_get_form( $form );
		$date      = false;
		$formatted = '';

		if ( ! empty( $form ) ) {

			// Form is scheduled and has defined start date
			if ( isset( $form->scheduleForm ) && $form->scheduleForm && ! empty( $form->scheduleStart ) ) {

				// Force leading zeros on minutes
				$minutes = sprintf( '%02d', $form->scheduleStartMinute );

				// Create readable date format
				$date = "{$form->scheduleStart} {$form->scheduleStartHour}:{$minutes} {$form->scheduleStartAmpm}";
				$date = DateTime::createFromFormat( 'm/d/Y g:i a', $date );
				$formatted = date_i18n( $format, $date->format( 'U' ) );
			} else {
				$formatted = gf_pages_get_form_post_date( $format, $form );
			}
		}

		return apply_filters( 'gf_pages_get_form_open_date', $formatted, $format, $form, $date );
	}

/**
 * Output the form close date
 *
 * @since 1.0.0
 *
 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_close_date( $format = 'Y-m-d', $form = 0 ) {
	echo gf_pages_get_form_close_date( $format, $form );
}

	/**
	 * Get the current form close date
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_close_date'
	 *
	 * @param string $format Optional. Date format. Defaults to 'Y-m-d'.
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form close date
	 */
	function gf_pages_get_form_close_date( $format = 'Y-m-d', $form = 0 ) {
		$form      = gf_pages_get_form( $form );
		$date      = false;
		$formatted = '';

		if ( ! empty( $form ) ) {

			// Form is scheduled and has defined end date
			if ( isset( $form->scheduleForm ) && $form->scheduleForm && ! empty( $form->scheduleEnd ) ) {

				// Force leading zeros on minutes
				$minutes = sprintf( '%02d', $form->scheduleEndMinute );

				// Create readable date format
				$date = "{$form->scheduleEnd} {$form->scheduleEndHour}:{$minutes} {$form->scheduleEndAmpm}";
				$date = DateTime::createFromFormat( 'm/d/Y g:i a', $date );
				$formatted = date_i18n( $format, $date->format( 'U' ) );
			}
		}

		return apply_filters( 'gf_pages_get_form_close_date', $formatted, $format, $form, $date );
	}

/**
 * Return whether the form is open for entries
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_is_form_open'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form is open
 */
function gf_pages_is_form_open( $form = 0 ) {
	$form = gf_pages_get_form( $form );
	$open = true;

	if ( ! empty( $form ) ) {

		// Inactive forms are closed
		if ( gf_pages_is_form_inactive( $form ) ) {
			$open = false;

		// Is form schedule enabled
		} elseif ( isset( $form->scheduleForm ) && $form->scheduleForm ) {

			// We're here before opening hours
			if ( time() < gf_pages_get_form_open_date( 'U', $form ) ) {
				$open = false;
			}
		}
	}

	return (bool) apply_filters( 'gf_pages_is_form_open', $open, $form );
}

/**
 * Return whether the form is not open for entries anymore
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_is_form_closed'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form is closed
 */
function gf_pages_is_form_closed( $form = 0 ) {
	$form   = gf_pages_get_form( $form );
	$closed = false;

	if ( ! empty( $form ) ) {

		// Inactive forms are closed
		if ( gf_pages_is_form_inactive( $form ) ) {
			$closed = true;

		// Is form schedule enabled
		} elseif ( isset( $form->scheduleForm ) && $form->scheduleForm ) {

			// We're past due date
			if ( time() > gf_pages_get_form_close_date( 'U', $form ) ) {
				$closed = true;
			}
		}
	}

	return (bool) apply_filters( 'gf_pages_is_form_closed', $closed, $form );
}

/**
 * Output the current form link
 *
 * @since 1.0.0
 *
 * @param array $args See {@see gf_pages_get_form_link()}.
 */
function gf_pages_the_form_link( $args = array() ) {
	echo gf_pages_get_form_link( $args );
}

	/**
	 * Get the current form link
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_link'
	 *
	 * @param array $args Function arguments, supports these args:
	 *  - form: Optional. Form object or ID. Defaults to the current form.
	 *  - link_before: Optional. Markup to put before the link. Defaults to an empty string.
	 *  - link_after: Optional. Markup to put after the link. Defaults to an empty string.
	 *  - link_text: Optional. Link text. Defaults to the form title.
	 * @return string Form link
	 */
	function gf_pages_get_form_link( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'form'        => 0,
			'link_before' => '',
			'link_after'  => '',
			'link_text'   => '',
		) );

		$form = gf_pages_get_form( $r['form'] );
		$link = '';

		if ( ! empty( $form ) ) {
			$url = gf_pages_get_form_url( $form );

			if ( $url ) {
				$link = sprintf( '%s<a href="%s" title="%s">%s</a>%s',
					$r['link_before'],
					esc_url( $url ),
					sprintf( esc_html__( 'Edit form %s', 'gravityforms-pages' ), gf_pages_get_form_title( $form ) ),
					! empty( $r['link_text'] ) ? $r['link_text'] : gf_pages_get_form_title( $form ),
					$r['link_after']
				);
			}
		}

		return apply_filters( 'gf_pages_get_form_link', $link, $form, $r );
	}

/**
 * Output the current form url
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_url( $form = 0 ) {
	echo gf_pages_get_form_url( $form );
}

	/**
	 * Get the current form url
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_url'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form url
	 */
	function gf_pages_get_form_url( $form = 0 ) {
		global $wp_rewrite;

		$form = gf_pages_get_form( $form );
		$url  = '';

		if ( ! empty( $form ) ) {

			// Using pretty permalinks
			if ( $wp_rewrite->using_permalinks() ) {
				$url = home_url( trailingslashit( $wp_rewrite->root . gf_pages_get_forms_slug() . '/' . gf_pages_get_form_slug( $form ) ) );

			// Unpretty permalinks
			} else {
				$url = add_query_arg( gf_pages_get_form_rewrite_id(), $form->id, home_url( '/' ) );
			}
		}

		return apply_filters( 'gf_pages_get_form_url', $url, $form );
	}

/**
 * Output the form's edit link
 *
 * @since 1.0.0
 *
 * @param array $args See {@see gf_pages_get_form_edit_link()}.
 */
function gf_pages_the_form_edit_link( $args = array() ) {
	echo gf_pages_get_form_edit_link( $args );
}

	/**
	 * Get the form's edit link
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_edit_link'
	 *
	 * @param array $args Function arguments, supports these args:
	 *  - form: Optional. Form object or ID. Defaults to the current form.
	 *  - link_before: Optional. Markup to put before the link. Defaults to an empty string.
	 *  - link_after: Optional. Markup to put after the link. Defaults to an empty string.
	 *  - link_text: Optional. Link text. Defaults to 'Edit'.
	 * @return string Edit form link
	 */
	function gf_pages_get_form_edit_link( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'form'        => 0,
			'link_before' => '',
			'link_after'  => '',
			'link_text'   => esc_html__( 'Edit', 'gravityforms-pages' ),
		) );

		$form = gf_pages_get_form( $r['form'] );
		$link = '';

		if ( ! empty( $form ) ) {
			$url = gf_pages_get_form_edit_url( $form );

			if ( $url ) {
				$link = sprintf( '%s<a href="%s" title="%s">%s</a>%s',
					$r['link_before'],
					esc_url( $url ),
					sprintf( esc_html__( 'Edit form %s', 'gravityforms-pages' ), gf_pages_get_form_title( $form ) ),
					$r['link_text'],
					$r['link_after']
				);
			}
		}

		return apply_filters( 'gf_pages_get_form_edit_link', $link, $form, $r );
	}

/**
 * Output the form's edit url
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_edit_url( $form = 0 ) {
	echo gf_pages_get_form_edit_url( $form );
}

	/**
	 * Get the form's edit url
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_edit_url'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Edit form url
	 */
	function gf_pages_get_form_edit_url( $form = 0 ) {
		$form = gf_pages_get_form( $form );
		$url  = '';

		if ( ! empty( $form ) ) {
			$url = add_query_arg( array( 'page' => 'gf_edit_forms', 'id' => $form->id ), admin_url( 'admin.php' ) );
		}

		return apply_filters( 'gf_pages_get_form_edit_url', $url, $form );
	}

/**
 * Output the view form entries url
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_view_form_entries_url( $form = 0 ) {
	echo gf_pages_get_view_form_entries_url( $form );
}

	/**
	 * Get the view form entries url
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_view_form_entries_url'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Edit form url
	 */
	function gf_pages_get_view_form_entries_url( $form = 0 ) {
		$form = gf_pages_get_form( $form );
		$url  = '';

		if ( ! empty( $form ) ) {
			$url = add_query_arg( array( 'page' => 'gf_entries', 'id' => $form->id ), admin_url( '/admin.php' ) );
		}

		return apply_filters( 'gf_pages_get_view_form_entries_url', $url, $form );
	}

/**
 * Output the current form view count
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_view_count( $form = 0 ) {
	echo gf_pages_get_form_view_count( $form );
}

	/**
	 * Get the current form view count
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_view_count'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form view count
	 */
	function gf_pages_get_form_view_count( $form = 0 ) {
		$form  = gf_pages_get_form( $form );
		$count = 0;

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
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_entry_count( $form = 0 ) {
	echo gf_pages_get_form_entry_count( $form );
}

	/**
	 * Get the current form lead count
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_entry_count'
	 *
	 * @todo Fix corrupt lead count, empty when it should not
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form lead count
	 */
	function gf_pages_get_form_entry_count( $form = 0 ) {
		$form  = gf_pages_get_form( $form );
		$count = 0;

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
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 */
function gf_pages_the_form_display_entry_count( $form = 0 ) {
	echo gf_pages_get_form_display_entry_count( $form );
}

	/**
	 * Get the form entry count display
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_form_display_entry_count_class'
	 * @uses apply_filters() Calls 'gf_pages_get_form_display_entry_count'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form entry count display
	 */
	function gf_pages_get_form_display_entry_count( $form = 0 ) {
		$form   = gf_pages_get_form( $form );
		$retval = '';

		if ( ! empty( $form ) ) {
			$count = gf_pages_get_form_entry_count( $form );
			$class = apply_filters( 'gf_pages_form_display_entry_count_class', 'entry-count', $form );
			$args  = array();

			// Form has entry limit
			if ( gf_pages_has_form_entry_limit( $form ) ) {
				$text   = __( '%s of %s Entries', 'gravityforms-pages' );
				$args[] = sprintf( '<span class="%s">%d</span>', $class, $count );
				$args[] = sprintf( '<span class="entry-count-limit">%d</span>', gf_pages_get_form_entry_limit( $form ) );

			// No limit
			} else {
				$text   = _n( '%s Entry', '%s Entries', $count, 'gravityforms-pages' );
				$args[] = sprintf( '<span class="%s">%d</span>', $class, $count );
			}

			// Merge entry count text
			$retval = vsprintf( $text, $args );
		}

		return apply_filters( 'gf_pages_get_form_display_entry_count', $retval, $form );
	}

/** Form Archive **************************************************************/

/**
 * Output the form archive title
 *
 * @since 1.0.0
 */
function gf_pages_the_form_archive_title() {
	echo gf_pages_get_form_archive_title();
}

	/**
	 * Get the form archive title
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_form_archive_title'
	 * @return string Form archive title
	 */
	function gf_pages_get_form_archive_title() {
		$title = gf_pages_form_archive_title();

		// Set default title
		if ( ! $title ) {
			$title = esc_html_x( 'Forms', 'Default form archive title', 'gravityforms-pages' );
		}

		return apply_filters( 'gf_pages_get_form_archive_title', $title );
	}

/**
 * Output the form archive description
 *
 * @since 1.0.0
 */
function gf_pages_the_form_archive_description() {
	echo gf_pages_get_form_archive_description();
}

	/**
	 * Get the form archive description
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_form_archive_description'
	 * @return string Form archive description
	 */
	function gf_pages_get_form_archive_description() {
		return apply_filters( 'gf_pages_get_form_archive_description', gf_pages_form_archive_description() );
	}

/**
 * Output the archive form link
 *
 * @since 1.0.0
 *
 * @param array $args See {@see gf_pages_get_form_archive_link()}.
 */
function gf_pages_the_form_archive_link( $args = array() ) {
	echo gf_pages_get_form_archive_link( $args );
}

	/**
	 * Get the archive form link
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_archive_link'
	 *
	 * @param array $args Function arguments, supports these args:
	 *  - link_before: Optional. Markup to put before the link. Defaults to an empty string.
	 *  - link_after: Optional. Markup to put after the link. Defaults to an empty string.
	 *  - link_text: Optional. Link text. Defaults to the archive title.
	 * @return string Form link
	 */
	function gf_pages_get_form_archive_link( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'link_before' => '',
			'link_after'  => '',
			'link_text'   => gf_pages_get_form_archive_title(),
		) );

		$url  = gf_pages_get_form_archive_url();
		$link = '';

		if ( $url ) {
			$link = sprintf( '%s<a href="%s">%s</a>%s',
				$r['link_before'],
				esc_url( $url ),
				$r['link_text'],
				$r['link_after']
			);
		}

		return apply_filters( 'gf_pages_get_form_archive_link', $link, $r );
	}

/**
 * Output the archive form url
 *
 * @since 1.0.0
 */
function gf_pages_the_form_archive_url() {
	echo gf_pages_get_form_archive_url();
}

	/**
	 * Get the archive form url
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_get_form_archive_url'
	 * @return string Form url
	 */
	function gf_pages_get_form_archive_url() {
		global $wp_rewrite;

		// Using pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = home_url( trailingslashit( $wp_rewrite->root . gf_pages_get_forms_slug() . '/' ) );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( gf_pages_get_form_archive_rewrite_id(), 1, home_url( '/' ) );
		}

		return apply_filters( 'gf_pages_get_form_archive_url', $url );
	}

/** Form Settings *************************************************************/

/**
 * Return whether the form is inactive
 *
 * @since 1.0.0
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form is inactive
 */
function gf_pages_is_form_inactive( $form = 0 ) {
	return ! gf_pages_is_form_active( $form );
}

	/**
	 * Return whether the form is active
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'gf_pages_is_form_active'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return bool Form is active
	 */
	function gf_pages_is_form_active( $form = 0 ) {
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
 * @uses apply_filters() Calls 'gf_pages_has_form_entry_limit'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form has entry limit
 */
function gf_pages_has_form_entry_limit( $form = 0 ) {
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
	 * @uses apply_filters() Calls 'gf_pages_get_form_entry_limit'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return int Form entry limit
	 */
	function gf_pages_get_form_entry_limit( $form = 0 ) {
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
	 * @uses apply_filters() Calls 'gf_pages_get_form_entry_limit_period'
	 *
	 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
	 * @return string Form entry limit period
	 */
	function gf_pages_get_form_entry_limit_period( $form = 0 ) {
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
 * @uses apply_filters() Calls 'gf_pages_form_requires_login'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form requires login
 */
function gf_pages_form_requires_login( $form = 0 ) {
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
 * @uses apply_filters() Calls 'gf_pages_is_form_honeypot_enabled'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form has honeypot enabled
 */
function gf_pages_is_form_honeypot_enabled( $form = 0 ) {
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
 * @uses apply_filters() Calls 'gf_pages_is_form_animation_enabled'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @return bool Form has animation enabled
 */
function gf_pages_is_form_animation_enabled( $form = 0 ) {
	$form    = gf_pages_get_form( $form );
	$enabled = false;

	if ( ! empty( $form ) && isset( $form->enableAnimation ) ) {
		$enabled = $form->enableAnimation;
	}

	return (bool) apply_filters( 'gf_pages_is_form_animation_enabled', $enabled, $form );
}

/** Form Entry ****************************************************************/

/**
 * Return whether the form has an entry from the user
 *
 * @since 1.0.0
 *
 * @uses WPDB $wpdb
 * @uses apply_filters() Calls 'gf_pages_has_form_user_entry'
 *
 * @param object|int $form Optional. Form data or ID. Defaults to the current form.
 * @param int $user_id User ID
 * @return bool User has form filled
 */
function gf_pages_has_form_user_entry( $form = 0, $user_id = 0 ) {
	global $wpdb;

	// Find form
	$form = gf_pages_get_form( $form );

	// Default to current user
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$has_entry = false;

	if ( ! empty( $form ) ) {
		$lead_table = GFFormsModel::get_lead_table_name();

		// Logged in user
		if ( ! empty( $user_id ) ) {

			// Find user entry
			if ( $wpdb->query( $wpdb->prepare( "SELECT id FROM {$lead_table} WHERE form_id = %d AND created_by = %d", $form->id, $user_id ) ) ) {
				$has_entry = true;
			}

		// IP check
		} else {

			// Find user entry
			if ( $wpdb->query( $wpdb->prepare( "SELECT id FROM {$lead_table} WHERE form_id = %d AND ip = %s", $form->id, GFFormsModel::get_ip() ) ) ) {
				$has_entry = true;
			}
		}
	}

	return (bool) apply_filters( 'gf_pages_has_form_user_entry', $has_entry, $form, $user_id );
}
