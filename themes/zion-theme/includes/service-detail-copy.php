<?php
/**
 * Editorial copy for individual service layouts.
 *
 * @package Zion_Theme
 */

/**
 * Get the hero, benefits and project steps for each service.
 *
 * @return array
 */
function zion_theme_service_detail_copy(): array {
	return array(
		'printare-3d'             => array(
			'hero'     => array( 'Ai nevoie de o piesă?', 'O facem reală.', '3D', 'Design / Print / Build' ),
			'lead'     => 'Trimite un model, o schiță sau dimensiunile piesei. Alegem materialul și pregătim fabricația în funcție de cum va fi montată și folosită.',
			'headings' => array( 'Piese pe care nu le găsești în magazin', 'Unde ajută printarea 3D', 'De la discuție la piesa livrată', 'Ai o piesă în minte?' ),
			'benefits' => array(
				'Componente la comandă'        => 'Realizăm suporturi, carcase și adaptoare pentru dimensiunile tale.',
				'Prototipuri pentru probă'     => 'Verifici montajul și funcționarea înainte de serie.',
				'Material potrivit utilizării' => 'Ținem cont de temperatură, uzură și solicitări.',
				'Cantități flexibile'          => 'Poți porni cu un exemplar sau cu un lot mic.',
			),
			'steps'    => array(
				'Vedem piesa sau schița' => 'Clarificăm dimensiunile și utilizarea.',
				'Alegem soluția'         => 'Stabilim materialul, costul și termenul.',
				'Printăm și predăm'      => 'Verificăm piesa și stabilim ridicarea sau livrarea.',
			),
		),
		'proiectare-3d'           => array(
			'hero'     => array( 'Dintr-o idee vagă,', 'un model precis.', '◇ 3D', 'Concept / CAD / Prototype' ),
			'lead'     => 'Pornim de la o schiță, câteva măsurători sau o componentă existentă. Modelăm geometria ținând cont de montaj și de metoda de fabricație.',
			'headings' => array( 'Un model gândit pentru utilizare', 'Situații în care modelarea ajută', 'De la cerințe la modelul 3D', 'Ai o idee fără model?' ),
			'benefits' => array(
				'De la schiță la geometrie'  => 'Traducem dimensiunile și observațiile într-un model digital.',
				'Reconstrucție după o piesă' => 'Folosim fotografii și măsurători drept referințe.',
				'Ajustări de formă'          => 'Corectăm prinderile, toleranțele și zonele de contact.',
				'Pregătire pentru probă'     => 'Livrăm fișiere pentru verificare și prototipare.',
			),
			'steps'    => array(
				'Analizăm referințele' => 'Notăm cotele și constrângerile.',
				'Modelăm geometria'    => 'Verificăm împreună forma și montajul.',
				'Pregătim fișierele'   => 'Stabilim formatul și următoarea probă.',
			),
		),
		'software-personalizat'   => array(
			'hero'     => array( 'Software-ul tău.', 'Regulile tale.', '</>', 'Custom Software / Business Solutions' ),
			'lead'     => 'Construim aplicații în jurul procesului tău, de la preluarea cererilor până la gestionarea datelor și a operațiunilor interne.',
			'headings' => array( 'Instrumente adaptate afacerii tale', 'Aplicații pentru probleme reale', 'Dezvoltare pe etape verificabile', 'Ai un proces greu de gestionat?' ),
			'benefits' => array(
				'Flux adaptat echipei'    => 'Păstrăm pașii care au sens pentru activitatea ta.',
				'Date centralizate'       => 'Consolidezi informațiile pe care le cauți zilnic.',
				'Operațiuni automatizate' => 'Reduci copierea manuală și verificările repetitive.',
				'Extindere planificată'   => 'Poți adăuga funcții când apar cerințe noi.',
			),
			'steps'    => array(
				'Urmărim fluxul actual'    => 'Identificăm utilizatorii, datele și blocajele.',
				'Construim prima versiune' => 'Prioritizăm funcțiile pe care echipa le poate testa.',
				'Lansăm și ajustăm'        => 'Verificăm aplicația în utilizare și stabilim îmbunătățirile.',
			),
		),
		'platforme-saas'          => array(
			'hero'     => array( 'Ideea ta poate deveni', 'un produs digital.', 'SaaS', 'Platform / Subscription / Growth' ),
			'lead'     => 'Definim o primă platformă utilizabilă, cu conturi, roluri și acces la funcțiile de care au nevoie clienții tăi.',
			'headings' => array( 'O platformă pe care o poți valida', 'Tipuri de produse pe care le putem construi', 'Pașii unei lansări SaaS', 'Ai o idee de platformă?' ),
			'benefits' => array(
				'Prima versiune concentrată'    => 'Pornești cu funcțiile care confirmă utilitatea produsului.',
				'Acces clar pentru utilizatori' => 'Organizăm conturile, rolurile și zona de administrare.',
				'Abonamente adaptabile'         => 'Accesul și funcțiile pot fi diferențiate pe planuri.',
				'Dezvoltare în etape'           => 'Decizi ce urmează pe baza feedbackului și utilizării.',
			),
			'steps'    => array(
				'Definim produsul'             => 'Clarificăm publicul și funcțiile esențiale.',
				'Construim versiunea inițială' => 'Punem în funcțiune conturile și fluxurile principale.',
				'Validăm cu utilizatori'       => 'Stabilim ce păstrăm și ce extindem.',
			),
		),
		'pluginuri-si-integrari'  => array(
			'hero'     => array( 'Sistemele tale pot', 'lucra împreună.', 'API', 'Plugins / Extensions / Integrations' ),
			'lead'     => 'Adăugăm funcții și conectăm platformele existente pentru ca informațiile să ajungă acolo unde le folosește echipa.',
			'headings' => array( 'Extensii pentru sistemele pe care le folosești', 'Funcții și date conectate', 'Cum pregătim o integrare', 'Îți lipsește o funcție?' ),
			'benefits' => array(
				'Funcții dedicate'           => 'Adăugăm exact funcționalitatea care lipsește.',
				'Date sincronizate'          => 'Transferurile repetate pot rula între platforme.',
				'Flux mai simplu'            => 'Scazi numărul de pași manuali pentru echipă.',
				'Compatibilitate verificată' => 'Analizăm versiunile și interfețele disponibile.',
			),
			'steps'    => array(
				'Inventariem sistemele' => 'Verificăm accesul și datele care trebuie transferate.',
				'Construim conectorul'  => 'Definim regulile și tratarea erorilor.',
				'Testăm integrarea'     => 'Verificăm transferurile și comportamentul la întreruperi.',
			),
		),
		'aplicatii-windows-linux' => array(
			'hero'     => array( 'Un program dedicat.', 'Lucru mai simplu.', '▣', 'Windows / Linux / Desktop' ),
			'lead'     => 'Dezvoltăm aplicații pentru calculator atunci când ai de procesat fișiere, de controlat echipamente sau de simplificat o operațiune repetitivă.',
			'headings' => array( 'Un instrument pentru modul tău de lucru', 'Aplicații pentru activitatea zilnică', 'Cum ajungem la programul potrivit', 'Ai nevoie de un program dedicat?' ),
			'benefits' => array(
				'Interfață clară'         => 'Operatorii văd acțiunile și informațiile de care au nevoie.',
				'Fluxuri specializate'    => 'Programul reflectă ordinea reală a operațiunilor.',
				'Procesare în lot'        => 'Grupezi acțiunile repetitive asupra fișierelor sau datelor.',
				'Acces la resurse locale' => 'Lucrăm cu sistemul de operare și dispozitivele disponibile.',
			),
			'steps'    => array(
				'Observăm activitatea' => 'Notăm sarcinile, stațiile și dispozitivele folosite.',
				'Proiectăm programul'  => 'Stabilim interfața și operațiunile principale.',
				'Verificăm instalarea' => 'Testăm aplicația în mediul în care va rula.',
			),
		),
		'integrari-ai'            => array(
			'hero'     => array( 'AI pentru probleme reale.', 'Mai mult timp pentru echipă.', 'AI', 'Intelligence / Automation / Insights' ),
			'lead'     => 'Integrăm căutarea, asistența sau prelucrarea documentelor în fluxurile existente, cu verificare umană acolo unde contează.',
			'headings' => array( 'Mai puține sarcini repetitive', 'Unde poate ajuta inteligența artificială', 'De la caz de utilizare la validare', 'Unde ar putea ajuta AI echipa ta?' ),
			'benefits' => array(
				'Căutare în documente'    => 'Găsești mai repede răspunsurile în sursele echipei.',
				'Asistență în aplicație'  => 'Pregătești răspunsuri și propuneri pentru verificare.',
				'Extragere de informații' => 'Identifici câmpuri din documente înainte de prelucrare.',
				'Automatizare controlată' => 'Stabilești pașii care necesită validare umană.',
			),
			'steps'    => array(
				'Alegem o sarcină concretă' => 'Definim rezultatul și datele disponibile.',
				'Testăm pe exemple'         => 'Măsurăm calitatea și stabilim limitele.',
				'Integrăm și urmărim'       => 'Validăm rezultatele cu echipa care folosește soluția.',
			),
		),
	);
}
