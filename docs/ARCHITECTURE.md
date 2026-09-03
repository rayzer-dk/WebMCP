# Architecture

## Request path

1. OpenCart renders the storefront header.
2. When the module is enabled, OCMOD injects `webmcp_catalog.js` and same-origin endpoint URLs.
3. The script waits for a browser WebMCP context.
4. It registers `search_products`, `get_product` and, when cart action is enabled, `add_to_cart` with `document.modelContext.registerTool()`.
5. An agent invokes a tool.
6. The JavaScript `execute` callback POSTs JSON to the OpenCart controller.
7. The controller validates method, content type, origin, payload and rate limit.
8. The backend resolves the active storefront language.
9. Search candidates are ranked and final candidates are hydrated from OpenCart for current price, currency, stock, URL, image and attributes.
10. Structured results return to the agent while the storefront can render the same result to the human.
11. Add To Cart, when explicitly requested by the user, changes only the current cart and then refreshes the visible cart UI.

## Tool surface

### search_products

Inputs: `query`, `limit`, `min_price`, `max_price`, `in_stock`.

Outputs include product ID, name, model/SKU, current price, currency, stock, product URL, image URL, category path and key attributes, plus final-result trust signals.

### get_product

Input: `product_id`.

Returns a larger structured payload for one already-selected product.

### add_to_cart

Inputs: `product_id`, optional `quantity`.

Changes the active OpenCart cart only. Products with required options are rejected. No checkout, order creation or payment operation is exposed.

The competition preset enables this cart action, but the entire module still installs disabled. Therefore no WebMCP capability is active until an administrator enables the module.

## Human + agent presentation

The model receives compact structured data while the page renders a Shared human + AI results panel with the same live products. Cart actions produce a human-visible confirmation and an agent-facing receipt from the same committed operation.

## Multilingual behavior

The browser script sends the locale of the currently open storefront page. The backend resolves that locale against enabled OpenCart languages, keeping the agent result aligned with what the shopper sees.

## Live commerce hydration

Text search identifies candidate product IDs. Final price, tax calculation, currency conversion, quantity, image and URL are built at call time from OpenCart, making `live_price` and `live_stock` meaningful.

## Production vs. competition edition

The live FermaTeh deployment uses the production AI Ready PRO catalog index and additional diagnostics. This public competition edition removes unrelated commercial features while preserving the core architecture: browser-native tools, server-side catalog resolution, live commerce hydration, shared human-agent presentation and bounded cart mutation.
