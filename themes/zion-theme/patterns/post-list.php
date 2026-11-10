<?php
/**
 * Title: Zion editorial collection
 * Slug: zion-theme/post-list
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:query {"query":{"inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide">
	<!-- wp:post-template {"className":"zion-editorial-grid"} -->
		<!-- wp:group {"tagName":"article","className":"zion-card zion-post-card"} -->
		<article class="wp-block-group zion-card zion-post-card">
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"zion-blog-image"} /-->
			<!-- wp:post-date {"className":"zion-kicker zion-blog-date"} /-->
			<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"card"} /-->
			<!-- wp:post-author-name {"className":"zion-blog-author"} /-->
			<!-- wp:post-terms {"term":"category","className":"zion-blog-categories"} /-->
			<!-- wp:zion/editorial {"part":"reading_time"} /-->
			<!-- wp:post-excerpt {"excerptLength":24,"className":"zion-blog-excerpt"} /-->
		</article>
		<!-- /wp:group -->
	<!-- /wp:post-template -->
	<!-- wp:query-pagination {"className":"zion-pagination","layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous {"label":"Înapoi"} /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next {"label":"Mai departe"} /-->
	<!-- /wp:query-pagination -->
	<!-- wp:query-no-results -->
		<!-- wp:paragraph -->
		<p>Nu am găsit rezultate. Încearcă o altă căutare.</p>
		<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
