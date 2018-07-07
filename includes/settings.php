<?php

/**
 * Gravity Forms Pages Settings
 *
 * @package Gravity Forms Pages
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Settings ******************************************************************/

/**
 * Get the plugin settings sections
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_settings_sections'
 * @return array Settings sections
 */
function gf_pages_admin_get_settings_sections() {
	return apply_filters( 'gf_pages_admin_get_settings_sections', array(

		// Form slugs
		'gf_pages_settings_slugs' => array(
			'title'    => esc_html__( 'Form slugs', 'gravityforms-pages' ),
			'callback' => 'gf_pages_admin_setting_callback_slugs_section',
			'page'     => 'gf-pages'
		),

		// Query
		'gf_pages_settings_query' => array(
			'title'    => esc_html__( 'Query settings', 'gravityforms-pages' ),
			'callback' => 'gf_pages_admin_setting_callback_query_section',
			'page'     => 'gf-pages'
		),

		// Additional
		'gf_pages_settings_additional' => array(
			'title'    => esc_html__( 'Additional settings', 'gravityforms-pages' ),
			'callback' => 'gf_pages_admin_setting_callback_additional_section',
			'page'     => 'gf-pages'
		)
	) );
}

/**
 * Get the plugin settings fields
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_settings_fields'
 * @return array Settings fields
 */
function gf_pages_admin_get_settings_fields() {
	return apply_filters( 'gf_pages_admin_get_settings_fields', array(

		// Form Slugs
		'gf_pages_settings_slugs' => array(

			// Form Slug
			'_gf_pages_form_slug' => array(
				'title'             => esc_html__( 'Single Form Slug', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// Form Archive Slug
			'_gf_pages_form_archive_slug' => array(
				'title'             => esc_html__( 'Form Archive Slug', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_archive_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			)
		),

		// Query
		'gf_pages_settings_query' => array(

			// Hide form archive
			'_gf_pages_hide_form_archive' => array(
				'title'             => esc_html__( 'Hide Form Archive', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_hide_form_archive',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Forms per page
			'_gf_pages_forms_per_page' => array(
				'title'             => esc_html__( 'Forms Per Page', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_forms_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Hide closed forms
			'_gf_pages_hide_closed_forms' => array(
				'title'             => esc_html__( 'Hide Closed Forms', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_hide_closed_forms',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),
		),

		// Additional
		'gf_pages_settings_additional' => array(

			// Force ajax
			'_gf_pages_force_ajax' => array(
				'title'             => esc_html__( 'Force Ajax Forms', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_force_ajax',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Show view count
			'_gf_pages_show_view_count' => array(
				'title'             => esc_html__( 'Show View Count', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_show_view_count',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Show entry count
			'_gf_pages_show_entry_count' => array(
				'title'             => esc_html__( 'Show Entry Count', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_show_entry_count',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),
		)
	) );
}

/**
 * Get settings fields per section
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_settings_fields_for_section'
 *
 * @param string $section_id
 * @return array Settings fields
 */
function gf_pages_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = gf_pages_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return (array) apply_filters( 'gf_pages_admin_get_settings_fields_for_section', $retval, $section_id );
}

/** Slugs *********************************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_slugs_section() {

	// Flush rewrite rules when this section is saved
	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) )
		flush_rewrite_rules(); ?>

	<p><?php esc_html_e( 'Customize your form permalink structure here.', 'gravityforms-pages' ); ?></p>

	<?php
}

/**
 * Output the form slug setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_form_slug() { ?>

	<input id="_gf_pages_form_slug" name="_gf_pages_form_slug" type="text" class="regular-text code" value="<?php echo get_option( '_gf_pages_form_slug', 'form' ); ?>" />

	<?php
}

/**
 * Output the form archive slug setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_form_archive_slug() { ?>

	<input id="_gf_pages_form_archive_slug" name="_gf_pages_form_archive_slug" type="text" class="regular-text code" value="<?php echo get_option( '_gf_pages_form_archive_slug', 'forms' ); ?>" />

	<?php
}

/** Query *********************************************************************/

/**
 * Query settings section description for the settings page
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_query_section() { ?>

	<p><?php esc_html_e( 'Set single form and form archive query settings here.', 'gravityforms-pages' ); ?></p>

	<?php
}

/**
 * Output the forms per page setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_hide_form_archive() { ?>

	<input id="_gf_pages_hide_form_archive" name="_gf_pages_hide_form_archive" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_form_archive' ) ); ?> />
	<label for="_gf_pages_hide_form_archive"><span class="description"><?php _e("Disable the forms archive listing page. Users will be routed to a 404 'Not Found' page.", 'gravityforms-pages' ); ?></span></label>

	<?php
}

/**
 * Output the forms per page setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_forms_per_page() { ?>

	<input id="_gf_pages_forms_per_page" name="_gf_pages_forms_per_page" type="number" min="0" step="1" value="<?php echo get_option( '_gf_pages_forms_per_page', 10 ); ?>" />

	<?php
}

/**
 * Output the forms per page setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_hide_closed_forms() { ?>

	<input id="_gf_pages_hide_closed_forms" name="_gf_pages_hide_closed_forms" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_closed_forms' ) ); ?> />
	<label for="_gf_pages_hide_closed_forms"><span class="description"><?php _e( 'Hide forms that are closed to new entries.', 'gravityforms-pages' ); ?></span></label>

	<?php
}

/** Additional ****************************************************************/

/**
 * Additional settings section description for the settings page
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_additional_section() { ?>

	<p><?php esc_html_e( 'Set additional settings here.', 'gravityforms-pages' ); ?></p>

	<?php
}

/**
 * Output the force ajax setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_force_ajax() { ?>

	<input id="_gf_pages_force_ajax" name="_gf_pages_force_ajax" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_force_ajax' ) ); ?> />
	<label for="_gf_pages_force_ajax"><span class="description"><?php _e( 'Always use interactive ajax functionality in forms.', 'gravityforms-pages' ); ?></span></label>

	<?php
}

/**
 * Output the show view count setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_show_view_count() { ?>

	<input id="_gf_pages_show_view_count" name="_gf_pages_show_view_count" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_show_view_count' ) ); ?> />
	<label for="_gf_pages_show_view_count"><span class="description"><?php _e( 'Show the form view count on form pages.', 'gravityforms-pages' ); ?></span></label>

	<?php
}

/**
 * Output the show entry count setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_show_entry_count() { ?>

	<input id="_gf_pages_show_entry_count" name="_gf_pages_show_entry_count" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_show_entry_count' ) ); ?> />
	<label for="_gf_pages_show_entry_count"><span class="description"><?php _e( 'Show the form entry count on form pages.', 'gravityforms-pages' ); ?></span></label>

	<?php
}

