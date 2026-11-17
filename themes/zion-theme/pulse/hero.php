<?php
/**
 * Original SVG controller: a signal network rather than an orbital scene.
 *
 * @package Zion_Theme
 */

?>
<div class="zion-pulse-console zion-pulse-motion" data-pulse-motion-scene>
	<div class="zion-pulse-console-top"><span><i class="zion-pulse-dot"></i> PULSE / SIGNAL LAB</span><span>HW + FW + SW</span></div>
	<svg class="zion-pulse-circuit" viewBox="0 0 620 500" fill="none" aria-hidden="true" focusable="false">
		<g class="zion-pulse-traces">
			<path d="M250 205H185V85H90M250 250H80M250 295H185V415H90M370 205H435V85H530M370 250H540M370 295H435V415H530"/>
			<path d="M285 170V115H220V35M335 170V115H400V35M285 330V385H220V465M335 330V385H400V465"/>
		</g>
		<g class="zion-pulse-signal">
			<path d="M90 85H185V205H250"/><path d="M80 250H250"/><path d="M90 415H185V295H250"/>
			<path d="M370 205H435V85H530"/><path d="M370 250H540"/><path d="M370 295H435V415H530"/>
		</g>
		<g class="zion-pulse-pins"><?php for ( $zion_theme_pin = 0; $zion_theme_pin < 7; ++$zion_theme_pin ) : ?>
			<path d="M<?php echo esc_attr( 274 + $zion_theme_pin * 12 ); ?> 160v17M<?php echo esc_attr( 274 + $zion_theme_pin * 12 ); ?> 323v17M240 <?php echo esc_attr( 198 + $zion_theme_pin * 17 ); ?>h17M363 <?php echo esc_attr( 198 + $zion_theme_pin * 17 ); ?>h17"/>
		<?php endfor; ?></g>
		<rect x="250" y="175" width="120" height="150" rx="12" class="zion-pulse-chip"/>
		<rect x="263" y="188" width="94" height="124" rx="5" class="zion-pulse-chip-inner"/>
		<path d="M278 250h16l9-25 14 52 11-27h15" class="zion-pulse-wave"/>
		<g class="zion-pulse-node"><circle cx="90" cy="85" r="20"/><circle cx="80" cy="250" r="20"/><circle cx="90" cy="415" r="20"/><circle cx="530" cy="85" r="20"/><circle cx="540" cy="250" r="20"/><circle cx="530" cy="415" r="20"/></g>
		<g class="zion-pulse-node-core"><circle cx="90" cy="85" r="5"/><circle cx="80" cy="250" r="5"/><circle cx="90" cy="415" r="5"/><circle cx="530" cy="85" r="5"/><circle cx="540" cy="250" r="5"/><circle cx="530" cy="415" r="5"/></g>
		<g class="zion-pulse-svg-label"><text x="90" y="125" text-anchor="middle">SENZORI</text><text x="80" y="290" text-anchor="middle">BUTOANE</text><text x="90" y="455" text-anchor="middle">NFC / RFID</text><text x="530" y="125" text-anchor="middle">WI-FI</text><text x="540" y="290" text-anchor="middle">LED / EFECTE</text><text x="530" y="455" text-anchor="middle">DASHBOARD</text><text x="310" y="302" text-anchor="middle">Z / PULSE</text></g>
	</svg>
	<div class="zion-pulse-console-bottom"><span>INTRARE <b>→</b> LOGICĂ <b>→</b> ACȚIUNE</span><button type="button" data-pulse-motion disabled aria-pressed="false">Pauză animație</button></div>
</div>
