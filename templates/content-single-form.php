<?php

/**
 * Single Form Content Part
 *
 * @package Gravity Forms Pages
 * @subpackage Theme
 */

?>

<article id="form-<?php gf_pages_form_id(); ?>" <?php gf_pages_form_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php gf_pages_form_title(); ?></h1>
		
		<div class="entry-meta">
			<?php gf_pages_form_posted_on(); ?>

			<?php if ( gf_pages_show_view_count() ) : ?>
			<span class="sep"> | </span>
			<span class="form-views"><?php printf( _n('1 View', '%d Views', gf_pages_get_form_view_count(), 'gravityforms-pages'), gf_pages_get_form_view_count() ); ?></span>
			<?php endif; ?>

			<?php if ( gf_pages_show_entry_count() ) : ?>
			<span class="sep"> | </span>
			<span class="form-entries"><?php gf_pages_form_display_entry_count(); ?></span>
			<?php endif; ?>
			
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php gf_pages_form_content(); ?>
	</div>

	<footer class="entry-meta">
		<?php printf( __( 'Bookmark the <a href="%1$s" title="Permalink to %2$s" rel="bookmark">permalink</a>.', 'gravityforms-pages' ), gf_pages_get_form_url(), gf_pages_get_form_title() ); ?>
		<?php gf_pages_edit_form_link( __( 'Edit', 'gravityforms-pages' ), '<span class="edit-link">', '</span>' ); ?>
	</footer>
</article>
