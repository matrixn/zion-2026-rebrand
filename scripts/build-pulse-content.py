"""Rebuild the editable native WordPress composition; no external dependencies."""
from html import escape
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

def block(name, html='', attrs=None):
    suffix = ' ' + json.dumps(attrs, ensure_ascii=False, separators=(',', ':')) if attrs else ''
    return f'<!-- wp:{name}{suffix} -->\n{html}\n<!-- /wp:{name} -->\n'

def text(value, level=0, cls=''):
    attrs = {'level': level} if level and level != 2 else {}
    if cls:
        attrs['className'] = cls
    tag = f'h{level}' if level else 'p'
    classes = ('wp-block-heading ' if level else '') + cls
    return block('heading' if level else 'paragraph', f'<{tag}' + (f' class="{classes.strip()}"' if classes else '') + f'>{escape(value)}</{tag}>', attrs)

def group(cls, content, anchor=''):
    attrs = {'className': cls, 'layout': {'type': 'default'}}
    if anchor:
        attrs['anchor'] = anchor
    return block('group', '<div' + (f' id="{anchor}"' if anchor else '') + f' class="wp-block-group {cls}">{content}</div>', attrs)

def shortcode(kind):
    return block('shortcode', f'[zion_pulse type="{kind}"]')

def buttons(links):
    content = ''
    for index, (label, href) in enumerate(links):
        cls = ' is-style-outline' if index else ''
        content += block('button', f'<div class="wp-block-button{cls}"><a class="wp-block-button__link wp-element-button" href="{escape(href, quote=True)}">{escape(label)}</a></div>', {'className': 'is-style-outline'} if index else None)
    return block('buttons', '<div class="wp-block-buttons">' + content + '</div>')

cards = [
    ('Escape Rooms & Puzzle Automation', 'Construim controllere pentru puzzle-uri interactive, senzori ascunși, mecanisme de deblocare scenografice, secvențe de butoane, iluminare și efecte coordonate. Sistemele pot fi proiectate modular, pentru a adapta electronica la scenarii noi fără reconstruirea completă a controllerului.', ['Puzzle-uri electronice', 'Comenzi Game Master', 'Reset și Forced Solve', 'Senzori NFC/RFID', 'LED-uri și efecte', 'Controllere reutilizabile']),
    ('Smart Control & IoT', 'Transformăm echipamentele și obiectele fizice în sisteme conectate, cu monitorizare, comenzi la distanță și logică personalizată.', ['ESP32 și Arduino', 'Wi-Fi', 'Senzori de stare', 'Monitorizare', 'Comenzi automate', 'Interfețe web']),
    ('Instalații Interactive', 'Creăm experiențe care reacționează la atingere, apropiere, mișcare sau interacțiunea utilizatorului.', ['Expoziții', 'Muzee', 'Evenimente', 'Spații tematice', 'Instalații educaționale', 'Experiențe demonstrative']),
    ('Automatizări pentru Ateliere', 'Proiectăm mici sisteme electronice care simplifică operațiunile repetitive și oferă mai mult control asupra proceselor.', ['Contorizare', 'Semnalizare', 'Detectare', 'Control secvențial', 'Monitorizare', 'Automatizări personalizate']),
    ('Electronică & Prototipare', 'Dezvoltăm prototipuri electronice personalizate, de la concept și schemă până la firmware, cablare, teste și integrare fizică.', ['ESP32 / Arduino', 'Module MOSFET', 'Senzori', 'NFC/RFID', 'Alimentări DC', 'PCB-uri și interconectare']),
    ('Hardware + Software + 3D Printing', 'Proiectăm nu doar logica, ci și forma fizică a sistemului: carcase, suporturi, panouri, montaj și integrarea componentelor.', ['Carcase printate 3D', 'Montaj modular', 'Panouri de comandă', 'Dashboard-uri', 'Firmware personalizat', 'Integrare hardware/software']),
]
faq = [
    ('Pot automatiza un echipament pe care îl am deja?', 'În multe cazuri, da. Analizăm echipamentul, conexiunile disponibile și condițiile de utilizare înainte să propunem o soluție.'),
    ('Este obligatorie conexiunea la internet?', 'Nu. Multe sisteme pot funcționa local, independent de internet. Conectivitatea poate fi folosită pentru monitorizare sau control suplimentar.'),
    ('Pot schimba funcționalitatea ulterior?', 'În funcție de arhitectura aleasă, firmware-ul poate fi modificat, iar sistemul poate fi extins cu senzori, module sau funcții noi.'),
    ('Construiți și carcasa sistemului?', 'Da. Putem proiecta și realiza carcase, suporturi și panouri personalizate prin printare 3D, potrivite componentelor selectate.'),
    ('Cât costă o automatizare?', 'Costul depinde de complexitatea logicii, numărul componentelor, integrarea fizică și nivelul de testare. Putem propune implementări etapizate, pornind de la funcțiile esențiale.'),
    ('Lucrați exclusiv cu escape room-uri?', 'Nu. Dezvoltăm soluții personalizate și pentru instalații interactive, ateliere, spații comerciale și alte aplicații unde electronica și software-ul pot rezolva o problemă concretă.'),
    ('Puteți automatiza uși și sisteme electrice?', 'Putem proiecta interfețe și sisteme de comandă pentru aplicații compatibile, însă lucrările la instalații electrice de putere, sisteme de siguranță și căi de evacuare necesită evaluarea și intervenția specialiștilor autorizați.'),
]
heading = block('heading', '<h1 class="wp-block-heading">Lumea reală.<br><em>Controlată inteligent.</em></h1>', {'level': 1})
hero = group('zion-pulse-section zion-pulse-hero', group('zion-pulse-lead',
    text('ZION PULSE / INTERACTIVE ENGINEERING', cls='zion-pulse-eyebrow') + heading +
    text('Automatizări inteligente & sisteme interactive', cls='zion-pulse-subtitle') +
    text('De la un singur senzor la un sistem interactiv complet. Construim automatizări personalizate care conectează electronica, software-ul și obiectele fizice într-o singură experiență.') +
    buttons([('Hai să automatizăm ideea ta ↗', '/contact/'), ('Descoperă ce putem construi ↓', '#ce-construim')]) +
    text('Dăm inteligență lucrurilor din jurul tău.', cls='zion-pulse-tagline')) + shortcode('hero'))
