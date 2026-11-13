"""Crawl boundaries and HTML parsing must not create cart/session side effects."""
import importlib.util
from pathlib import Path
import unittest

spec = importlib.util.spec_from_file_location("launch_audit", Path(__file__).parents[1] / "scripts" / "audit-launch.py")
audit = importlib.util.module_from_spec(spec)
spec.loader.exec_module(audit)


class LaunchAuditTests(unittest.TestCase):
    def test_mutating_and_private_links_are_excluded(self):
        for href in ("/?add-to-cart=42", "/?remove_item=key", "/wp-admin/", "/wp-login.php", "/?wc-ajax=add_to_cart", "/?token=private", "/contul-meu/customer-logout/"):
            with self.subTest(href=href):
                self.assertIsNone(audit.normalize(href, "https://zion-stage.on-forge.com/")[0])

    def test_scope_and_credentials(self):
        for href in ("https://food.zion3d.ro/", "https://user:secret@example.com/", "mailto:public@example.com", "https://example.com:8443/"):
            self.assertIsNone(audit.normalize(href, "https://zion-stage.on-forge.com/")[0])

    def test_native_pagination_and_anchors(self):
        self.assertEqual(("https://zion-stage.on-forge.com/magazin-3d/?query-73-page=2", "secțiune"), audit.normalize("/magazin-3d/?query-73-page=2#sec%C8%9Biune", "https://zion-stage.on-forge.com/"))

    def test_malformed_migrated_image_url(self):
        self.assertEqual((None, "malformed-url-whitespace"), audit.normalize("https://example.com/a.jpg https://example.com/a.jpg", "https://zion-stage.on-forge.com/"))

    def test_html_entities_headings_and_real_links(self):
        page = audit.Page()
        page.feed('<html lang="ro-RO"><head><title>A &amp; B</title><meta property="og:image" content="/cover.png"><link href="/pagina/" rel="canonical"></head><body><main><h1 id="titlu">Un titlu</h1><p>Text</p><a href="/produs/?a=1&amp;b=2">Produs</a><img src="/photo.jpg" alt=""></main><script>secret</script></body></html>')
        self.assertEqual(["Un titlu"], page.h1)
        self.assertEqual(["/cover.png"], page.values("og:image"))
        self.assertEqual(["/pagina/"], page.canonicals)
        self.assertIn("titlu", page.ids)
        self.assertEqual("/produs/?a=1&b=2", page.links[0]["href"])
        self.assertNotIn("secret", page.text)
        self.assertEqual("", page.images[0]["alt"])

    def test_production_cannot_be_used_as_crawl_root(self):
        for site in ("https://zion3d.ro/", "https://www.zion3d.ro/", "https://food.zion3d.ro/", "http://zion-stage.on-forge.com/"):
            with self.assertRaises(ValueError):
                audit.crawl(site, "unused.json")

    def test_live_release_requires_the_explicit_exact_origin(self):
        self.assertEqual("https://zion3d.ro/", audit.crawl_origin("https://zion3d.ro", live=True)[1])
        self.assertEqual("https://zion-stage.on-forge.com/", audit.crawl_origin("https://zion-stage.on-forge.com/")[1])
        for site in ("http://zion3d.ro/", "https://www.zion3d.ro/", "https://food.zion3d.ro/", "https://zion-stage.on-forge.com/", "https://zion3d.ro.example.com/", "https://user:secret@zion3d.ro/", "https://zion3d.ro:8443/", "https://zion3d.ro/wp-admin/", "https://zion3d.ro/?action=delete", "https://zion3d.ro/#fragment"):
            with self.subTest(site=site), self.assertRaises(ValueError):
                audit.crawl_origin(site, live=True)

    def test_staging_root_rejects_credentials_ports_paths_and_queries(self):
        for suffix in (":443/", "/wp-admin/", "/?action=delete", "/#fragment"):
            with self.assertRaises(ValueError):
                audit.crawl_origin("https://zion-stage.on-forge.com" + suffix)

    def test_native_structured_data_is_validated_without_execution(self):
        page = audit.Page()
        page.feed('<script type="application/ld+json">{"@graph":[{"@type":"Product","offers":{"@type":"Offer"}}]}</script><script type="application/ld+json">{invalid}</script>')
        self.assertEqual(["Offer", "Product"], sorted(audit.schema_types(page.schemas)))
        self.assertEqual(1, page.schema_errors)


if __name__ == "__main__":
    unittest.main()
