<?php

/**
 * Gravity Forms Pages Admin Functions
 *
 * @package Gravity Forms Pages
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Nav Menus *****************************************************************/

/**
 * Display the plugin's nav menu metabox
 *
 * @since 1.0.0
 */
function gf_pages_admin_nav_menu_metabox() {
	global $_nav_menu_placeholder, $nav_menu_selected_id;

	// Paginate browsing for large numbers of form objects.
	$per_page = 50;
	$pagenum  = isset( $_REQUEST['forms-tab'] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;

	$args = array(
		'number'           => $per_page,
		'paged'            => $pagenum,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'suppress_filters' => true,
	);

	$forms = gf_pages_get_forms( $args );
	if ( ! $forms ) {
		echo '<p>' . __( 'No items.' ) . '</p>';
		return;
	}

	$num_pages = ceil( gf_pages_query_forms_found_rows( $args ) / $per_page );

	$page_links = paginate_links(
		array(
			'base'               => add_query_arg(
				array(
					'forms-tab'   => 'all',
					'paged'       => '%#%',
					'item-type'   => 'gravityforms-pages',
				)
			),
			'format'             => '',
			'prev_text'          => '<span aria-label="' . esc_attr__( 'Previous page' ) . '">' . __( '&laquo;' ) . '</span>',
			'next_text'          => '<span aria-label="' . esc_attr__( 'Next page' ) . '">' . __( '&raquo;' ) . '</span>',
			'before_page_number' => '<span class="screen-reader-text">' . __( 'Page' ) . '</span> ',
			'total'              => $num_pages,
			'current'            => $pagenum,
		)
	);

	$walker = new GF_Pages_Walker_Nav_Menu_Checklist();
	$args = array( 'walker' => $walker );

	$current_tab = 'most-recent';
	if ( isset( $_REQUEST['forms-tab'] ) && in_array( $_REQUEST['forms-tab'], array( 'all', 'search' ) ) ) {
		$current_tab = $_REQUEST['forms-tab'];
	}

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);

	$most_recent_url = $view_all_url = $search_url = '';
	if ( $nav_menu_selected_id ) {
		$most_recent_url = esc_url( add_query_arg( 'forms-tab', 'most-recent', remove_query_arg( $removed_args ) ) );
		$view_all_url    = esc_url( add_query_arg( 'forms-tab', 'all', remove_query_arg( $removed_args ) ) );
		$search_url      = esc_url( add_query_arg( 'forms-tab', 'search', remove_query_arg( $removed_args ) ) );
	}
	?>

	<div id="gravityforms-pages" class="posttypediv">
		<ul id="forms-tabs" class="posttype-tabs add-menu-item-tabs">
			<li <?php echo ( 'most-recent' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-forms-most-recent" href="<?php echo $most_recent_url; ?>#tabs-panel-forms-most-recent">
					<?php _e( 'Most Recent' ); ?>
				</a>
			</li>
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="forms-all" href="<?php echo $view_all_url; ?>#forms-all">
					<?php _e( 'View All' ); ?>
				</a>
			</li>
			<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-forms-search" href="<?php echo $search_url; ?>#tabs-panel-forms-search">
					<?php _e( 'Search' ); ?>
				</a>
			</li>
		</ul><!-- .posttype-tabs -->

		<div id="tabs-panel-forms-most-recent" class="tabs-panel 
												<?php
												echo ( 'most-recent' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>
		">
			<ul id="formschecklist-most-recent" class="categorychecklist form-no-clear">
				<?php
				$most_recent = gf_pages_get_forms(
					array(
						'number'           => 15,
						'suppress_filters' => true
					)
				);
				$most_recent = array_map( 'gf_pages_setup_form_nav_menu_item', $most_recent );
				echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $most_recent ), 0, (object) $args );
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div class="tabs-panel 
		<?php
			echo ( 'search' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>
		" id="tabs-panel-forms-search">
			<?php
			if ( isset( $_REQUEST['quick-search-gravityforms-pages'] ) ) {
				$searched       = esc_attr( $_REQUEST['quick-search-gravityforms-pages'] );
				$search_results = gf_pages_get_forms(
					array(
						's'     => $searched,
						'order' => 'DESC',
					)
				);
			} else {
				$searched       = '';
				$search_results = array();
			}
			?>
			<p class="quick-search-wrap">
				<label for="quick-search-gravityforms-pages" class="screen-reader-text"><?php _e( 'Search' ); ?></label>
				<input type="search" class="quick-search" value="<?php echo $searched; ?>" name="quick-search-gravityforms-pages" id="quick-search-gravityforms-pages" />
				<span class="spinner"></span>
				<?php submit_button( __( 'Search' ), 'small quick-search-submit hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-gravityforms-pages' ) ); ?>
			</p>

			<ul id="forms-search-checklist" data-wp-lists="list:forms" class="categorychecklist form-no-clear">
			<?php if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) ) : ?>
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $search_results ), 0, (object) $args ); ?>
			<?php elseif ( is_wp_error( $search_results ) ) : ?>
				<li><?php echo $search_results->get_error_message(); ?></li>
			<?php elseif ( ! empty( $searched ) ) : ?>
				<li><?php _e( 'No results found.' ); ?></li>
			<?php endif; ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div id="forms-all" class="tabs-panel tabs-panel-view-all 
							<?php
							echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>
		">
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
			<ul id="formschecklist" data-wp-lists="list:forms" class="categorychecklist form-no-clear">
				<?php

				/**
				 * Put the other custom nav menu items at the top of the list.
				 */
				$forms = array_map( 'gf_pages_setup_form_nav_menu_item', $forms );
				foreach ( array_reverse( gf_pages_get_nav_menu_items() ) as $item_id => $item ) {
					$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) - 1 : -1;
					array_unshift( $forms, (object) wp_parse_args( $item, array(
						'db_id'            => 0,
						'object'           => $item_id,
						'object_id'        => $_nav_menu_placeholder,
						'menu_item_parent' => 0,
						// 'type'             => 'gravityforms-pages',
						'target'           => '',
						'attr_title'       => '',
						'classes'          => array(),
						'xfn'              => '',
					) ) );
				}

				$checkbox_items = walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $forms ), 0, (object) $args );

				if ( 'all' == $current_tab && ! empty( $_REQUEST['selectall'] ) ) {
					$checkbox_items = preg_replace( '/(type=(.)checkbox(\2))/', '$1 checked=$2checked$2', $checkbox_items );
				}

				echo $checkbox_items;
				?>
			</ul>
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.tabs-panel -->

		<p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a href="
				<?php
					echo esc_url(
						add_query_arg(
							array(
								'forms-tab' => 'all',
								'selectall' => 1,
							),
							remove_query_arg( $removed_args )
						)
					);
				?>
				#gravityforms-pages" class="select-all aria-button-if-js"><?php _e( 'Select All' ); ?></a>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-form-menu-item" id="submit-gravityforms-pages" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.posttypediv -->

	<?php
}

