<?php
/**
 * Title: Zion FAQ / Demonstration questions
 * Slug: zion-theme/faq-questions
 * Categories: zion-library
 *
 * @package Zion_Theme
 */

$zion_theme_faq_examples = array(
	'Cum pregătesc un fișier pentru ofertă?'         => 'Include modelul 3D, dimensiunile și utilizarea dorită. Pentru această demonstrație folosim fișiere STL, STEP sau 3MF, fără date reale de client.',
	'Cum aleg materialul potrivit?'                  => 'Materialul se alege în funcție de temperatură, solicitări mecanice și aspect. Comparăm cerințele înainte de a propune o soluție.',
	'Pot începe de la o idee sau o piesă existentă?' => 'O schiță ori câteva măsurători pot porni o discuție despre proiectare. Geometria și toleranțele trebuie clarificate înaintea producției.',
	'Cum se stabilește termenul unui proiect?'       => 'Estimarea depinde de complexitate, cantitate și operațiunile necesare. Termenul se confirmă în oferta proiectului.',
	'Cum discutăm o serie de piese?'                 => 'Precizează cantitatea, cerințele de repetabilitate și dacă este util un prototip înainte de serie.',
	'Ce dimensiuni pot avea piesele?'                => 'Verificăm modelul și posibilitatea de fabricație. Pentru piese mari discutăm și o construcție modulară, dacă este potrivită.',
	'Ce influențează rezistența unei piese?'         => 'Materialul, geometria, orientarea straturilor și mediul de utilizare influențează comportamentul final.',
	'Cum aleg culoarea și finisajul?'                => 'Spune-ne preferințele de aspect. Disponibilitatea materialelor și operațiunile de finisare se verifică pentru fiecare proiect.',
	'Cum stabilim toleranțele?'                      => 'Pornim de la suprafețele de contact și utilizarea piesei. Pentru ansambluri poate fi necesară o probă de montaj.',
	'Unde găsesc produsele disponibile?'             => 'Catalogul 3D prezintă produsele disponibile. Pentru adaptări sau o piesă nouă, contactează-ne cu detaliile proiectului.',
);
foreach ( $zion_theme_faq_examples as $zion_theme_faq_question => $zion_theme_faq_answer ) {
	echo get_comment_delimited_block_content( 'core/details', array(), '<details class="wp-block-details"><summary>' . esc_html( $zion_theme_faq_question ) . '</summary>' . zion_theme_service_text( $zion_theme_faq_answer ) . '</details>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped text in native serialized blocks.
}