intro = group('zion-pulse-section zion-pulse-manifesto', text('De la idee la interacțiune.', 2) + group('zion-pulse-intro-copy',
    text('Proiectăm și dezvoltăm automatizări electronice, sisteme interactive și soluții IoT personalizate. Combinăm hardware, software și fabricație 3D pentru a transforma ideile în mecanisme inteligente, funcționale și adaptabile.') +
    text('Automatizări ESP32 și Arduino, proiectate în Râmnicu Vâlcea. Alegem împreună componentele, firmware-ul și integrarea fizică potrivite proiectului, de la primul prototip la testare, documentare și configurare.')))
card_content = ''
for index, (title, description, tags) in enumerate(cards, 1):
    tag_blocks = ''.join(block('list-item', '<li>' + escape(tag) + '</li>') for tag in tags)
    card_content += group('zion-pulse-card', text(f'{index:02d} / CAPABILITĂȚI', cls='zion-pulse-eyebrow') + text(title, 3) + text(description) + block('list', '<ul class="wp-block-list">' + tag_blocks + '</ul>'))
capabilities = group('zion-pulse-section', text('01 / CE PUTEM CONSTRUI', cls='zion-pulse-eyebrow') + text('De la un simplu impuls la un sistem complet.', 2) + text('Fiecare proiect începe cu o problemă, o idee sau o experiență pe care vrei să o creezi. Noi proiectăm electronica și logica necesare pentru ca lucrurile să funcționeze împreună.') + group('zion-pulse-capabilities', card_content), 'ce-construim')
demo = group('zion-pulse-section', text('02 / ÎNCEARCĂ ZION PULSE', cls='zion-pulse-eyebrow') + text('Nu doar îți spunem cum funcționează. Îți arătăm.', 2) + text('Cinci intrări. O secvență. Un rezultat. Încearcă un puzzle electronic simulat și urmărește cum reacționează sistemul la fiecare comandă.') + shortcode('demo'), 'laborator-pulse')
modules = group('zion-pulse-section', text('03 / SISTEME MODULARE', cls='zion-pulse-eyebrow') + text('O platformă. Nenumărate posibilități.', 2) + text('Nu fiecare proiect trebuie reconstruit de la zero. Acolo unde este potrivit, dezvoltăm arhitecturi modulare care permit reutilizarea controllerelor, schimbarea senzorilor și adaptarea logicii la cerințe noi.') + shortcode('modules') + group('zion-pulse-benefits', ''.join(text(v) for v in ['Reutilizabil.', 'Configurabil.', 'Extensibil.', 'Mai ușor de întreținut.'])) + text('Adaptarea depinde de hardware și de cerințele proiectului. Compatibilitatea se evaluează înainte de integrare.'), 'sisteme-modulare')
technologies = ['ESP32', 'Arduino', 'Embedded C/C++', 'Wi-Fi', 'NFC / RFID', 'Senzori digitali și analogici', 'MOSFET / PWM', 'LED Control', 'REST API', 'Web Dashboards', 'Integrare IoT', '3D Printing / CAD']
tech = group('zion-pulse-section', text('04 / TEHNOLOGII', cls='zion-pulse-eyebrow') + text('Electronica întâlnește software-ul.', 2) + text('Instrumentele sunt alese pentru ce trebuie să facă sistemul. Fără complexitate inutilă, cu atenție la configurare, operare și întreținere.') + group('zion-pulse-tech-grid', ''.join(block('paragraph', f'<p><span aria-hidden="true">{i:02d} /</span>{escape(value)}</p>') for i, value in enumerate(technologies, 1))))
steps = [('Înțelegem.', 'Ne explici ce trebuie să se întâmple și în ce condiții.'), ('Proiectăm.', 'Alegem arhitectura hardware și software potrivită, ținând cont de costuri, întreținere și posibilitatea de extindere.'), ('Prototipăm.', 'Realizăm schema, firmware-ul și un prototip pentru testarea logicii.'), ('Integrăm.', 'Conectăm componentele, organizăm montajul și verificăm funcționalitatea.'), ('Evoluăm.', 'La nevoie, adaptăm și dezvoltăm sistemul odată cu proiectul.')]
process = group('zion-pulse-section', text('05 / CUM LUCRĂM', cls='zion-pulse-eyebrow') + text('De la idee la primul impuls.', 2) + group('zion-pulse-process', ''.join(group('zion-pulse-step', text(f'{i:02d}', cls='zion-pulse-eyebrow') + text(title, 3) + text(description)) for i, (title, description) in enumerate(steps, 1))))
questions = group('zion-pulse-section zion-pulse-faq', text('06 / ÎNTREBĂRI FRECVENTE', cls='zion-pulse-eyebrow') + text('Răspunsuri înainte de primul pas.', 2) + ''.join(block('details', '<details class="wp-block-details"><summary>' + escape(question) + '</summary>' + text(answer) + '</details>') for question, answer in faq))
whatsapp = 'https://wa.me/40770675597?text=Buna%21%20Sunt%20interesat%20de%20serviciul%20Zion%20Pulse%20si%20as%20vrea%20sa%20discutam%20despre%20o%20automatizare.'
final = group('zion-pulse-section zion-pulse-final', text('URMĂTORUL IMPULS ÎȚI APARȚINE', cls='zion-pulse-eyebrow') + text('Ai o idee care ar trebui să prindă viață?', 2) + text('Poate este un puzzle care trebuie să reacționeze la o combinație secretă. Poate este un senzor care trebuie să declanșeze o acțiune. Sau poate ai un proces pe care vrei să îl automatizezi.') + text('Povestește-ne ce ai în minte. Găsim împreună soluția potrivită.') + buttons([('Discută cu noi despre proiectul tău ↗', '/contact/'), ('Contact direct pe WhatsApp ↗', whatsapp)]))
destination = ROOT / 'themes/zion-theme/assets/pulse/page.html'
destination.parent.mkdir(parents=True, exist_ok=True)
destination.write_text(group('zion-pulse', hero + intro + capabilities + demo + modules + tech + process + questions + final), encoding='utf-8')
print(destination)
