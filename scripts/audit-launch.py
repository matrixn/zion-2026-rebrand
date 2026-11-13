#!/usr/bin/env python3
"""Bounded read-only release crawl; staging by default, live by explicit opt-in."""
import argparse
import collections
import concurrent.futures
import datetime
import hashlib
import http.client
from html.parser import HTMLParser
import ipaddress
import json
from pathlib import Path
import re
import socket
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET

SKIP_QUERY = re.compile(r"(?:add.to.cart|remove.item|wc.ajax|logout|nonce|action|preview|customize|rest_route|key|token|password)", re.I)
SKIP_PATH = re.compile(r"/(?:wp-admin|wp-login\.php|wp-json|xmlrpc\.php|wc-api)(?:/|$)|/customer-logout(?:/|$)", re.I)
EXCLUDED_HOSTS = {"food.zion3d.ro"}


def normalize(href, source):
    """Return safe HTTP URL and fragment, or an explicit exclusion reason."""
    try:
        if re.search(r"[\s\x00-\x1f]", href.strip()):
            return None, "malformed-url-whitespace"
        url = urllib.parse.urlsplit(urllib.parse.urljoin(source, href.strip()))
        if url.scheme not in ("http", "https"):
            return None, "non-http"
        if url.username or url.password or not url.hostname:
            return None, "credentials-or-host"
        if url.hostname.lower() in EXCLUDED_HOSTS:
            return None, "ZionFood outside project"
        if url.port not in (None, 80, 443) or SKIP_PATH.search(url.path):
            return None, "privileged-or-action-path"
        if any(SKIP_QUERY.search(key) for key, _ in urllib.parse.parse_qsl(url.query)):
            return None, "state-changing-or-private-query"
        return urllib.parse.urlunsplit((url.scheme, url.netloc.lower(), url.path or "/", url.query, "")), urllib.parse.unquote(url.fragment)
    except ValueError:
        return None, "invalid-url"


def public_host(host):
    try:
        return all(ipaddress.ip_address(row[4][0]).is_global for row in socket.getaddrinfo(host, 443, type=socket.SOCK_STREAM))
    except (socket.gaierror, ValueError):
        return False


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


