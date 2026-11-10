<?php
/**
 * WooCommerce product classification screen, preview and journal.
 *
 * @package Zion_Core
 */

/**
 * Read a scalar filter from an admin list.
 *
 * @param string $key Filter key.
 * @return string
 */
function zion_core_shop_admin_filter( string $key ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters on the admin list.
	return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : '';
}

/**
 * Classification filters shared by both product screens.
 *
 * @return array
 */
function zion_core_shop_filter_labels(): array {
	return array(
		''             => __( 'Toate clasificările', 'zion-core' ),
		'unclassified' => __( 'Neclasificate', 'zion-core' ),
		'3d'           => __( 'În Magazin 3D', 'zion-core' ),
		'software'     => __( 'În Magazin Software', 'zion-core' ),
		'both'         => __( 'În ambele vitrine', 'zion-core' ),
	);
}

/**
 * Build a taxonomy condition without altering WooCommerce category filters.
 *
 * @param string $state Selected classification.
 * @return array
 */
function zion_core_shop_filter_clause( string $state ): array {
	return match ( $state ) {
		'unclassified' => array(
			'taxonomy'         => 'zion_shop',
			'field'            => 'slug',
			'terms'            => array_keys( zion_core_shops() ),
			'operator'         => 'NOT IN',
			'include_children' => false,
		),
		'both' => array(
			'taxonomy'         => 'zion_shop',
			'field'            => 'slug',
			'terms'            => array_keys( zion_core_shops() ),
			'operator'         => 'AND',
			'include_children' => false,
		),
		'3d', 'software' => array(
			'taxonomy'         => 'zion_shop',
			'field'            => 'slug',
			'terms'            => array( $state ),
			'include_children' => false,
		),
		default => array(),
	};
}

/**
 * Render the classification selector.
 *
 * @param string $state Selected state.
 */
function zion_core_shop_admin_selector( string $state ): void {
	?>
	<label class="screen-reader-text" for="zion-shop-state"><?php esc_html_e( 'Clasificare vitrină', 'zion-core' ); ?></label>
	<select id="zion-shop-state" name="zion_shop_state">
		<?php foreach ( zion_core_shop_filter_labels() as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $state, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Add the shop filter to WooCommerce's native Products list.
 *
 * @param string $post_type Current post type.
 * @param string $which Toolbar position.
 */
function zion_core_shop_products_filter( string $post_type, string $which ): void {
	if ( 'product' === $post_type && 'top' === $which && taxonomy_exists( 'zion_shop' ) ) {
		zion_core_shop_admin_selector( zion_core_shop_admin_filter( 'zion_shop_state' ) );
	}
}
add_action( 'restrict_manage_posts', 'zion_core_shop_products_filter', 10, 2 );

/**
 * Restrict only the native Products list query.
 *
 * @param WP_Query $query WordPress query.
 */
function zion_core_shop_products_query( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() || 'edit.php' !== ( $GLOBALS['pagenow'] ?? '' ) || 'product' !== $query->get( 'post_type' ) ) {
		return;
	}
	$clause = zion_core_shop_filter_clause( zion_core_shop_admin_filter( 'zion_shop_state' ) );
	if ( $clause ) {
		$existing = $query->get( 'tax_query' );
		$query->set(
			'tax_query',
			$existing ? array(
				'relation' => 'AND',
				$existing,
				$clause,
			) : array( $clause )
		);
	}
}
add_action( 'pre_get_posts', 'zion_core_shop_products_query', 30 );

/**
 * Add a visible classification status to every product.
 *
 * @param array $columns Current list columns.
 * @return array
 */
function zion_core_shop_product_columns( array $columns ): array {
	$columns['zion_shop_status'] = __( 'Vitrine Zion', 'zion-core' );
	return $columns;
}
add_filter( 'manage_product_posts_columns', 'zion_core_shop_product_columns' );

/**
 * Print classification state in the native product list.
 *
 * @param string $column Column key.
 * @param int    $id Product ID.
 */
function zion_core_shop_product_column( string $column, int $id ): void {
	if ( 'zion_shop_status' === $column ) {
		echo esc_html( zion_core_shop_status_label( zion_core_shop_product_slugs( $id ) ) );
	}
}
add_action( 'manage_product_posts_custom_column', 'zion_core_shop_product_column', 10, 2 );

/**
 * Get canonical shop slugs for display using the terms cache.
 *
 * @param int $id Product ID.
 * @return array
 */
function zion_core_shop_product_slugs( int $id ): array {
	$terms = get_the_terms( $id, 'zion_shop' );
	return is_array( $terms ) ? array_intersect( array_keys( zion_core_shops() ), wp_list_pluck( $terms, 'slug' ) ) : array();
}

/**
 * Format shop membership for display.
 *
 * @param array $slugs Shop slugs.
 * @return string
 */
function zion_core_shop_status_label( array $slugs ): string {
	$labels = array_column( array_intersect_key( zion_core_shops(), array_fill_keys( $slugs, true ) ), 'title' );
	return $labels ? implode( ' + ', $labels ) : __( 'Neclasificat', 'zion-core' );
}

/**
 * Add Products → Vitrine Zion while WooCommerce is active.
 */
function zion_core_shop_admin_menu(): void {
	if ( taxonomy_exists( 'zion_shop' ) ) {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Clasificare vitrine Zion', 'zion-core' ),
			__( 'Vitrine Zion', 'zion-core' ),
			'manage_woocommerce',
			'zion-shops',
			'zion_core_shop_admin_screen'
		);
	}
}
add_action( 'admin_menu', 'zion_core_shop_admin_menu' );

