# Zion Theme

- Keep presentation in `themes/zion-theme` and store classification or other business rules in `plugins/zion-core`. WooCommerce owns product data, stock, orders, cart and checkout.
- Use native WordPress blocks, patterns and the Site Editor. Write new code for this repository. Treat the existing Zion site and discussion as visual references only.
- Use `theme.json` as the source of visual presets. Scope component CSS to Zion classes, retain visible keyboard focus and honor reduced motion.
- Keep authoring dependencies development-only. Edit `assets/src/zion.css`, run `npm run build` and commit the generated stylesheet alongside its source.
- Use WordPress Coding Standards, capability checks for privileged operations, context-appropriate escaping and native APIs for persistence.
- Keep dependencies pinned through lockfiles and the root wp-env configuration. Use synthetic data in local/test instances. Keep production data and credentials outside the repository.
- Run the build, WPCS, PHPUnit, integration checks and Playwright before committing changes to the storefront. Use the tests instance for automated browser checks.
