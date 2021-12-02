<?php

/**
 * Gravity Forms Pages Settings
 *
 * @package Gravity Forms Pages
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Settings ******************************************************************/

/**
 * Return settings fields for the new Settings layout since GF 2.5
 *
 * @since 1.0.3
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_settings_fields_for_gf_2_5'
 * @uses apply_filters() Calls 'gf_pages_admin_get_primary_settings_field_for_gf_2_5'
 *
 * @return array Settings fields
 */
function gf_pages_admin_get_settings_fields_for_gf_2_5() {

	// Define page heading field
	$settings_fields = array(
		'heading' => array(
			'title'       => esc_html__( 'Forms Pages', 'gravityforms-pages' ),
			'class'       => 'gform-settings-panel--full',
			'description' => esc_html__( 'Setup the structure of the paged forms archive listing and single forms pages.', 'gravityforms-pages' ),
			'fields'      => array( array( 'type' => 'html' ) )
		)
	);

	// Walk legacy settings fields for setup
	foreach ( gf_pages_admin_get_settings_fields() as $section => $fields ) {
		foreach ( $fields as $option_name => $args ) {

			// Get field name without prefix
			$field_name = isset( $args['name'] ) ? $args['name'] : str_replace( '_gf_pages_', '', $option_name );

			// Skip field when not indicated for inclusion
			if ( ! isset( $args['args']['type'] ) ) {
				continue;
			}

			// Identify any sub fields
			$sub_fields = array();
			if ( isset( $args['args']['sub_fields'] ) ) {
				$sub_fields = $args['args']['sub_fields'];
				unset( $args['args']['sub_fields'] );
			}

			// Set field attributes
			$settings_fields[ $option_name ] = wp_parse_args( $args, array(
				'id'     => 'section_' . $option_name,
				'class'  => 'gform-settings-panel--half',
				'fields' => array_merge( array(
					wp_parse_args( $args['args'], array(
						'name' => $field_name
					) )
				), $sub_fields )
			) );
		}
	}

	// Put primary option in the heading
	$primary_option_name = apply_filters( 'gf_pages_admin_get_primary_settings_field_for_gf_2_5', '_gf_pages_forms_slug' );
	if ( isset( $settings_fields[ $primary_option_name ] ) ) {
		$settings_fields['heading']['fields'][] = $settings_fields[ $primary_option_name ]['fields'][0];
		unset( $settings_fields[ $primary_option_name ] );
	}

	return apply_filters( 'gf_pages_admin_get_settings_fields_for_gf_2_5', $settings_fields );
}

/**
 * Return settings fields' initial values for the new Settings layout since GF 2.5
 *
 * @since 1.0.3
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_settings_fields_initial_values_for_gf_2_5'
 * @return array Settings fields' initial values
 */
function gf_pages_admin_get_settings_fields_initial_values_for_gf_2_5() {

	// Define retval
	$initial_values = array();

	// Walk legacy settings fields for setup
	foreach ( gf_pages_admin_get_settings_fields() as $section => $fields ) {
		foreach ( $fields as $option_name => $args ) {

			// Get field name without prefix
			$field_name = isset( $args['name'] ) ? $args['name'] : str_replace( '_gf_pages_', '', $option_name );

			// Collect initial values
			$initial_values[ $field_name ] = get_option( $option_name, $args['args']['default_value'] );
		}
	}

	return apply_filters( 'gf_pages_admin_get_settings_fields_initial_values_for_gf_2_5', $initial_values );
}

/**
 * Update settings fields for the new Settings layout since GF 2.5
 *
 * @since 1.0.3
 *
 * @uses do_action() Calls 'gf_pages_admin_update_settings_fields_for_gf_2_5'
 */
function gf_pages_admin_update_settings_fields_for_gf_2_5( $values ) {

	// Walk legacy settings fields for saving
	foreach ( gf_pages_admin_get_settings_fields() as $section => $fields ) {
		foreach ( $fields as $option_name => $args ) {

			// Settings are saved by unprefixed name, so get name first
			$field_name = isset( $args['name'] ) ? $args['name'] : str_replace( '_gf_pages_', '', $option_name );

			/* Save setting. Sanitization callbacks are registered through `GravityForms_Pages_Admin::register_settings()` */
			update_option( $option_name, rgar( $values, $field_name ) );
		}
	}

	do_action( 'gf_pages_admin_update_settings_fields_for_gf_2_5', $values );
}

