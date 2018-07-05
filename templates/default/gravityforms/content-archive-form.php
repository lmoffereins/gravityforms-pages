<?php

/**
 * Single Form Template Part
 *
 * @package Gravity Forms Pages
 * @subpackage Theme
 */

?>

<div id="gravityforms-forms">

	<?php if ( gf_pages_has_forms() ) : ?>

		<?php while ( gf_pages_has_forms() ) : gf_pages_the_form(); ?>

		<div id="form-<?php gf_pages_the_form_id(); ?>" <?php gf_pages_form_class(); ?>>

			<?php gf_pages_the_form_link( array(
				'link_before' => '<h2 class="form-title">',
				'link_after'  => '</h2>'
			) ); ?>

			<?php gf_pages_the_form_description(); ?>

			<?php gf_pages_the_form_link( array(
				'link_before' => '<p>',
				'link_after'  => '</p>',
				'link_text'   => esc_html__( 'Complete the form &rarr;', 'gravityforms-pages' )
			) ); ?>

		</div>

		<?php endwhile; ?>

	<?php else : ?>

		<?php gf_pages_get_template_part( 'feedback', 'no-forms' ); ?>

	<?php endif; ?>

</div>
