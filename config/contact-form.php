<?php
/**
 * Native CF7 authoring preset. No recipient or transport credentials.
 *
 * @package Zion_Theme
 */

return array(
	'form'     => '<div class="zion-contact-fields">
<div class="zion-contact-field"><label>Numele tău * [text* your-name autocomplete:name maxlength:120]</label></div>
<div class="zion-contact-field"><label>Adresa de e-mail * [email* your-email autocomplete:email maxlength:254]</label></div>
<div class="zion-contact-field"><label>Telefon <span class="zion-contact-optional">(opțional)</span> [tel your-phone autocomplete:tel maxlength:30]</label></div>
<div class="zion-contact-field"><label>Serviciul de interes * [select* your-service first_as_label "Alege un serviciu" {{services}} "Alt proiect / Încă nu știu"]</label></div>
<div class="zion-contact-field zion-contact-message"><label>Pe scurt, despre proiect * [textarea* your-message 40x5 minlength:10 maxlength:4000]</label></div>
</div>
<p class="zion-contact-privacy">Folosim informațiile din mesaj pentru a discuta solicitarea ta. <a href="{{privacy}}">Informarea despre confidențialitate ↗</a></p>
<div class="zion-contact-submit">[submit "Trimite mesajul ↗"]</div>',
	'mail'     => array(
		'subject'            => 'Zion 3D / [your-service]',
		'additional_headers' => 'Reply-To: [your-email]',
		'body'               => "Mesaj din pagina Contact Zion\n\nNume: [your-name]\nE-mail: [your-email]\nTelefon: [your-phone]\nServiciu: [your-service]\n\nMesaj:\n[your-message]\n\nSursă: [_url]",
		'attachments'        => '',
		'use_html'           => false,
		'exclude_blank'      => true,
	),
	'messages' => array(
		'mail_sent_ok'      => 'Mesajul a fost trimis. Îți mulțumim! Continuăm conversația folosind datele de contact lăsate de tine.',
		'mail_sent_ng'      => 'Mesajul nu a putut fi trimis. Încearcă din nou sau scrie-ne direct la contact@zion3d.ro.',
		'validation_error'  => 'Verifică informațiile din câmpurile marcate și încearcă din nou.',
		'spam'              => 'Mesajul nu a putut fi procesat. Poți folosi adresa de e-mail sau telefonul pentru a ne contacta.',
		'accept_terms'      => 'Verifică această opțiune înainte de trimitere.',
		'invalid_required'  => 'Completează acest câmp.',
		'invalid_too_long'  => 'Textul este prea lung pentru acest câmp.',
		'invalid_too_short' => 'Adaugă câteva detalii în plus.',
		'invalid_email'     => 'Introdu o adresă de e-mail validă.',
		'invalid_tel'       => 'Introdu un număr de telefon valid.',
	),
);
