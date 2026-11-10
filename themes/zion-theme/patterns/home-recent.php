<?php
/**
 * Title: Zion · Articole recente
 * Slug: zion-theme/home-recent
 * Categories: zion-library
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"zion-section"} -->
<section class="wp-block-group alignfull zion-section">
<!-- wp:group {"className":"zion-shell"} -->
<div class="wp-block-group zion-shell">
<!-- wp:heading {"className":"zion-section-title"} -->
<h2 class="wp-block-heading zion-section-title">Articole recente</h2>
<!-- /wp:heading -->
<!-- wp:query {"queryId":91,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"zion-home-recent"} -->
<div class="wp-block-query zion-home-recent">
<!-- wp:post-template -->
<!-- wp:group {"tagName":"article","className":"zion-card zion-post-card"} -->
<article class="wp-block-group zion-card zion-post-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
<!-- wp:post-date {"className":"zion-kicker"} /-->
<!-- wp:post-title {"level":3,"isLink":true} /-->
<!-- wp:post-excerpt {"excerptLength":20} /-->
</article>
<!-- /wp:group -->
<!-- /wp:post-template -->
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Articolele publicate vor apărea aici.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
