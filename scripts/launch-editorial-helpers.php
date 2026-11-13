<?php
/**
 * Native WordPress preflight for an explicit editorial launch plan.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reject invalid legacy templates before wp_update_post can partially write a row.
 * WordPress validates page_template after its database update, even for title edits.
 *
 * @param int   $id Existing post ID.
 * @param array $fields Explicit planned WordPress fields.
 * @return WP_Error|null
 */
function zion_theme_launch_template_preflight( int $id, array $fields ): ?WP_Error {
	$post = get_post( $id );
	if ( ! $post ) {
		return new WP_Error( 'zion_launch_missing_post', 'The backed-up post no longer exists.' );
	}
	$template = $fields['page_template'] ?? get_post_meta( $id, '_wp_page_template', true );
	if ( isset( $fields['page_template'] ) && '' === $template ) {
		return new WP_Error( 'zion_launch_empty_template', 'Choose default explicitly instead of an empty template.' );
	}
	$templates = wp_get_theme()->get_page_templates( $post, $post->post_type );
	if ( $template && 'default' !== $template && ! isset( $templates[ $template ] ) ) {
		return new WP_Error( 'zion_launch_invalid_template', 'An unsupported legacy template requires an explicit valid native replacement.' );
	}
	return null;
}
