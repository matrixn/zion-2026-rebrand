<?php
/**
 * Title: Zion editorial card
 * Slug: zion-theme/post-card
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"tagName":"article","className":"zion-card zion-post-card"} -->
<article class="wp-block-group zion-card zion-post-card">
<!-- wp:post-featured-image {"aspectRatio":"16/9","className":"zion-blog-image"} /-->
<!-- wp:group {"className":"zion-post-card-copy"} -->
<div class="wp-block-group zion-post-card-copy">
<!-- wp:post-terms {"term":"category","className":"zion-blog-categories"} /-->
<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"card"} /-->
<!-- wp:group {"className":"zion-post-card-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group zion-post-card-meta">
<!-- wp:post-date {"format":"d.m.Y","className":"zion-blog-date"} /-->
<!-- wp:zion/editorial {"part":"reading_time"} /-->
</div><!-- /wp:group -->
<!-- wp:post-excerpt {"excerptLength":24,"className":"zion-blog-excerpt"} /-->
<!-- wp:group {"className":"zion-post-card-foot","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<div class="wp-block-group zion-post-card-foot">
<!-- wp:post-author-name {"className":"zion-blog-author"} /-->
<!-- wp:zion/editorial {"part":"card_link"} /-->
</div><!-- /wp:group -->
</div><!-- /wp:group -->
</article><!-- /wp:group -->
