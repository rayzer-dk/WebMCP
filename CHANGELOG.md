# Changelog

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
