<?php
/**
 * Optional editorial illustration; no product, order or personal data is used.
 *
 * @package Zion_Theme
 */

/** Render one progressively enhanced, self-contained learning illustration. */
function zion_theme_learning_lab(): string {
	wp_enqueue_script(
		'zion-learning-lab',
		get_theme_file_uri( 'assets/blocks/learning-lab.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	$id = wp_unique_id( 'zion-learning-' );
	ob_start();
	?>
	<section class="zion-learning-lab" data-learning-lab aria-label="Laborator interactiv: de la formă la descoperire">
		<div class="zion-learning-lab-top"><span>CONTINUUM / CURIOSITY LAB</span><span>01 → 02 → 03</span></div>
		<div class="zion-learning-lab-steps" role="group" aria-label="Etapele experienței">
			<?php foreach ( array( 'Printează', 'Colorează', 'Descoperă' ) as $index => $label ) : ?>
				<button type="button" data-learning-step="<?php echo esc_attr( $index + 1 ); ?>" aria-controls="<?php echo esc_attr( $id . '-panel-' . ( $index + 1 ) ); ?>" aria-pressed="false" disabled><span>0<?php echo esc_html( $index + 1 ); ?></span> <?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>
		<div class="zion-learning-lab-grid">
			<div class="zion-learning-lab-visual" aria-hidden="true">
				<svg class="zion-learning-lab-svg" viewBox="0 0 360 320" fill="none" focusable="false">
					<path class="zion-learning-trace" d="M28 280V40h304v240H28m0-40h304M68 40v240m224-240v240"/>
					<g class="zion-learning-plate">
						<path d="m54 231 126 49 126-49v-18L180 164 54 213Z"/>
						<path d="m54 213 126 49 126-49M180 262v18"/>
						<ellipse cx="180" cy="207" rx="91" ry="33"/>
						<ellipse cx="180" cy="207" rx="65" ry="24"/>
						<ellipse cx="180" cy="207" rx="38" ry="14"/>
					</g>
					<g class="zion-learning-parts">
						<circle cx="180" cy="181" r="26"/>
						<circle cx="116" cy="213" r="13"/>
						<circle cx="251" cy="219" r="18"/>
					</g>
					<g class="zion-learning-layer"><path d="M87 152h186M87 158h186M87 164h186"/></g>
					<g class="zion-learning-flags">
						<path d="M180 151V99m-41 0h82V67h-82ZM116 193v-58m-29 0h58v-28H87Zm135 0V119m-28 0h56V91h-56Z"/>
						<path d="m166 83 9 8 18-18m-89 48h24m110-16h27"/>
					</g>
				</svg>
				<span class="zion-learning-lab-coordinate">FORMĂ / CULOARE / SENS</span>
			</div>
			<div class="zion-learning-lab-panels">
				<div id="<?php echo esc_attr( $id . '-panel-1' ); ?>" data-learning-panel="1">
					<h3>O idee capătă volum.</h3>
					<p>Strat cu strat, un model digital devine un obiect pe care îl poți ține în mână. Forma este începutul experienței.</p>
				</div>
				<div id="<?php echo esc_attr( $id . '-panel-2' ); ?>" data-learning-panel="2">
					<h3>Tu adaugi personalitatea.</h3>
					<p>În această ilustrație poți aprinde accentul lime. Într-un kit real, culorile sunt adăugate de copil, urmând ghidul produsului.</p>
					<button type="button" class="zion-learning-paint" data-learning-paint aria-pressed="false" disabled>Adaugă culoare</button>
				</div>
				<div id="<?php echo esc_attr( $id . '-panel-3' ); ?>" data-learning-panel="3">
					<h3>Din culoare se naște o întrebare.</h3>
					<p>Ce rol are un steag cu denumirea unei componente?</p>
					<div class="zion-learning-answers" role="group" aria-label="Alege un răspuns">
						<button type="button" data-learning-answer="correct" aria-pressed="false" disabled>Leagă numele de ceea ce observi.</button>
						<button type="button" data-learning-answer="retry" aria-pressed="false" disabled>Înlocuiește toate explicațiile din ghid.</button>
					</div>
				</div>
				<p class="zion-learning-lab-status" data-learning-status role="status" aria-live="polite">Activează JavaScript pentru demonstrație. Cele trei etape pot fi citite și fără animații.</p>
			</div>
		</div>
		<div class="zion-learning-lab-footer"><button type="button" data-learning-reset disabled>Reia explorarea ↺</button><button type="button" data-learning-motion aria-pressed="false" disabled>Pauză animație</button></div>
		<p class="zion-learning-lab-disclaimer">Ilustrație interactivă schematică. Nu reproduce un produs comercial și nu este quiz-ul oficial al unui producător.</p>
	</section>
	<?php
	return (string) ob_get_clean();
}