/**
 * Process a verified request on the classification screen.
 *
 * @return array|WP_Error
 */
function zion_core_shop_admin_request(): array|WP_Error {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- The presence check only detects a submission. Processing follows nonce verification.
	if ( ! isset( $_POST['zion_shop_action'] ) ) {
		return array();
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
	check_admin_referer( 'zion_core_shop_admin' );

	$action = is_string( $_POST['zion_shop_action'] ) ? sanitize_key( wp_unslash( $_POST['zion_shop_action'] ) ) : '';
	if ( 'pages' === $action ) {
		if ( ! current_user_can( 'publish_pages' ) ) {
			return new WP_Error( 'zion_shop_pages_forbidden', __( 'Nu ai permisiunea să publici pagini.', 'zion-core' ) );
		}
		return array( 'pages' => zion_core_create_shop_pages() );
	}
	if ( 'apply' === $action ) {
		$token = isset( $_POST['zion_shop_plan'] ) && is_string( $_POST['zion_shop_plan'] ) ? sanitize_key( wp_unslash( $_POST['zion_shop_plan'] ) ) : '';
		$key   = 'zion_shop_plan_' . get_current_user_id() . '_' . $token;
		$plan  = wp_is_uuid( $token, 4 ) ? get_transient( $key ) : false;
		if ( ! is_array( $plan ) || ! delete_transient( $key ) ) {
			return new WP_Error( 'zion_shop_missing_plan', __( 'Previzualizarea a expirat sau a fost folosită. Generează una nouă.', 'zion-core' ) );
		}
		$rows = zion_core_apply_classification_plan( $plan );
		return is_wp_error( $rows ) ? $rows : array(
			'mode' => 'applied',
			'rows' => $rows,
		);
	}
	if ( ! in_array( $action, array( 'preview', 'dry_run' ), true ) ) {
		return new WP_Error( 'zion_shop_unknown_action', __( 'Operație necunoscută.', 'zion-core' ) );
	}

	$operation = isset( $_POST['zion_shop_operation'] ) && is_string( $_POST['zion_shop_operation'] ) ? sanitize_key( wp_unslash( $_POST['zion_shop_operation'] ) ) : '';
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The lists are converted to exact ID/slug values below, then validated against the product permissions and canonical terms.
	$ids   = isset( $_POST['product_ids'] ) && is_array( $_POST['product_ids'] ) ? wp_unslash( $_POST['product_ids'] ) : array();
	$shops = isset( $_POST['shops'] ) && is_array( $_POST['shops'] ) ? wp_unslash( $_POST['shops'] ) : array();
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	foreach ( $ids as &$id ) {
		$id = is_string( $id ) && ctype_digit( $id ) && (string) (int) $id === $id ? (int) $id : 0;
	}
	unset( $id );
	foreach ( $shops as $slug ) {
		if ( ! is_string( $slug ) || ! isset( zion_core_shops()[ $slug ] ) ) {
			return new WP_Error( 'zion_shop_invalid_shop', __( 'Vitrină necunoscută.', 'zion-core' ) );
		}
	}
	$plan = zion_core_classification_plan( $ids, $operation, $shops );
	if ( is_wp_error( $plan ) ) {
		return $plan;
	}

	$response = array(
		'mode' => $action,
		'rows' => $plan['rows'],
	);
	if ( 'preview' === $action && in_array( 'pending', array_column( $plan['rows'], 'state' ), true ) ) {
		$token = wp_generate_uuid4();
		if ( ! set_transient( 'zion_shop_plan_' . get_current_user_id() . '_' . $token, $plan, 15 * MINUTE_IN_SECONDS ) ) {
			return new WP_Error( 'zion_shop_preview_failed', __( 'Previzualizarea nu a putut fi salvată.', 'zion-core' ) );
		}
		$response['token'] = $token;
	}
	return $response;
}

/**
 * Build the URL of the classification screen.
 *
 * @param array $args Optional filters or pagination.
 * @return string
 */
function zion_core_shop_admin_url( array $args = array() ): string {
	return add_query_arg(
		array_merge(
			array(
				'post_type' => 'product',
				'page'      => 'zion-shops',
			),
			$args
		),
		admin_url( 'edit.php' )
	);
}

/**
 * Build the paginated bulk inventory.
 *
 * @param string $state Classification filter.
 * @param int    $category Category ID.
 * @param int    $page Page number.
 * @return WP_Query
 */
function zion_core_shop_admin_inventory( string $state, int $category, int $page ): WP_Query {
	$clauses = array_filter( array( zion_core_shop_filter_clause( $state ) ) );
	if ( $category ) {
		$clauses[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => array( $category ),
		);
	}
	return new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
			'perm'           => 'editable',
			'posts_per_page' => 20,
			'paged'          => $page,
			'orderby'        => array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Filter by WooCommerce category and explicit taxonomy classification.
			'tax_query'      => array_values( $clauses ),
		)
	);
}

