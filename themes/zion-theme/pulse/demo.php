<?php
/**
 * An isolated software-only puzzle. Native buttons and live status are accessible.
 *
 * @package Zion_Theme
 */

$zion_theme_demo_id = wp_unique_id( 'zion-pulse-demo-' );
?>
<div class="zion-pulse-demo" data-pulse-demo data-state="ready" aria-label="Demonstrație software puzzle electronic">
	<div class="zion-pulse-demo-bench">
		<div class="zion-pulse-console-top"><span>01 / SEQUENCE CONTROLLER</span><span>SIMULARE SOFTWARE</span></div>
		<div class="zion-pulse-demo-display">
			<div class="zion-pulse-lock" data-pulse-lock aria-hidden="true"><span class="zion-pulse-lock-shackle"></span><span class="zion-pulse-lock-box"><span>▰</span><small>Z / PULSE</small></span></div>
			<div><p class="zion-pulse-eyebrow">ACCES SCENOGRAFIC / VIRTUAL</p><h3>Un impuls corect.<br>Încă un pas înainte.</h3><p>Verde → Galben → Albastru → Roșu → Alb</p></div>
		</div>
		<p id="<?php echo esc_attr( $zion_theme_demo_id ); ?>" class="zion-pulse-instructions">Apasă culorile în ordinea indicată. O alegere greșită reia secvența de la început.</p>
		<div class="zion-pulse-keys" role="group" aria-label="Butoanele secvenței" aria-describedby="<?php echo esc_attr( $zion_theme_demo_id ); ?>">
		<?php
		foreach ( array(
			'verde'    => 'Verde',
			'galben'   => 'Galben',
			'albastru' => 'Albastru',
			'rosu'     => 'Roșu',
			'alb'      => 'Alb',
		) as $zion_theme_color => $zion_theme_label ) :
			?>
			<button type="button" class="zion-pulse-key zion-pulse-key--<?php echo esc_attr( $zion_theme_color ); ?>" data-pulse-key="<?php echo esc_attr( $zion_theme_color ); ?>" aria-pressed="false" disabled><span class="zion-pulse-key-led" aria-hidden="true"></span><span><?php echo esc_html( $zion_theme_label ); ?></span><small data-pulse-key-status>În așteptare</small></button>
		<?php endforeach; ?>
		</div>
		<p class="zion-pulse-demo-fallback" data-pulse-fallback>Activează JavaScript pentru a încerca simularea. Secvența descrie o logică demonstrativă; nu controlează echipamente reale.</p>
	</div>
	<aside class="zion-pulse-gm" aria-label="Panou Game Master">
		<p class="zion-pulse-eyebrow">GAME MASTER</p><div class="zion-pulse-gm-status"><span class="zion-pulse-dot"></span><strong data-pulse-state>În așteptare</strong></div>
		<dl><div><dt>Progres</dt><dd data-pulse-progress>0 / 5</dd></div><div><dt>Cutie virtuală</dt><dd data-pulse-door>Blocată</dd></div><div><dt>Mod</dt><dd>Doar software</dd></div><div><dt>Conexiune hardware</dt><dd>Inexistentă</dd></div></dl>
		<div class="zion-pulse-progress" aria-hidden="true"><span data-pulse-meter></span></div>
		<p class="zion-pulse-live" role="status" aria-live="polite" aria-atomic="true" data-pulse-message>Aștept prima comandă: Verde.</p>
		<div class="zion-pulse-gm-actions"><button type="button" data-pulse-reset disabled>RESET</button><button type="button" data-pulse-solve disabled>FORCE SOLVE</button></div>
		<p class="zion-pulse-small">Controale demonstrative. Fără dispozitive conectate, fără comenzi către server.</p>
	</aside>
</div>
