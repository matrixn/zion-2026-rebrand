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
		<!-- wp:pattern {"slug":"zion-theme/post-card"} /-->
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