/**
 * Ajax handler for forms menu quick searching
 *
 * Runs before WP's default handler, which runs at priority 1.
 *
 * @see _wp_ajax_menu_quick_search()
 *
 * @since 1.0.0
 */
function gf_pages_wp_ajax_menu_quick_search() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( -1 );
	}

	require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

	$args            = array();
	$type            = isset( $_POST['type'] ) ? $_POST['type'] : '';
	$object_type     = isset( $_POST['object_type'] ) ? $_POST['object_type'] : '';
	$query           = isset( $_POST['q'] ) ? $_POST['q'] : '';
	$response_format = isset( $_POST['response-format'] ) && in_array( $_POST['response-format'], array( 'json', 'markup' ) ) ? $_POST['response-format'] : 'json';

	// Bail when this is not our plugin's request
	if ( 'quick-search-gravityforms-pages' !== $type )
		return;

	if ( 'markup' == $response_format ) {
		$args['walker'] = new GF_Pages_Walker_Nav_Menu_Checklist;
	}
	
	$forms = gf_pages_get_forms( array(
		's'                => $query,
		'suppress_filters' => true
	) );

	if ( $forms && ! is_wp_error( $forms ) ) {
		foreach ( $forms as $form ) {
			if ( 'markup' == $response_format ) {
				$form = gf_pages_setup_form_nav_menu_item( $form );
				echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', array( $form ) ), 0, (object) $args );
			} elseif ( 'json' == $response_format ) {
				echo wp_json_encode(
					array(
						'ID'         => $form->id,
						'post_title' => gf_pages_get_form_title( $form ),
						'post_type'  => '',
					)
				);
				echo "\n";
			}
		}
	}

	wp_die();
}
