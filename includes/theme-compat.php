<?php

/**
 * Gravity Forms Pages Theme Compatability Functions
 *
 * @package Gravity Forms
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
 * Output the forms template part
 *
 * @since 1.0.0
 *
 * @see get_template_part()
 *
 * @uses do_action() Calls 'get_template_part_$slug'
 * @uses apply_filters() Calls 'gf_pages_get_template_part'
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
