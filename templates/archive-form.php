<?php

/**
 * Form Archive
 *
 * @package Gravity Forms Pages
 * @subpackage Theme
 *
 * @see _s archive.php
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

		<?php if ( gf_pages_has_forms() ) : ?>
			<header class="page-header">
				<h1 class="page-title"><?php gf_pages_form_archive_title(); ?></h1>
			</header><!-- .archive-header -->

			<?php /* The loop */ ?>
			<?php while ( gf_pages_forms() ) : gf_pages_the_form(); ?>

				<?php
					/**
					 * Include the form-specific template for the content.
					 * Since forms aren't set up like WordPress's posts, the present theme content 
					 * templates will not work and Gravity Forms Pages will default to it's own
					 * content template when content-archive-form.php is not found.
					 */
					gf_pages_get_template_part( 'content', 'archive-form' ); 
				?>

			<?php endwhile; ?>

			<?php gf_pages_content_nav( 'nav-below' ); ?>

		<?php else : ?>

			<?php get_template_part( 'no-results', 'archive' ); ?>

		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>