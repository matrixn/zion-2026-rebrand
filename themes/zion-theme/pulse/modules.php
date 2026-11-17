<?php
/**
 * Accessible modular architecture: each button explains one possible interface.
 *
 * @package Zion_Theme
 */

$zion_theme_module_id = wp_unique_id( 'zion-pulse-module-' );
$zion_theme_modules   = array(
	'Panou de butoane'     => 'Intrări clare, secvențe și combinații. Logica este adaptată interacțiunii pe care o proiectăm.',
	'Senzori NFC/RFID'     => 'Identificarea obiectelor sau a etichetelor compatibile, cu cititoare și distanțe stabilite pentru proiect.',
	'Iluminare LED'        => 'Feedback vizual, efecte și secvențe de lumină. Alimentarea și controlul se dimensionează pentru instalație.',
	'Efecte și actuatoare' => 'Ieșiri și interfețe de comandă compatibile, selectate în funcție de sarcină și de cerințele de siguranță.',
	'Panou Game Master'    => 'Stare, reset și comenzi de operare adaptate scenariului, cu limite și permisiuni definite împreună.',
	'Interfață software'   => 'Monitorizare și configurare printr-un dashboard local sau integrare software, unde arhitectura o permite.',
);
?>
<div class="zion-pulse-modules" data-pulse-modules>
	<div class="zion-pulse-hub"><span aria-hidden="true">⌁</span><strong>CONTROLLER<br>CENTRAL</strong><small>ARHITECTURĂ MODULARĂ</small></div>
	<div class="zion-pulse-module-grid" role="group" aria-label="Explorează modulele controllerului">
	<?php foreach ( $zion_theme_modules as $zion_theme_label => $zion_theme_description ) : ?>
		<button type="button" data-pulse-module data-description="<?php echo esc_attr( $zion_theme_description ); ?>" aria-pressed="false" aria-controls="<?php echo esc_attr( $zion_theme_module_id ); ?>" disabled><span aria-hidden="true">↳</span><?php echo esc_html( $zion_theme_label ); ?></button>
	<?php endforeach; ?>
	</div>
	<p id="<?php echo esc_attr( $zion_theme_module_id ); ?>" class="zion-pulse-module-description" role="status" aria-live="polite" data-pulse-module-description>Selectează un modul pentru a vedea cum se poate conecta. Compatibilitatea depinde de hardware și de cerințele proiectului.</p>
	<noscript><p>Arhitectura poate conecta panouri de butoane, NFC/RFID, iluminare LED, actuatoare, comenzi Game Master și interfețe software. Activează JavaScript pentru explicațiile interactive ale modulelor.</p></noscript>
</div>
