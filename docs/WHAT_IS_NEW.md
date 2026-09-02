# What is new vs. the pre-existing site

## Pre-existing before the WebMCP Challenge

- FermaTeh e-commerce storefront.
- OpenCart/ocStore product catalog.
- Product descriptions, categories, prices, inventory and images.
- Existing site search and normal human shopping UI.

## Added during the WebMCP Challenge

- Browser-native WebMCP support through `document.modelContext.registerTool()`.
- A dedicated `search_products` tool for constrained product discovery.
- A dedicated `get_product` tool for structured product details.
- Live OpenCart price, tax, currency and stock hydration for tool results.
- Product URL and image delivery in structured responses.
- Human-visible result cards shown after real agent tool calls.
- Page-bound multilingual locale resolution.
- Server-side result caps and input validation.
- Same-origin request enforcement and read-only endpoint security.
- Tool descriptions tuned so agents can avoid redundant DOM search and repeated product-page verification.
- Competition documentation, installable open-source edition and reproducible testing instructions.

## Why publish a competition edition

The production AI Ready PRO module contains unrelated commercial features such as AI discovery files, diagnostics and additional integrations. They are not necessary to understand or reproduce the WebMCP experience.

This repository isolates the WebMCP catalog layer so judges and developers can inspect the relevant code directly and install it independently on an OpenCart 3.x store.
