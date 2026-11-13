#!/usr/bin/env python3
"""Render the complete crawl into an offline searchable HTML report, without dependencies."""
import argparse
import html
import json
from pathlib import Path
import urllib.parse


def render(report, destination):
    esc = lambda value: html.escape(str(value), quote=True)
    root = urllib.parse.urlsplit(report["site"]).netloc
    pages = [row for row in report["results"] if "title" in row]
    errors = [row for row in report["results"] if row.get("error") or row.get("status", 0) >= 400]
    rows = []
    for row in report["results"]:
        status = row.get("status", "ERR")
        internal = urllib.parse.urlsplit(row["url"]).netloc == root
        if status in (401, 403, 429):
            note = "Acces restricționat pentru crawler; necesită verificare în browser"
        elif row.get("error"):
            note = row["error"]
        elif isinstance(status, int) and status >= 400:
            note = "Răspuns HTTP de eroare"
            if not row.get("sources"):
                note += "; probă de infrastructură, nu link găsit într-o pagină"
        elif row.get("redirects"):
            note = " → ".join(str(item["status"]) for item in row["redirects"]) + " → " + str(status)
        else:
            note = "OK"
        robots = ", ".join(row.get("robots", [])) or row.get("robots_header", "")
        issues = ", ".join(row.get("issues", []))
        source_links = "<br>".join(f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{esc(url)}</a>' for url in row.get("sources", []))
        extra = f'<details><summary>{len(row.get("sources", []))} pagini sursă</summary>{source_links}</details>' if row.get("sources") else ""
        rows.append(f'<tr><td>{esc(status)}</td><td>{"Intern" if internal else "Extern"}</td><td><a href="{esc(row["url"])}" target="_blank" rel="noopener noreferrer">{esc(row["url"])}</a><small>{esc(row.get("title", ""))}</small>{extra}</td><td>{esc(note)}<small>{esc(issues)}</small><small>{esc(robots)}</small></td><td>{esc(" | ".join(row.get("canonicals", [])))}</td><td>{esc(" | ".join(row.get("og_images", [])))}</td></tr>')
    duplicate_rows = []
    for kind, groups in report["duplicates"].items():
        for group in groups:
            duplicate_rows.append(f'<li><strong>{esc(kind)}</strong><ul>' + "".join(f'<li>{esc(url)}</li>' for url in group) + '</ul></li>')
    exclusions = "".join(f'<li><code>{esc(url)}</code> — {esc(reason)}</li>' for url, reason in report["excluded"].items())
    output = '''<!doctype html><html lang="ro"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Audit de lansare Zion 3D</title><style>
body{font:16px/1.6 system-ui,sans-serif;background:#0b110d;color:#f3f6ef;margin:0;padding:clamp(16px,4vw,48px)}h1,h2{line-height:1.2}a{color:#d9fb4f;overflow-wrap:anywhere}small{display:block;color:#b4c3b3}input{font:inherit;width:min(600px,90%);padding:12px;border:1px solid #6c7d68;border-radius:8px;background:#141f16;color:#fff}input:focus{outline:3px solid #d9fb4f}table{border-collapse:collapse;width:100%;font-size:13px}th,td{text-align:left;padding:12px;vertical-align:top;border-bottom:1px solid #364432}th{background:#141f16;position:sticky;top:0}td:nth-child(3){min-width:260px}td:nth-child(5),td:nth-child(6){max-width:250px;overflow-wrap:anywhere}.scroll{overflow:auto}.stats{padding:20px;background:#141f16;border-left:3px solid #d9fb4f;border-radius:8px}.note{max-width:1000px;color:#b4c3b3}details{margin:10px 0}summary{cursor:pointer}code{overflow-wrap:anywhere}li{margin-bottom:6px}
</style><h1>Audit de lansare · Zion 3D</h1>'''
    output += f'<p>{esc(report["site"])} · {esc(report["generated_utc"])}</p><div class="stats"><strong>{report["checked"]} URL-uri · {len(pages)} pagini HTML · {len(errors)} răspunsuri de eroare/restricție</strong><br>Crawl complet: {"DA" if report["complete"] else "NU — limita a fost atinsă"}</div>'
    output += '<p class="note">Audit anonim, fără formulare, cumpărări sau URL-uri care modifică starea. Linkurile externe sunt verificate o singură dată și nu sunt parcurse recursiv. ZionFood este exclus. Stagingul rămâne intenționat noindex; sitemapul nativ este dezactivat cât timp indexarea este oprită. Răspunsurile 403/429 pot indica protecție anti-bot. Canonical-urile comune și variantele cu parametri nu sunt automat defecte: se interpretează împreună cu noindex și destinația canonical. Endpointul Cloudflare email-protection este un mecanism al proxy-ului și necesită verificarea linkului decodat în browser.</p>'
    output += '<label for="search">Caută URL, titlu, status sau problemă</label><p><input id="search" type="search" placeholder="Ex.: 404, og_images, magazin, noindex"></p><div class="scroll"><table><thead><tr><th>Status</th><th>Tip</th><th>URL și surse</th><th>Rezultat / SEO</th><th>Canonical</th><th>Imagine share</th></tr></thead><tbody>' + "".join(rows) + '</tbody></table></div>'
    output += '<details><summary>Discrepanțe și conținut duplicat de interpretat</summary><ul>' + "".join(duplicate_rows) + '</ul><pre>' + esc(json.dumps(report["broken_fragments"], ensure_ascii=False, indent=2)) + '</pre></details>'
    output += '<details><summary>Linkuri excluse intenționat și URL-uri malformate</summary><ul>' + exclusions + '</ul></details>'
    output += '<script>document.querySelector("#search").addEventListener("input",e=>{const q=e.target.value.toLocaleLowerCase("ro");for(const row of document.querySelectorAll("tbody tr"))row.hidden=!row.textContent.toLocaleLowerCase("ro").includes(q)});</script></html>'
    Path(destination).write_text(output, encoding="utf-8")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("input")
    parser.add_argument("output")
    args = parser.parse_args()
    render(json.loads(Path(args.input).read_text(encoding="utf-8")), args.output)