/** Legacy Settings ***********************************************************/

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
				'args'              => array(
					'description'   => esc_html__( 'Enter the permalink part for the forms archive and single forms.', 'gravityforms-pages' ),
					'type'          => 'text',
					'default_value' => 'forms'
				)
			),
		),

		// Query
		'gf_pages_settings_query' => array(

			// Form archive title
			'_gf_pages_form_archive_title' => array(
				'title'             => version_compare( GFCommon::$version, '2.5', '<=' )
					? esc_html__( 'Form Archive Title', 'gravityforms-pages' )
					: esc_html__( 'Form Archive Title and Description', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_archive_title',
				'sanitize_callback' => 'strip_tags',
				'args'              => array(
					'description'   => sprintf( esc_html__( 'The forms archive page title. When empty, the default title %s will be used.', 'gravityforms-pages' ), '<code>' . esc_html_x( 'Forms', 'Default form archive title', 'gravityforms-pages' ) . '</code>' ),
					'type'          => 'text',
					'default_value' => esc_html_x( 'Forms', 'Default form archive title', 'gravityforms-pages' ),
					'sub_fields'    => array(
						array(
							'name'          => 'form_archive_description',
							'description'   => esc_html__( 'The introduction text to the paged forms archive.', 'gravityforms-pages' ),
							'type'          => 'textarea',
							'default_value' => ''
						)
					)
				)
			),

			// Form archive description
			'_gf_pages_form_archive_description' => array(
				'title'             => esc_html__( 'Form Archive Description', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_form_archive_description',
				'sanitize_callback' => '',
				'args'              => array(
					'default_value' => ''
				)
			),

			// Forms per page
			'_gf_pages_forms_per_page' => array(
				'title'             => esc_html__( 'Forms Per Page', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_forms_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array(
					'description'   => esc_html__( 'The number of items to show per page on the paged forms archive.', 'gravityforms-pages' ),
					'type'          => 'text',
					'input_type'    => 'number',
					'default_value' => 10
				)
			),

			// Hide form archive
			'_gf_pages_hide_form_archive' => array(
				'title'             => esc_html__( 'Hide Form Archive', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_hide_form_archive',
				'sanitize_callback' => 'intval',
				'args'              => array(
					'description'   => sprintf( esc_html__( "Disable the paged forms archive listing. Visitors of %s will be routed to a 404 'Not Found' page.", 'gravityforms-pages' ), '<code>' . gf_pages_get_form_archive_url() . '</code>' ),
					'type'          => 'toggle',
					'default_value' => 0
				)
			),

			// Hide closed forms
			'_gf_pages_hide_closed_forms' => array(
				'title'             => esc_html__( 'Hide Closed Forms', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_hide_closed_forms',
				'sanitize_callback' => 'intval',
				'args'              => array(
					'description'   => esc_html__( 'Hide paged forms that are closed to new entries.', 'gravityforms-pages' ),
					'type'          => 'toggle',
					'default_value' => 0
				)
			),
		),

		// Additional
		'gf_pages_settings_additional' => array(

			// Default availability
			'_gf_pages_default_availability' => array(
				'title'             => esc_html__( 'Default Availability', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_default_availability',
				'sanitize_callback' => 'intval',
				'args'              => array(
					'description'   => esc_html__( 'Enable this when forms should be available as a page by default. Availability can also be set on a per-form basis. When made available, form settings like inactive status, required user login and time schedule restrictions are respected before the form is displayed.', 'gravityforms-pages' ),
					'type'          => 'toggle',
					'default_value' => 1
				)
			),

			// Force ajax
			'_gf_pages_force_ajax' => array(
				'title'             => esc_html__( 'Force Ajax', 'gravityforms-pages' ),
				'callback'          => 'gf_pages_admin_setting_callback_force_ajax',
				'sanitize_callback' => 'intval',
				'args'              => array(
					'description'   => esc_html__( 'Always use interactive ajax functionality in paged forms.', 'gravityforms-pages' ),
					'type'          => 'toggle',
					'default_value' => 0
				)
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
function gf_pages_admin_setting_callback_form_archive_title( $args = array() ) { ?>

	<input id="_gf_pages_form_archive_title" name="_gf_pages_form_archive_title" type="text" class="regular-text" value="<?php echo get_option( '_gf_pages_form_archive_title', $args['default_value'] ); ?>" />
	<br>
	<label for="_gf_pages_form_archive_title"><span class="description"><?php echo $args['description']; ?></span></label>

	<?php
}

/**
 * Output the form archive description setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_form_archive_description( $args = array() ) { ?>

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
function gf_pages_admin_setting_callback_hide_form_archive( $args = array() ) { ?>

	<input id="_gf_pages_hide_form_archive" name="_gf_pages_hide_form_archive" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_form_archive' ) ); ?> />
	<label for="_gf_pages_hide_form_archive"><span class="description"><?php echo $args['description']; ?></span></label>

	<?php
}

/**
 * Output the forms per page setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_hide_closed_forms( $args = array() ) { ?>

	<input id="_gf_pages_hide_closed_forms" name="_gf_pages_hide_closed_forms" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_hide_closed_forms' ) ); ?> />
	<label for="_gf_pages_hide_closed_forms"><span class="description"><?php echo $args['description']; ?></span></label>

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
 * Output the default availability setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_default_availability( $args = array() ) { ?>

	<input id="_gf_pages_default_availability" name="_gf_pages_default_availability" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_default_availability', true ) ); ?> />
	<label for="_gf_pages_default_availability"><span class="description"><?php echo $args['description']; ?></span></label>

	<?php
}

/**
 * Output the force ajax setting field
 *
 * @since 1.0.0
 */
function gf_pages_admin_setting_callback_force_ajax( $args = array() ) { ?>

	<input id="_gf_pages_force_ajax" name="_gf_pages_force_ajax" type="checkbox" value="1" <?php checked( get_option( '_gf_pages_force_ajax' ) ); ?> />
	<label for="_gf_pages_force_ajax"><span class="description"><?php echo $args['description']; ?></span></label>

	<?php
}

/** Forms *********************************************************************/

/**
 * Get the form settings fields
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'gf_pages_admin_get_form_settings_fields'
 * @return array Form settings fields
 */
function gf_pages_admin_get_form_settings_fields() {
	$legacy_settings = version_compare( GFCommon::$version, '2.5', '<=' );
	$field_title     = esc_html__( 'Page Availability', 'gravityforms-pages' );
	$tooltip_text    = '<strong>' . $field_title . '</strong>' . esc_html__( 'Customize whether this form is available as a page within your site. When made available, other form settings like inactive status, required user login or time schedule restrictions are respected before the form is displayed.', 'gravityforms-pages' );

	return (array) apply_filters( 'gf_pages_admin_get_form_settings_fields', array(

		// Page Availability
		'gf_pages_page_availability' => array(
			'section'           => $legacy_settings ? 'Restrictions' : 'form_basics',
			'type'              => 'toggle',
			'title'             => $field_title,
			'tooltip'           => $legacy_settings ? $tooltip_text : gform_tooltip( $tooltip_text, '', true ),
			'callback'          => 'gf_pages_admin_form_setting_callback_page_availability',
			'sanitize_callback' => 'intval',
		)
	) );
}

/**
 * Display the form availabiltity form settings field
 *
 * @since 1.0.0
 *
 * @param array $form Form data
 */
function gf_pages_admin_form_setting_callback_page_availability( $form ) {

	// Get form setting, default to global setting
	$selected = isset( $form['gf_pages_page_availability'] )
		? (bool) $form['gf_pages_page_availability']
		: gf_pages_default_availability();

	ob_start(); ?>

	<select id="gf_pages_page_availability" name="gf_pages_page_availability">
		<option value="1" <?php selected( $selected, true ); ?>><?php esc_html_e( 'available', 'gravityforms-pages' ); ?></option>
		<option value="0" <?php selected( $selected, false ); ?>><?php esc_html_e( 'unavailable', 'gravityforms-pages' ); ?></option>
	</select>

	<?php $availability = ob_get_clean(); ?>

	<label for="gf_pages_page_availability"><?php printf( esc_html__( 'Make this form %s as a page.', 'gravityforms-pages' ), $availability ); ?></label>

	<?php
}
