<?php
/**
 * Native journal presentation; WordPress owns posts, queries and comments.
 *
 * @package Zion_Theme
 */

/**
 * Give explicit post searches an editable native template of their own.
 *
 * @param array $templates Native template hierarchy.
 * @return array
 */
function zion_theme_journal_search_template( array $templates ): array {
	if ( 'post' === get_query_var( 'post_type' ) ) {
		array_unshift( $templates, 'search-posts.php' );
	}
	return $templates;
}
add_filter( 'search_template_hierarchy', 'zion_theme_journal_search_template' );

/**
 * Render the same registered native card in related collections.
 *
 * @param WP_Post[] $posts Public posts already selected by WordPress.
 * @return string
 */
function zion_theme_journal_cards( array $posts ): string {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/post-card' );
	if ( ! $pattern ) {
		return '';
	}
	// Native title/author blocks also read WordPress loop globals, not only context.
	$keys  = array( 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' );
	$saved = array();
	foreach ( $keys as $key ) {
		if ( array_key_exists( $key, $GLOBALS ) ) {
			$saved[ $key ] = $GLOBALS[ $key ];
		}
	}
	$html = '';
	try {
		foreach ( $posts as $item ) {
			$GLOBALS['post'] = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Native block loop, restored in finally.
			setup_postdata( $item );
			foreach ( parse_blocks( $pattern['content'] ) as $parsed ) {
				$block = new WP_Block(
					$parsed,
					array(
						'postId'   => $item->ID,
						'postType' => 'post',
					)
				);
				$html .= $block->render();
			}
		}
	} finally {
		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $saved ) ) {
				$GLOBALS[ $key ] = $saved[ $key ]; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Restore WordPress-owned loop globals exactly.
			} else {
				unset( $GLOBALS[ $key ] );
			}
		}
	}
	return $html;
}

/**
 * Editorial fragments inside native Site Editor layouts.
 *
 * @param string   $part Fragment identifier.
 * @param WP_Block $block Native block context.
 * @return string|null Null delegates to the existing editorial components.
 */
function zion_theme_journal_fragment( string $part, WP_Block $block ): ?string {
	if ( 'journal_search_term' === $part ) {
		return '<p class="zion-editorial-intro">' . esc_html( sprintf( 'Căutare: „%s”', get_search_query( false ) ) ) . '</p>';
	}
	if ( 'journal_status' === $part ) {
		global $wp_query;
		$count = (int) ( $wp_query->found_posts ?? 0 );
		return '<p class="zion-journal-status"><strong>' . esc_html( number_format_i18n( $count ) ) . '</strong> ' . esc_html( 1 === $count ? 'articol de explorat' : 'articole de explorat' ) . '</p>';
	}
	if ( 'journal_categories' === $part ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
			)
		);
		$url   = zion_theme_journal_url();
		$links = $url ? '<li><a href="' . esc_url( $url ) . '"' . ( is_home() ? ' aria-current="page"' : '' ) . '>Toate articolele</a></li>' : '';
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$links .= '<li><a href="' . esc_url( $link ) . '"' . ( is_category( $term->term_id ) ? ' aria-current="page"' : '' ) . '>' . esc_html( $term->name ) . '</a></li>';
			}
		}
		return $links ? '<nav class="zion-journal-categories" aria-label="Categorii din jurnal"><ul>' . $links . '</ul></nav>' : '';
	}
	if ( ! in_array( $part, array( 'card_link', 'article_breadcrumb', 'article_return', 'reading_progress' ), true ) ) {
		return null;
	}
	$id = (int) ( $block->context['postId'] ?? get_the_ID() );
	if ( 'post' !== get_post_type( $id ) || post_password_required( $id ) ) {
		return '';
	}
	if ( 'card_link' === $part ) {
		return '<a class="zion-post-card-link" href="' . esc_url( get_permalink( $id ) ) . '">Citește articolul <span aria-hidden="true">↗</span></a>';
	}
	if ( ! is_singular( 'post' ) || get_queried_object_id() !== $id ) {
		return '';
	}
	if ( 'reading_progress' === $part ) {
		return '<div class="zion-reading-progress" hidden><progress max="100" value="0" aria-label="Progresul lecturii"></progress><span data-zion-reading-label>0% parcurs</span></div>';
	}
	$url = zion_theme_journal_url();
	if ( ! $url ) {
		return '';
	}
	if ( 'article_return' === $part ) {
		return '<p class="zion-article-return"><a href="' . esc_url( $url ) . '">← Înapoi la jurnal</a></p>';
	}
	return '<nav class="zion-article-breadcrumb" aria-label="Fir de navigare"><ol><li><a href="' . esc_url( home_url( '/' ) ) . '">Acasă</a></li><li><a href="' . esc_url( $url ) . '">Jurnal</a></li><li aria-current="page">' . esc_html( get_the_title( $id ) ) . '</li></ol></nav>';
}

/**
 * Decorative scenes are injected only at frontend render time.
 *
 * @param string $html Rendered group.
 * @param array  $block Native group.
 * @return string
 */
function zion_theme_journal_motion( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	$type    = in_array( 'zion-editorial-motion', $classes, true ) ? 'journal-pages' : ( in_array( 'zion-reading-map', $classes, true ) ? 'article-flow' : '' );
	if ( ! $type || ( 'article-flow' === $type && post_password_required() ) ) {
		return $html;
	}
	zion_theme_enqueue_continuum();
	return preg_replace( '~(</div>\s*)$~', '<canvas class="zion-spatial-canvas" data-zion-spatial="' . esc_attr( $type ) . '" aria-hidden="true"></canvas>$1', $html, 1 ) ?? $html;
}
add_filter( 'render_block_core/group', 'zion_theme_journal_motion', 29, 2 );

