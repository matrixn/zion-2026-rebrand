<?php
/**
 * Title: Zion product catalog
 * Slug: zion-theme/product-catalog
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:woocommerce/store-notices {"align":"wide"} /-->
<!-- wp:group {"align":"wide","className":"zion-catalog-toolbar","layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide zion-catalog-toolbar">
	<!-- wp:woocommerce/product-results-count /-->
	<!-- wp:woocommerce/catalog-sorting /-->
</div>
<!-- /wp:group -->
<!-- wp:woocommerce/product-collection {"queryId":72,"query":{"postType":"product","perPage":12,"inherit":true,"isProductCollectionBlock":true},"displayLayout":{"type":"flex","columns":3},"align":"wide"} -->
<div class="wp-block-woocommerce-product-collection alignwide">
	<!-- wp:woocommerce/product-template {"className":"zion-product-grid"} -->
		<!-- wp:woocommerce/product-image {"isDescendentOfQueryLoop":true,"aspectRatio":"4/3"} /-->
		<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"card"} /-->
		<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
		<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->
	<!-- /wp:woocommerce/product-template -->
	<!-- wp:query-pagination {"className":"zion-pagination","layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous {"label":"Înapoi"} /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next {"label":"Mai departe"} /-->
	<!-- /wp:query-pagination -->
	<!-- wp:woocommerce/product-collection-no-results -->
		<!-- wp:paragraph -->
		<p>Nu am găsit produse pentru această selecție.</p>
		<!-- /wp:paragraph -->
	<!-- /wp:woocommerce/product-collection-no-results -->
</div>
<!-- /wp:woocommerce/product-collection -->
