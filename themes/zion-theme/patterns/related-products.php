<?php
/**
 * Title: Related products
 * Slug: zion-theme/related-products
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:woocommerce/product-collection {"align":"wide","queryId":0,"query":{"postType":"product","perPage":5,"inherit":false,"isProductCollectionBlock":true,"woocommerceStockStatus":["instock","onbackorder"],"relatedBy":{"categories":true,"tags":true}},"displayLayout":{"type":"flex","columns":5,"shrinkColumns":false},"collection":"woocommerce/product-collection/related","hideControls":["inherit"],"queryContextIncludes":["collection"]} -->
<div class="wp-block-woocommerce-product-collection alignwide">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Related products', 'woocommerce' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:woocommerce/product-template -->
	<!-- wp:zion/product-card {"level":3} /-->
	<!-- /wp:woocommerce/product-template -->
</div>
<!-- /wp:woocommerce/product-collection -->
