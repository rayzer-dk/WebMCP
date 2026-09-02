# Architecture

## Request path

1. OpenCart renders the storefront header.
2. When the module is enabled, OCMOD injects `webmcp_catalog.js` and same-origin endpoint URLs.
3. The script waits for a browser WebMCP context.
4. It registers `search_products` and `get_product` with `document.modelContext.registerTool()`.
5. An agent invokes a tool.
6. The JavaScript `execute` callback POSTs JSON to the OpenCart controller.
7. The controller validates request method, content type, origin, size and rate limit.
8. The model resolves the active storefront language.
9. Search candidates are ranked from product/category text, model and SKU.
10. Final candidates are hydrated from OpenCart for current price, currency, stock, URL, image and attributes.
11. The structured response returns to the agent.
12. If visible results are enabled, the page renders the same returned products in a compact panel.

## Why the tool boundary is here

The browser exposes intent and constraints. OpenCart remains authoritative for catalog truth.

This avoids duplicating pricing, stock, language or SEO logic in JavaScript and avoids DOM scraping. It also means a catalog update becomes visible to the agent without rebuilding a separate static product database.

## Tool surface

### search_products

Inputs: `query`, `limit`, `min_price`, `max_price`, `in_stock`.

Outputs include product ID, name, model/SKU, current price, current currency, quantity/stock state, product URL, image URL, category path and key attributes.

### get_product

Input: `product_id`.

Returns one structured product with a larger attribute/description payload.

## Multilingual behavior

The browser script sends the locale of the currently open storefront page. The backend resolves that locale only against enabled OpenCart language records. Product descriptions and attributes are selected using the resolved language ID.

The agent does not get a free-form language selector in the tool schema, which prevents accidental cross-language results.

## Live commerce hydration

Text search identifies candidate product IDs. Final price, tax calculation, currency conversion, quantity, image and URL are built at call time from OpenCart. This is what makes `live_price` and `live_stock` meaningful.

## Production vs. competition edition

The live FermaTeh deployment uses the production AI Ready PRO catalog index for larger-catalog search efficiency and additional diagnostics. This public competition edition deliberately removes unrelated commercial features and keeps the WebMCP interaction layer small and readable while preserving the same core architecture: browser-native tools, server-side catalog resolution, live commerce hydration and visible human-agent presentation.