/**
 * Render WordPress-native pagination.
 *
 * @param array  $args Current query args.
 * @param string $key Page query key.
 * @param int    $current Current page number.
 * @param int    $total Total pages.
 */
function zion_core_shop_admin_pagination( array $args, string $key, int $current, int $total ): void {
	if ( $total < 2 ) {
		return;
	}
	$args[ $key ] = 999999;
	$links        = paginate_links(
		array(
			'base'    => str_replace( '999999', '%#%', zion_core_shop_admin_url( $args ) ),
			'current' => $current,
			'total'   => $total,
		)
	);
	echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $links ) . '</div></div>';
}

/**
 * Render product and shop setup and the bulk workflow.
 */
function zion_core_shop_admin_screen(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! current_user_can( 'edit_products' ) ) {
		wp_die( esc_html__( 'Nu ai acces la clasificarea în masă.', 'zion-core' ), '', array( 'response' => 403 ) );
	}
	$result   = zion_core_shop_admin_request();
	$state    = zion_core_shop_admin_filter( 'zion_shop_state' );
	$category = absint( zion_core_shop_admin_filter( 'zion_product_cat' ) );
	$page     = max( 1, absint( zion_core_shop_admin_filter( 'zion_page' ) ) );
	$log_page = max( 1, absint( zion_core_shop_admin_filter( 'zion_log_page' ) ) );
	$args     = array(
		'zion_shop_state'  => $state,
		'zion_product_cat' => $category,
	);
	$products = zion_core_shop_admin_inventory( $state, $category, $page );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Clasificare vitrine Zion', 'zion-core' ); ?></h1>
		<p><?php esc_html_e( 'Selectează produse, apoi compară clasificarea curentă cu cea propusă. Poți selecta până la 20 de produse de pe pagina curentă.', 'zion-core' ); ?></p>
		<?php zion_core_render_shop_admin_result( $result ); ?>
		<?php zion_core_render_shop_setup(); ?>

		<h2><?php esc_html_e( 'Produse', 'zion-core' ); ?></h2>
		<form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
			<input type="hidden" name="post_type" value="product">
			<input type="hidden" name="page" value="zion-shops">
			<label for="zion-product-cat"><?php esc_html_e( 'Categorie', 'zion-core' ); ?></label>
			<?php
			wp_dropdown_categories(
				array(
					'taxonomy'        => 'product_cat',
					'name'            => 'zion_product_cat',
					'id'              => 'zion-product-cat',
					'show_option_all' => __( 'Toate categoriile', 'zion-core' ),
					'hierarchical'    => true,
					'hide_empty'      => false,
					'selected'        => $category,
				)
			);
			zion_core_shop_admin_selector( $state );
			?>
			<button class="button"><?php esc_html_e( 'Filtrează produse', 'zion-core' ); ?></button>
		</form>

		<form method="post" action="<?php echo esc_url( zion_core_shop_admin_url( array_merge( $args, array( 'zion_page' => $page ) ) ) ); ?>">
			<?php wp_nonce_field( 'zion_core_shop_admin' ); ?>
			<p>
				<label for="zion-shop-operation"><?php esc_html_e( 'Operație', 'zion-core' ); ?></label>
				<select id="zion-shop-operation" name="zion_shop_operation">
					<option value="add"><?php esc_html_e( 'Adaugă vitrine', 'zion-core' ); ?></option>
					<option value="remove"><?php esc_html_e( 'Elimină vitrine', 'zion-core' ); ?></option>
					<option value="replace"><?php esc_html_e( 'Înlocuiește vitrinele', 'zion-core' ); ?></option>
				</select>
			</p>
			<fieldset>
				<legend><?php esc_html_e( 'Vitrine țintă', 'zion-core' ); ?></legend>
				<?php foreach ( zion_core_shops() as $slug => $shop ) : ?>
					<label><input type="checkbox" name="shops[]" value="<?php echo esc_attr( $slug ); ?>"> <?php echo esc_html( $shop['title'] ); ?></label>
				<?php endforeach; ?>
				<p class="description"><?php esc_html_e( 'Înlocuirea fără o vitrină selectată marchează produsele ca neclasificate.', 'zion-core' ); ?></p>
			</fieldset>
			<?php zion_core_render_shop_inventory( $products->posts ); ?>
			<p>
				<button class="button button-primary" name="zion_shop_action" value="preview"><?php esc_html_e( 'Previzualizează', 'zion-core' ); ?></button>
				<button class="button" name="zion_shop_action" value="dry_run"><?php esc_html_e( 'Dry-run', 'zion-core' ); ?></button>
			</p>
		</form>
		<?php zion_core_shop_admin_pagination( $args, 'zion_page', $page, $products->max_num_pages ); ?>
		<?php zion_core_render_shop_journal( $args, $log_page ); ?>
	</div>
	<?php
}

