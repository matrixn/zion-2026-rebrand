<?php
/**
 * Title: Zion · Homepage / Pulse
 * Slug: zion-theme/home-pulse
 * Categories: zion-library
 * Description: Editable service card with the shared Pulse circuit preview.
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"tagName":"article","className":"zion-home-service zion-home-service-pulse zion-pulse","layout":{"type":"default"}} -->
<article class="wp-block-group zion-home-service zion-home-service-pulse zion-pulse">
<!-- wp:group {"className":"zion-home-pulse-copy","layout":{"type":"default"}} -->
<div class="wp-block-group zion-home-pulse-copy">
<!-- wp:paragraph {"className":"zion-overview-label"} -->
<p class="zion-overview-label">08 / INTERACTIVE ENGINEERING</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="<?php echo esc_url( home_url( '/zion-pulse/' ) ); ?>">Zion Pulse<span aria-hidden="true"> ↗</span></a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Dăm inteligență lucrurilor din jurul tău. Automatizări electronice, senzori și sisteme interactive, de la primul impuls la un mecanism complet.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"zion-home-pulse-tags"} -->
<p class="zion-home-pulse-tags">ESP32 · NFC/RFID · Wi-Fi · Hardware + Software</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:shortcode -->
[zion_pulse type="preview"]
<!-- /wp:shortcode -->
</article>
<!-- /wp:group -->
