<?php

/**
 * Gravityforms Pages Sub Actions
 *
 * @package Gravityforms Pages
 * @subpackage Core
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Run dedicated activation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'gf_pages_activation'
 */
function gf_pages_activation() {
	do_action( 'gf_pages_activation' );
}

/**
 * Run dedicated deactivation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'gf_pages_deactivation'
 */
function gf_pages_deactivation() {
	do_action( 'gf_pages_deactivation' );
}

/**
 * Run dedicated init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'gf_pages_init'
 */
function gf_pages_init() {
	do_action( 'gf_pages_init' );
}

/**
 * Run dedicated admin init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'gf_pages_admin_init'
 */
function gf_pages_admin_init() {
	do_action( 'gf_pages_admin_init' );
}