class Page(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.links, self.images, self.meta, self.canonicals, self.ids = [], [], [], [], set()
        self.title, self.h1, self.headings, self.text = [], [], [], []
        self.current_heading, self.in_title, self.hidden = None, False, 0
        self.in_main, self.main_text = False, []
        self.lang = ""
        self.schema_buffer, self.schemas, self.schema_errors = None, [], 0

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if attrs.get("id"):
            self.ids.add(attrs["id"])
        if tag == "a":
            self.links.append({"href": attrs.get("href", ""), "text": ""})
        if tag == "img":
            self.images.append({key: attrs.get(key) for key in ("src", "alt", "width", "height")})
        if tag == "meta":
            self.meta.append(attrs)
        if tag == "link" and "canonical" in attrs.get("rel", "").lower().split():
            self.canonicals.append(attrs.get("href", ""))
        if tag == "html":
            self.lang = attrs.get("lang", "")
        if tag == "main":
            self.in_main = True
        if tag == "title":
            self.in_title = True
        if re.fullmatch(r"h[1-6]", tag):
            self.current_heading = [int(tag[1]), ""]
        if tag in ("script", "style", "noscript"):
            self.hidden += 1
        if tag == "script" and attrs.get("type", "").lower() == "application/ld+json":
            self.schema_buffer = []

    def handle_endtag(self, tag):
        if tag == "script" and self.schema_buffer is not None:
            try:
                self.schemas.append(json.loads("".join(self.schema_buffer)))
            except (json.JSONDecodeError, ValueError):
                self.schema_errors += 1
            self.schema_buffer = None
        if tag in ("script", "style", "noscript"):
            self.hidden = max(0, self.hidden - 1)
        if tag == "title":
            self.in_title = False
        if tag == "main":
            self.in_main = False
        if self.current_heading and tag == "h" + str(self.current_heading[0]):
            self.headings.append(self.current_heading)
            if self.current_heading[0] == 1:
                self.h1.append(self.current_heading[1].strip())
            self.current_heading = None

    def handle_data(self, text):
        if self.schema_buffer is not None:
            self.schema_buffer.append(text)
        if self.in_title:
            self.title.append(text)
        if self.current_heading:
            self.current_heading[1] += text
        if not self.hidden and text.strip():
            self.text.append(text.strip())
            if self.in_main:
                self.main_text.append(text.strip())

    def values(self, key):
        return [m.get("content", "") for m in self.meta if (m.get("name") or m.get("property") or "").lower() == key]


def schema_types(value):
    if isinstance(value, list):
        return [kind for item in value for kind in schema_types(item)]
    if isinstance(value, dict):
        own = value.get("@type", [])
        types = [own] if isinstance(own, str) else [kind for kind in own if isinstance(kind, str)] if isinstance(own, list) else []
        return types + [kind for item in value.values() for kind in schema_types(item)]
    return []


def request(url):
    """Validate every redirect, never send cookies, cap response size and duration."""
    opener = urllib.request.build_opener(NoRedirect)
    current, chain = url, []
    for _ in range(7):
        if not public_host(urllib.parse.urlsplit(current).hostname):
            return {"url": url, "error": "host not public/reachable", "redirects": chain}
        req = urllib.request.Request(current, headers={"User-Agent": "ZionLaunchAudit/1.1 (authorized read-only release audit)", "Accept": "text/html,application/xml,image/*;q=0.8,*/*;q=0.5"})
        try:
            response = opener.open(req, timeout=18)
        except urllib.error.HTTPError as error:
            response = error
        except (urllib.error.URLError, TimeoutError, OSError, http.client.HTTPException, ValueError) as error:
            return {"url": url, "error": str(error), "redirects": chain}
        with response:
            status = response.code
            if status in (301, 302, 303, 307, 308):
                target, reason = normalize(response.headers.get("Location", ""), current)
                chain.append({"url": current, "status": status, "location": target or reason})
                if not target:
                    return {"url": url, "status": status, "redirects": chain, "excluded_redirect": reason}
                current = target
                continue
            kind = response.headers.get("Content-Type", "")
            row = {"url": url, "final_url": current, "status": status, "content_type": kind, "redirects": chain, "robots_header": response.headers.get("X-Robots-Tag", "")}
            if "html" in kind or "xml" in kind or current.endswith("robots.txt"):
                data = response.read(4_000_001)
                row["truncated"] = len(data) > 4_000_000
                row["body"] = data[:4_000_000].decode(response.headers.get_content_charset() or "utf-8", errors="replace")
            return row
    return {"url": url, "error": "redirect limit", "redirects": chain}


def inspect(row):
    if row.get("status") != 200 or "html" not in row.get("content_type", ""):
        return None
    page = Page()
    page.feed(row.pop("body", ""))
    text = " ".join(page.main_text or page.text)
    row.update(title="".join(page.title).strip(), h1=page.h1, headings=page.headings, lang=page.lang,
               descriptions=page.values("description"), canonicals=page.canonicals,
               robots=page.values("robots"), og_images=page.values("og:image"), og_titles=page.values("og:title"),
               og_urls=page.values("og:url"), twitter_cards=page.values("twitter:card"),
               words=len(text.split()), text_sha256=hashlib.sha256(text.encode()).hexdigest(),
               schema_types=sorted(set(schema_types(page.schemas))), schema_errors=page.schema_errors,
               images_missing_alt=[img["src"] for img in page.images if img["alt"] is None],
               text_warnings=[term for term in ("Caregory", "sofrwae", "Lorem ipsum", "Hello world!", "Just another WordPress site", "[elementor", "[etheme") if term.lower() in text.lower()])
    row["issues"] = []
    if not row["title"]:
        row["issues"].append("missing-title")
    if len(row["h1"]) != 1:
        row["issues"].append("h1-count-" + str(len(row["h1"])))
    for field in ("descriptions", "canonicals", "og_images"):
        if not row[field]:
            row["issues"].append("missing-" + field)
    if len(row["descriptions"]) > 1 or len(row["canonicals"]) > 1:
        row["issues"].append("duplicate-seo-tags")
    if not row["lang"]:
        row["issues"].append("missing-lang")
    if row["images_missing_alt"]:
        row["issues"].append("images-missing-alt")
    if page.schema_errors:
        row["issues"].append("invalid-json-ld")
    return page


def crawl_origin(base, live=False):
    """Require one exact HTTPS origin; live authorization never permits another host."""
    origin = urllib.parse.urlsplit(base)
    expected = "zion3d.ro" if live else "zion-stage.on-forge.com"
    if origin.scheme != "https" or origin.netloc != expected or origin.path not in ("", "/") or origin.query or origin.fragment:
        raise ValueError(f"Only the explicitly selected HTTPS origin https://{expected}/ is accepted")
    root = f"https://{origin.hostname}/"
    return origin, root


def crawl(base, output, limit=600, seeds=(), live=False):
    origin, root = crawl_origin(base, live)
    pending = collections.deque([root, root + "robots.txt", root + "wp-sitemap.xml", root + "sitemap_index.xml", *seeds])
    known, results, pages, sources, exclusions, fragments = set(), {}, {}, collections.defaultdict(set), {}, []
    while pending and len(known) < limit:
        batch = []
        while pending and len(batch) < 4 and len(known) < limit:
            candidate, reason = normalize(pending.popleft(), root)
            if candidate and candidate not in known:
                known.add(candidate)
                batch.append(candidate)
        with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
            fetched = list(pool.map(request, batch))
        for row in fetched:
            url = row["url"]
            internal = urllib.parse.urlsplit(url).netloc == origin.netloc
            page = inspect(row) if internal else None
            results[url] = row
            if page:
                pages[url] = page
                # Query variants are checked once without expanding a faceted crawl trap.
                candidates = ([] if urllib.parse.urlsplit(url).query else [link["href"] for link in page.links]) + row["og_images"] + [i["src"] for i in page.images if i["src"]]
                for href in candidates:
                    target, fragment = normalize(href, row["final_url"])
                    if not target:
                        exclusions[href] = fragment
                        continue
                    sources[target].add(url)
                    if fragment and urllib.parse.urlsplit(target).netloc == origin.netloc:
                        fragments.append((url, target, fragment))
                    # Faceted/search links are checked but do not recursively expand their combinations.
                    if target not in known:
                        pending.append(target)
            elif internal and row.get("status") == 200 and "body" in row:
                if "xml" in row.get("content_type", ""):
                    try:
                        for item in ET.fromstring(row["body"]).iter():
                            if item.tag.split("}")[-1] == "loc" and item.text:
                                target, _ = normalize(item.text, root)
                                if target and urllib.parse.urlsplit(target).netloc == origin.netloc:
                                    pending.append(target)
                    except ET.ParseError:
                        row["xml_error"] = True
                if url.endswith("robots.txt"):
                    row["robots_txt"] = row["body"]
            row.pop("body", None)
        print(f"Checked {len(known)} URLs; queue {len(pending)}", flush=True)
        Path(output).parent.mkdir(parents=True, exist_ok=True)
        Path(output + ".partial.json").write_text(json.dumps(list(results.values()), ensure_ascii=False, indent=2), encoding="utf-8")
    duplicates = {}
    for field in ("title", "descriptions", "text_sha256"):
        groups = collections.defaultdict(list)
        for url, row in results.items():
            value = row.get(field)
            if value:
                groups[json.dumps(value, ensure_ascii=False)].append(url)
        duplicates[field] = [urls for urls in groups.values() if len(urls) > 1]
    broken_fragments = [{"source": source, "target": target, "fragment": fragment} for source, target, fragment in fragments if target in pages and fragment not in pages[target].ids]
    for url, row in results.items():
        row["sources"] = sorted(sources[url])
    report = {"generated_utc": datetime.datetime.now(datetime.timezone.utc).isoformat(), "site": root,
              "complete": not pending, "limit": limit, "checked": len(results), "html_pages": len(pages),
              "results": list(results.values()), "duplicates": duplicates,
              "broken_fragments": broken_fragments, "excluded": exclusions,
              "scope": "Anonymous GET only; external destinations checked once, never expanded; ZionFood excluded; no action URLs, forms, purchases or authenticated pages."}
    Path(output).parent.mkdir(parents=True, exist_ok=True)
    Path(output).write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps({"output": output, "complete": report["complete"], "checked": len(results), "pages": len(pages), "errors": sum(bool(r.get("error")) or r.get("status", 0) >= 400 for r in results.values()), "broken_fragments": len(broken_fragments)}))


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base")
    parser.add_argument("output")
    parser.add_argument("--limit", type=int, default=600)
    parser.add_argument("--seeds", help="Private JSON array of additional public URLs on the selected origin")
    parser.add_argument("--live", action="store_true", help="Explicitly authorize only https://zion3d.ro/ for a read-only release audit")
    args = parser.parse_args()
    crawl(args.base, args.output, min(max(args.limit, 1), 5000), json.loads(Path(args.seeds).read_text(encoding="utf-8")) if args.seeds else (), live=args.live)