/**
 * Keep a missing photograph intentional, respecting existing visibility rules.
 *
 * @param string   $html Native image output.
 * @param array    $block Parsed block.
 * @param WP_Block $instance Native post context.
 * @return string
 */
function zion_theme_journal_image( string $html, array $block, WP_Block $instance ): string {
	$id      = (int) ( $instance->context['postId'] ?? get_the_ID() );
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	if ( ! in_array( 'zion-blog-image', $classes, true ) || 'post' !== get_post_type( $id ) || is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	if ( post_password_required( $id ) ) {
		return '';
	}
	if ( '' === trim( $html ) && ! in_array( 'zion-article-cover', $classes, true ) && zion_theme_editorial_setting( 'blog_image', $id ) ) {
		return '<div class="zion-editorial-placeholder" aria-hidden="true"><span>Z / FIELD NOTES</span><svg viewBox="0 0 320 180" fill="none"><path d="M80 125 160 155 240 125 160 95Z M80 95 160 125 240 95 160 65Z M80 65 160 95 240 65 160 35Z M160 35V155" stroke="currentColor"/></svg></div>';
	}
	return $html;
}
add_filter( 'render_block_core/post-featured-image', 'zion_theme_journal_image', 25, 3 );

/**
 * Avoid an automatic copy of the opening paragraph in single article headers.
 *
 * @param string   $html Native excerpt.
 * @param array    $block Parsed block.
 * @param WP_Block $instance Native post context.
 * @return string
 */
function zion_theme_journal_intro( string $html, array $block, WP_Block $instance ): string {
	return in_array( 'zion-article-intro', explode( ' ', $block['attrs']['className'] ?? '' ), true ) && ! has_excerpt( $instance->context['postId'] ?? get_the_ID() ) ? '' : $html;
}
add_filter( 'render_block_core/post-excerpt', 'zion_theme_journal_intro', 25, 3 );

/**
 * Normalize body H1 tags at display time while preserving raw-text islands.
 * WordPress tokenization leaves attributes, prose, SVG and stored content intact.
 *
 * @param string $html Rendered article body.
 * @return string
 */
function zion_theme_journal_heading_levels( string $html ): string {
	$tokens = wp_html_split( $html );
	$raw    = '';
	foreach ( $tokens as &$token ) {
		if ( $raw ) {
			if ( preg_match( '~^</' . $raw . '\s*>$~i', $token ) ) {
				$raw = '';
			}
			continue;
		}
		if ( preg_match( '~^<(script|style|textarea|title|xmp|iframe|noembed|noframes)\b~i', $token, $match ) ) {
			$raw = strtolower( $match[1] );
			continue;
		}
		$token = preg_replace( '~^(</?)h1(?=[\s>])~i', '$1h2', $token ) ?? $token;
	}
	unset( $token );
	return implode( '', $tokens );
}

/**
 * Apply heading hierarchy only to this theme's marked single-post content block.
 *
 * @param string $html Native content output.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_journal_content( string $html, array $block ): string {
	if ( ! is_admin() && ! wp_is_serving_rest_request() && is_singular( 'post' ) && in_array( 'zion-article-copy', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return zion_theme_journal_heading_levels( $html );
	}
	return $html;
}
add_filter( 'render_block_core/post-content', 'zion_theme_journal_content', 25, 2 );

/**
 * Localize the native comment form without replacing its submission lifecycle.
 *
 * @param array $args Native comment-form defaults.
 * @return array
 */
function zion_theme_journal_comment_form( array $args ): array {
	if ( ! is_singular( 'post' ) || is_admin() || wp_is_serving_rest_request() ) {
		return $args;
	}
	$args['title_reply']          = 'Lasă un comentariu';
	$args['title_reply_to']       = 'Răspunde lui %s';
	$args['cancel_reply_link']    = 'Anulează răspunsul';
	$args['label_submit']         = 'Trimite comentariul';
	$args['comment_notes_before'] = '<p class="comment-notes">Adresa de e-mail nu va fi publicată. Câmpurile marcate cu * sunt obligatorii.</p>';
	$args['comment_field']        = '<p class="comment-form-comment"><label for="comment">Comentariu <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required></textarea></p>';
	if ( is_user_logged_in() ) {
		$args['logged_in_as'] = '<p class="logged-in-as">Conectat ca ' . esc_html( wp_get_current_user()->display_name ) . '. <a href="' . esc_url( get_edit_profile_url() ) . '">Profilul tău</a> · <a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">Deconectare</a></p>';
	}
	return $args;
}
add_filter( 'comment_form_defaults', 'zion_theme_journal_comment_form' );

/**
 * Translate labels while retaining WordPress fields, IDs and consent control.
 *
 * @param array $fields Native comment fields.
 * @return array
 */
function zion_theme_journal_comment_fields( array $fields ): array {
	if ( is_singular( 'post' ) && ! is_admin() && ! wp_is_serving_rest_request() ) {
		$labels = array(
			'>Name'    => '>Nume',
			'>Email'   => '>E-mail',
			'>Website' => '>Website (opțional)',
			'Save my name, email, and website in this browser for the next time I comment.' => 'Salvează numele, e-mailul și website-ul în acest browser pentru următorul comentariu.',
		);
		foreach ( $fields as &$field ) {
			$field = strtr( $field, $labels );
		}
		unset( $field );
	}
	return $fields;
}
add_filter( 'comment_form_default_fields', 'zion_theme_journal_comment_fields' );
