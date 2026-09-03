# What is new vs. the pre-existing site

## Pre-existing before the WebMCP Challenge

- FermaTeh e-commerce storefront.
- OpenCart/ocStore product catalog.
- Product descriptions, categories, prices, inventory and images.
- Existing site search, cart and normal human shopping UI.

## Added during the WebMCP Challenge

- Browser-native WebMCP support through `document.modelContext.registerTool()`.
- `search_products` for authoritative constrained product discovery.
- `get_product` for detailed structured product data on one selected item.
- Optional `add_to_cart` write tool for the active OpenCart browser session.
- Live OpenCart price, tax, currency and stock hydration.
- Explicit trust-contract signals such as `authoritative`, `result_is_final`, `live_price`, `live_stock`, `browser_verification_required=false` and `navigation_required=false`.
- Shared human + AI storefront presentation showing the same products returned to the agent.
- Separate compact agent data and visual human presentation.
- Visible WebMCP cart confirmation and immediate storefront cart synchronization.
- Cart-only action receipt explicitly confirming no order placement and no payment.
- Page-bound multilingual locale resolution.
- Same-origin request enforcement, payload limits and session rate limiting.
- Required-product-option protection for the write action.
- Tool descriptions tuned to reduce redundant DOM navigation and product-page verification.
- Competition documentation, installable open-source edition and reproducible testing instructions.

## Why publish a competition edition

The production AI Ready PRO module contains unrelated commercial features such as AI discovery files, diagnostics and additional integrations. They are not necessary to understand or reproduce the WebMCP experience.

This repository isolates the WebMCP storefront layer so judges and developers can inspect the relevant code directly and install it independently on an OpenCart 3.x / ocStore 3.x store.
