<?php

/**
 * Gravity Forms Pages Actions
 *
 * @package Gravity Forms Pages
 * @subpackage Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Sub-actions ***************************************************************/

add_action( 'init',                      'gf_pages_init',              10 );
add_action( 'admin_init',                'gf_pages_admin_init',        10 );
add_action( 'after_setup_theme',         'gf_pages_after_setup_theme', 10 );
add_filter( 'template_include',          'gf_pages_template_include',  10 );

/** Utility *******************************************************************/

add_action( 'gf_pages_activation',       'gf_pages_delete_rewrite_rules', 10 );
add_action( 'gf_pages_deactivation',     'gf_pages_delete_rewrite_rules', 10 );

/** Query *********************************************************************/

add_action( 'parse_query',               'gf_pages_parse_query',        2    ); // Early for overrides
add_filter( 'posts_request',             'gf_pages_filter_wp_query',   10, 2 );
add_filter( 'posts_pre_query',           'gf_pages_bypass_wp_query',   10, 2 ); // Since WP 4.6

/** Post **********************************************************************/

add_filter( 'gf_pages_get_form_content',     'do_shortcode',       10 );

add_filter( 'gf_pages_get_form_description', 'wptexturize',        6  );
add_filter( 'gf_pages_get_form_description', 'convert_chars',      8  );
add_filter( 'gf_pages_get_form_description', 'capital_P_dangit',   10 );
add_filter( 'gf_pages_get_form_description', 'convert_smilies',    20 );
add_filter( 'gf_pages_get_form_description', 'force_balance_tags', 30 );
add_filter( 'gf_pages_get_form_description', 'wpautop',            40 );

add_filter( 'gf_pages_get_form_excerpt',     'wptexturize',        6  );
add_filter( 'gf_pages_get_form_excerpt',     'convert_chars',      8  );
add_filter( 'gf_pages_get_form_excerpt',     'capital_P_dangit',   10 );
add_filter( 'gf_pages_get_form_excerpt',     'convert_smilies',    20 );
add_filter( 'gf_pages_get_form_excerpt',     'force_balance_tags', 30 );
add_filter( 'gf_pages_get_form_excerpt',     'wpautop',            40 );

/** Archive *******************************************************************/

add_filter( 'gf_pages_get_form_archive_title',       'wptexturize',       10 );
add_filter( 'gf_pages_get_form_archive_title',       'strip_tags',        10 );
add_filter( 'gf_pages_get_form_archive_title',       'convert_chars',     10 );
add_filter( 'gf_pages_get_form_archive_title',       'trim',              10 );

add_filter( 'gf_pages_get_form_archive_description', 'wptexturize',       10 );
add_filter( 'gf_pages_get_form_archive_description', 'convert_smilies',   10 );
add_filter( 'gf_pages_get_form_archive_description', 'convert_chars',     10 );
add_filter( 'gf_pages_get_form_archive_description', 'wpautop',           10 );
add_filter( 'gf_pages_get_form_archive_description', 'shortcode_unautop', 10 );

/** Template ******************************************************************/

add_action( 'gf_pages_after_setup_theme',  'gf_pages_load_theme_functions'        );
add_filter( 'document_title_parts',        'gf_pages_document_title_parts'        ); // Since WP 4.4
add_filter( 'body_class',                  'gf_pages_body_class',           10, 2 );
add_filter( 'get_the_archive_title',       'gf_pages_get_the_archive_title'       );
add_filter( 'get_the_archive_description', 'gf_pages_get_the_archive_description' );
add_filter( 'post_class',                  'gf_pages_filter_item_class'           );
add_filter( 'gf_pages_form_class',         'gf_pages_filter_item_class'           );

// Theme Compat
add_filter( 'gf_pages_template_include', 'gf_pages_template_include_theme_supports', 10 );
add_filter( 'gf_pages_template_include', 'gf_pages_template_include_theme_compat',   12 );

/** Menus *********************************************************************/

add_filter( 'customize_nav_menu_available_item_types', 'gf_pages_customize_nav_menu_available_item_types', 10    );
add_filter( 'customize_nav_menu_available_items',      'gf_pages_customize_nav_menu_available_items',      10, 4 );
add_filter( 'customize_nav_menu_searched_items',       'gf_pages_customize_nav_menu_searched_items',       10, 2 );
add_filter( 'wp_setup_nav_menu_item',                  'gf_pages_setup_nav_menu_item'                            );
add_filter( 'wp_nav_menu_objects',                     'gf_pages_nav_menu_objects',                        10, 2 );

/** Misc **********************************************************************/

add_action( 'admin_bar_menu',            'gf_pages_admin_bar_menu', 90 );

/** Admin *********************************************************************/

if ( is_admin() ) {
	add_action( 'gf_pages_init',         'gf_pages_admin',          10 );
	add_action( 'gf_pages_admin_init',   'gf_pages_setup_updater', 999 );
}

/** Extend ********************************************************************/

add_action( 'gf_pages_init', 'gfp_wpseo' );
