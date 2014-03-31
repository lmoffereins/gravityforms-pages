<?php

/**
 * Archive Form Content Part
 *
 * @package Gravity Forms Pages
 * @subpackage Theme
 */

?>

<article id="form-<?php gf_pages_form_id(); ?>" <?php gf_pages_form_class(); ?>>
	<header class="entry-header">
		<h2 class="entry-title"><a href="<?php gf_pages_form_url(); ?>" title="<?php esc_attr_e( sprintf( __( 'Permalink to %s', 'gravityforms-pages' ), gf_pages_get_form_title() ) ); ?>" rel="bookmark"><?php gf_pages_form_title(); ?></a></h2>
		
		<div class="entry-meta">
			<?php gf_pages_form_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php gf_pages_form_excerpt(); ?>
	</div>

	<footer class="entry-meta">
		<span class="form-user-entry">
			<?php if ( ! gf_pages_has_form_user_entry() ) : ?>

				<?php if ( gf_pages_is_form_open() ) : ?>
				<a href="<?php gf_pages_form_url(); ?>"><?php _e('Fill out this form', 'gravityforms-pages'); ?></a>
				<?php else : ?>
				<?php _e('This form is closed', 'gravityforms-pages'); ?>
				<?php endif; ?>

			<?php else : ?>

				<?php _e('You responded', 'gravityforms-pages'); ?>

			<?php endif; ?>
		</span>

		<?php if ( gf_pages_show_view_count() ) : ?>
		<span class="sep"> | </span>
		<span class="form-views"><?php printf( _n('1 View', '%d Views', gf_pages_get_form_view_count(), 'gravityforms-pages'), gf_pages_get_form_view_count() ); ?></span>
		<?php endif; ?>

		<?php if ( gf_pages_show_entry_count() ) : ?>
		<span class="sep"> | </span>
		<span class="form-entries"><?php gf_pages_form_display_entry_count(); ?></span>
		<?php endif; ?>

		<?php gf_pages_edit_form_link( __( 'Edit', 'gravityforms-pages' ), '<span class="sep"> | </span> <span class="edit-link">', '</span>' ); ?>
	</footer>
</article>
