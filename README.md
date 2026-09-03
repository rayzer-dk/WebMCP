# WebMCP Catalog Bridge for OpenCart

Open-source competition edition of the WebMCP storefront layer demonstrated on FermaTeh.

Live demo: https://fermateh.com.ua/en/

Demo video: https://youtu.be/etBFEEBXYAA

Public submission concept: a shared human + AI storefront where agents search, compare and act on live commerce data through WebMCP.

## Suggested judge prompt

> Open https://fermateh.com.ua/en/ in the built-in browser and use the website's WebMCP tools. Find 3 animal repellents currently in stock between 5,000 and 15,000 UAH. Compare them, recommend the best option for a farm, field or garden, use Get Product for the recommendation, then add 1 unit of the recommended simple product to the cart. Do not place an order or make a payment.

## WebMCP implementation

The repository contains the browser-native WebMCP registration pattern required by the challenge:

```js
document.modelContext.registerTool({
  name: "search_products",
  description: "Search the product catalog",
  inputSchema: { /* ... */ },
  execute: async (input) => { /* ... */ }
});
```

The complete implementation is in `opencart-module/upload/catalog/view/javascript/webmcp_catalog.js`.

The competition edition provides three tools when the module is enabled:

- `search_products` — authoritative product discovery with live OpenCart price, stock, URLs, images and attributes.
- `get_product` — detailed structured information for one selected product.
- `add_to_cart` — bounded write tool that adds one selected simple product to the current OpenCart cart. It never places an order and never performs payment.

For fresh competition-edition installs, Add To Cart is preset enabled so judges immediately see all three tools after the administrator enables the module. The module itself still installs disabled, so nothing runs until it is explicitly enabled. The cart action can also be disabled independently.

## Why WebMCP

Online stores already know their catalog, price, stock, product identity and cart state. A general browser agent normally has to reconstruct that information from the visible UI by navigating search pages, opening product pages and parsing DOM content.

WebMCP exposes those domain operations directly. The agent asks the store for authoritative data instead of imitating a human browser session. On the live FermaTeh demo, server-side product search typically completes in tens of milliseconds and current price and stock are read from OpenCart at call time.

## Shared human + AI storefront

After a real WebMCP search, the storefront renders a visible `Shared human + AI results` panel. The shopper sees the same products the agent received, including real images, current price, current stock and links.

Search results include an explicit trust contract with signals such as `authoritative: true`, `live_price: true`, `live_stock: true`, `result_is_final: true`, `browser_verification_required: false` and `navigation_required: false`. This tells the agent that the store is the source of truth and avoids unnecessary product-page re-checking.

## Read + write collaboration

The human asks for products in natural language. The agent calls Search Products. OpenCart returns live authoritative matches. The shopper sees those same products on the page. The agent uses Get Product only for the selected recommendation. If the user explicitly asks to add it, Add To Cart changes the active OpenCart cart and refreshes the visible cart UI. The returned receipt explicitly states that no order and no payment were submitted.

This is a real state-changing WebMCP workflow on a live commerce session, not a simulated chat response.

## Human presentation vs agent payload

The agent receives compact structured commerce data while the shopper receives product cards and cart confirmation on the storefront. Both are derived from the same underlying tool result, keeping model payloads focused while preserving a clear human-visible interaction.

## Language handling

The WebMCP layer follows the language of the currently open storefront page. Product names and descriptions therefore stay aligned with what the human is viewing.

## Security boundary

- The module installs disabled.
- Search Products and Get Product are read-only.
- Add To Cart is limited to the current cart and requires an explicit user request before the agent should call it.
- Products with required options are rejected for shopper completion.
- Checkout, order placement and payment are not implemented.
- Endpoints are POST-only and JSON-only, same-origin/same-site checks are enforced, request sizes are bounded and a per-session rate limit protects public calls.

See `docs/SECURITY.md` for details.

## Install on OpenCart 3.x / ocStore 3.x

1. Back up the site files and database.
2. Use `dist/WebMCP_Catalog_Challenge_v1.1.1.ocmod.zip` or build it from `opencart-module/`.
3. Upload the archive in Extensions > Installer.
4. Refresh Extensions > Modifications.
5. Install WebMCP Catalog Bridge under Extensions > Modules.
6. Open its settings, enable the module and save.
7. Reload the storefront in ChatGPT's in-app browser or another WebMCP-enabled browser.
8. Verify Site tools shows Search Products, Get Product and Add To Cart.

To expose only read tools, disable WebMCP Add To Cart in module settings and reload the storefront.

## Build

```bash
cd opencart-module
zip -r ../dist/WebMCP_Catalog_Challenge_v1.1.1.ocmod.zip install.xml upload
```

## What was built during the challenge

FermaTeh and its OpenCart catalog existed before the challenge. The challenge work is the WebMCP interaction layer: browser-native tool registration, authoritative product discovery, live price/stock hydration, multilingual page binding, trust-contract semantics, shared human + AI presentation, compact agent payloads, Add To Cart, live cart synchronization, cart-only action receipts and public endpoint protections.

## Repository layout

```text
opencart-module/       installable OpenCart competition edition
docs/                  architecture, security, testing and demo notes
dist/                  reproducible build artifacts and checksums
DEVPOST_SUBMISSION.md  submission copy
LICENSE                MIT license
```

## License

MIT. See `LICENSE`.

## Credits

Built by CodeCart PRO as the WebMCP competition edition of the agent-ready storefront layer demonstrated on FermaTeh.
