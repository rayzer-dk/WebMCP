# Changelog

## 1.1.1 — 2026-09-03

- Preset the `add_to_cart` WebMCP action enabled for fresh competition-edition installs.
- Kept the whole module disabled after installation, so no WebMCP tool is active until the administrator explicitly enables the module.
- Once the module is enabled, all three competition tools are immediately available by default: Search Products, Get Product and Add To Cart.
- The cart action can still be disabled independently to expose only the two read-only tools.
- Updated version, cache key, documentation and reproducible build metadata.

## 1.1.0 — 2026-09-03

- Added optional browser-native `add_to_cart` WebMCP write tool.
- Added explicit cart-only action receipt: no order placement and no payment.
- Added immediate storefront cart synchronization after a successful WebMCP cart action.
- Added shared human + AI results presentation with live-data trust badges and cart confirmation.
- Added trust-contract result semantics to discourage redundant DOM/product-page verification.
- Kept the write action disabled by default and rejected products with required options.
- Updated judge prompt, Devpost copy and demo video link.

## 1.0.0 — 2026-09-02

- Initial open-source WebMCP Challenge competition edition.
- Added browser-native `search_products` and `get_product` tools.
- Added live OpenCart price, currency and stock hydration.
- Added multilingual storefront-locale binding.
- Added visible human-agent product result panel.
- Added same-origin JSON endpoints, payload limits and session rate limiting.
- Added OpenCart 3.x/ocStore 3.x OCMOD installer and admin settings.
