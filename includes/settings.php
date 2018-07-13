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

			// Forms Slug
			'_gf_pages_forms_slug' => array(
				'title'             => esc_html__( 'Forms Slug', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_forms_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),
		),

		// Query
		'gf_pages_settings_query' => array(

			// Form archive title
			'_gf_pages_form_archive_title' => array(
				'title'             => esc_html__( 'Form Archive Title', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_archive_title',
				'sanitize_callback' => 'strip_tags',
				'args'              => array()
			),

			// Form archive description
			'_gf_pages_form_archive_description' => array(
				'title'             => esc_html__( 'Form Archive Description', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_archive_description',
				'sanitize_callback' => '',
				'args'              => array()
			),

			// Forms per page
			'_gf_pages_forms_per_page' => array(
				'title'             => esc_html__( 'Forms Per Page', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_forms_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Hide form archive
			'_gf_pages_hide_form_archive' => array(
				'title'             => esc_html__( 'Hide Form Archive', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_hide_form_archive',
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
 * Output the forms slug setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_forms_slug() { ?>

	<input id="_gf_pages_forms_slug" name="_gf_pages_forms_slug" type="text" class="regular-text code" value="<?php echo get_option( '_gf_pages_forms_slug', 'forms' ); ?>" />

	<?php
}

/** Query *********************************************************************/

/**
 * Query settings section description for the settings page
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_query_section() { ?>

	<p><?php esc_html_e( 'Customize the form archive and query settings here.', 'gravityforms-pages' ); ?></p>

	<?php
}

/**
 * Output the form archive title setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_form_archive_title() {
	$default_value = esc_html_x( 'Forms', 'Default form archive title', 'gravityforms-pages' ); ?>

	<input id="_gf_pages_form_archive_title" name="_gf_pages_form_archive_title" type="text" class="regular-text" value="<?php echo get_option( '_gf_pages_form_archive_title', $default_value ); ?>" />
	<br>
	<label for="_gf_pages_form_archive_title"><span class="description"><?php printf( esc_html__( 'When empty, the default title %s will be used.', 'gravityforms-pages' ), '<code>' . $default_value . '</code>' ); ?></span></label>

	<?php
}

/**
 * Output the form archive description setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_form_archive_description() { ?>

	<textarea id="_gf_pages_form_archive_description" name="_gf_pages_form_archive_description" class="large-text" rows="3" cols="50"><?php echo esc_textarea( get_option( '_gf_pages_form_archive_description', '' ) ); ?></textarea>

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
function gf_pages_admin_setting_callback_hide_form_archive() { ?>

	<input id="_gf_pages_hide_form_archive" name="_gf_pages_hide_form_archive" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_form_archive' ) ); ?> />
	<label for="_gf_pages_hide_form_archive"><span class="description"><?php printf( esc_html__( "Disable the paged forms archive listing. Visitors of %s will be routed to a 404 'Not Found' page.", 'gravityforms-pages' ), '<code>' . gf_pages_get_form_archive_url() . '</code>' ); ?></span></label>

	<?php
}

/**
 * Output the forms per page setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_hide_closed_forms() { ?>

	<input id="_gf_pages_hide_closed_forms" name="_gf_pages_hide_closed_forms" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_closed_forms' ) ); ?> />
	<label for="_gf_pages_hide_closed_forms"><span class="description"><?php esc_html_e( 'Hide paged forms that are closed to new entries.', 'gravityforms-pages' ); ?></span></label>

	<?php
}

/** Additional ****************************************************************/

/**
 * Additional settings section description for the settings page
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_additional_section() { ?>

	<p><?php esc_html_e( 'Customize additional settings here.', 'gravityforms-pages' ); ?></p>

	<?php
}

/**
 * Output the force ajax setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_force_ajax() { ?>

	<input id="_gf_pages_force_ajax" name="_gf_pages_force_ajax" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_force_ajax' ) ); ?> />
	<label for="_gf_pages_force_ajax"><span class="description"><?php esc_html_e( 'Always use interactive ajax functionality in paged forms.', 'gravityforms-pages' ); ?></span></label>

	<?php
}