/**
 * Render the bulk selection table.
 *
 * @param array $products Inventory posts.
 */
function zion_core_render_shop_inventory( array $products ): void {
	?>
	<table class="wp-list-table widefat striped">
		<thead><tr>
			<td class="check-column"><input type="checkbox" aria-label="<?php esc_attr_e( 'Selectează toate produsele afișate', 'zion-core' ); ?>"></td>
			<th scope="col"><?php esc_html_e( 'Produs', 'zion-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Stare', 'zion-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Clasificare', 'zion-core' ); ?></th>
		</tr></thead>
		<tbody>
		<?php foreach ( $products as $product ) : ?>
			<tr>
				<th scope="row" class="check-column">
					<?php if ( zion_core_can_classify_product( $product->ID ) ) : ?>
						<input type="checkbox" name="product_ids[]" value="<?php echo esc_attr( (string) $product->ID ); ?>" aria-label="<?php echo esc_attr( get_the_title( $product ) ); ?>">
					<?php endif; ?>
				</th>
				<td><?php echo esc_html( get_the_title( $product ) ); ?> <span class="description">#<?php echo esc_html( (string) $product->ID ); ?></span></td>
				<td><?php echo esc_html( get_post_status_object( $product->post_status )->label ); ?></td>
				<td><?php echo esc_html( zion_core_shop_status_label( zion_core_shop_product_slugs( $product->ID ) ) ); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if ( ! $products ) : ?>
			<tr><td colspan="4"><?php esc_html_e( 'Niciun produs pentru filtrele selectate.', 'zion-core' ); ?></td></tr>
		<?php endif; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Show links to editorial pages and offer provisioning for missing ones.
 */
function zion_core_render_shop_setup(): void {
	?>
	<h2><?php esc_html_e( 'Paginile vitrinelor', 'zion-core' ); ?></h2>
	<ul>
		<?php
		$missing        = false;
		$native_shop_id = (int) get_option( 'woocommerce_shop_page_id' );
		foreach ( zion_core_shops() as $shop ) :
			$page    = get_page_by_path( $shop['slug'], OBJECT, 'page' );
			$link    = $page ? get_edit_post_link( $page->ID ) : '';
			$missing = $missing || ! $page;
			?>
			<li><?php echo esc_html( $shop['title'] . ' /' . $shop['slug'] . '/' ); ?>
				<?php if ( $link ) : ?>
					· <a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Editează pagina', 'zion-core' ); ?></a>
				<?php elseif ( ! $page ) : ?>
					· <?php esc_html_e( 'Pagina lipsește', 'zion-core' ); ?>
				<?php endif; ?>
				<?php if ( $page && $page->ID === $native_shop_id ) : ?>
					· <strong><?php esc_html_e( 'Pagina este configurată ca Shop WooCommerce. Alege o pagină Shop separată pentru a folosi acest URL ca vitrină.', 'zion-core' ); ?></strong>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( $missing && current_user_can( 'publish_pages' ) ) : ?>
		<form method="post" action="<?php echo esc_url( zion_core_shop_admin_url() ); ?>">
			<?php wp_nonce_field( 'zion_core_shop_admin' ); ?>
			<button class="button" name="zion_shop_action" value="pages"><?php esc_html_e( 'Creează paginile lipsă', 'zion-core' ); ?></button>
		</form>
	<?php endif; ?>
	<?php
}

/**
 * Render preview, dry-run, write result or errors.
 *
 * @param array|WP_Error $result Operation result.
 */
function zion_core_render_shop_admin_result( array|WP_Error $result ): void {
	if ( is_wp_error( $result ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		return;
	}
	if ( isset( $result['pages'] ) ) {
		foreach ( $result['pages'] as $page ) {
			if ( is_wp_error( $page ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $page->get_error_message() ) . '</p></div>';
			}
		}
		return;
	}
	if ( ! isset( $result['rows'] ) ) {
		return;
	}
	$heading = match ( $result['mode'] ) {
		'preview' => __( 'Previzualizare clasificare', 'zion-core' ),
		'dry_run' => __( 'Dry-run finalizat', 'zion-core' ),
		default => __( 'Rezultatul aplicării', 'zion-core' ),
	};
	?>
	<section aria-label="<?php echo esc_attr( $heading ); ?>">
		<h2><?php echo esc_html( $heading ); ?></h2>
		<?php if ( 'applied' !== $result['mode'] ) : ?>
			<p><?php esc_html_e( 'Clasificarea produselor și jurnalul nu au fost modificate.', 'zion-core' ); ?></p>
		<?php endif; ?>
		<?php zion_core_render_shop_changes( $result['rows'] ); ?>
		<?php if ( isset( $result['token'] ) ) : ?>
			<form method="post" action="<?php echo esc_url( zion_core_shop_admin_url() ); ?>">
				<?php wp_nonce_field( 'zion_core_shop_admin' ); ?>
				<input type="hidden" name="zion_shop_plan" value="<?php echo esc_attr( $result['token'] ); ?>">
				<p><button class="button button-primary" name="zion_shop_action" value="apply"><?php esc_html_e( 'Aplică modificările confirmate', 'zion-core' ); ?></button></p>
			</form>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render before/after rows shared by previews and the journal.
 *
 * @param array $rows Change rows.
 */
function zion_core_render_shop_changes( array $rows ): void {
	$labels = array(
		'pending'   => __( 'În așteptare', 'zion-core' ),
		'unchanged' => __( 'Fără modificări', 'zion-core' ),
		'applied'   => __( 'Aplicat', 'zion-core' ),
		'stale'     => __( 'Produs schimbat între timp', 'zion-core' ),
		'failed'    => __( 'Scriere neverificată', 'zion-core' ),
	);
	?>
	<table class="widefat striped">
		<thead><tr>
			<th scope="col"><?php esc_html_e( 'Produs', 'zion-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Înainte', 'zion-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'După propunere', 'zion-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Rezultat', 'zion-core' ); ?></th>
		</tr></thead>
		<tbody>
		<?php foreach ( $rows as $row ) : ?>
			<tr>
				<td>#<?php echo esc_html( (string) $row['id'] ); ?> <?php echo current_user_can( 'edit_post', $row['id'] ) ? esc_html( get_the_title( $row['id'] ) ) : ''; ?></td>
				<td><?php echo esc_html( zion_core_shop_status_label( $row['before_shops'] ) ); ?></td>
				<td><?php echo esc_html( zion_core_shop_status_label( $row['after_shops'] ) ); ?></td>
				<td><?php echo esc_html( $labels[ $row['state'] ] ?? '' ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render the persistent journal with actor, date and each product outcome.
 *
 * @param array $args Inventory filters.
 * @param int   $page Journal page number.
 */
function zion_core_render_shop_journal( array $args, int $page ): void {
	$events     = new WP_Query(
		array(
			'post_type'      => 'zion_shop_journal',
			'post_status'    => 'private',
			'posts_per_page' => 10,
			'paged'          => $page,
			'orderby'        => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
		)
	);
	$operations = array(
		'add'     => __( 'Adăugare', 'zion-core' ),
		'remove'  => __( 'Eliminare', 'zion-core' ),
		'replace' => __( 'Înlocuire', 'zion-core' ),
	);
	?>
	<section aria-label="<?php esc_attr_e( 'Jurnalul clasificărilor', 'zion-core' ); ?>">
		<h2><?php esc_html_e( 'Jurnalul clasificărilor', 'zion-core' ); ?></h2>
		<?php if ( ! $events->posts ) : ?>
			<p><?php esc_html_e( 'Jurnalul nu conține încă aplicări.', 'zion-core' ); ?></p>
		<?php endif; ?>
		<?php foreach ( $events->posts as $event ) : ?>
			<?php $entry = json_decode( $event->post_content, true ); ?>
			<?php if ( is_array( $entry['rows'] ?? null ) ) : ?>
				<?php $actor = get_the_author_meta( 'display_name', (int) $event->post_author ); ?>
				<details>
					<summary>
						<?php echo esc_html( get_the_date( 'Y-m-d H:i:s', $event ) ); ?>
						· <?php echo esc_html( $actor ? $actor : '#' . $event->post_author ); ?>
						· <?php echo esc_html( $operations[ $entry['operation'] ] ?? '' ); ?>
					</summary>
					<?php zion_core_render_shop_changes( $entry['rows'] ); ?>
				</details>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php zion_core_shop_admin_pagination( $args, 'zion_log_page', $page, $events->max_num_pages ); ?>
	</section>
	<?php
}
