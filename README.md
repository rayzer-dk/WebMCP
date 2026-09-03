# WebMCP Catalog Bridge for OpenCart

Open-source competition edition of the WebMCP storefront layer demonstrated on FermaTeh.

Live demo: https://fermateh.com.ua/en/

Demo video: https://youtu.be/etBFEEBXYAA

Public submission concept: a shared human + AI storefront where agents search, compare and act on live commerce data through WebMCP.

Suggested judge prompt:

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

The competition edition exposes up to three tools:

- `search_products` — authoritative product discovery with live OpenCart price, stock, URLs, images and attributes.
- `get_product` — detailed structured information for one selected product.
- `add_to_cart` — optional write tool that adds one selected simple product to the current OpenCart cart. It never places an order and never performs payment.

`add_to_cart` is disabled by default and must be explicitly enabled by the store administrator.

## Why this is a strong WebMCP use case

Online stores already know their catalog, price, stock, product identity and cart state. A general browser agent normally has to reconstruct that information from the visible UI by navigating search pages, opening product pages and parsing DOM content.

WebMCP lets the storefront expose those domain operations directly. The agent can ask the store for authoritative results instead of imitating a human browser session.

For the live FermaTeh demo, server-side product searches typically complete in tens of milliseconds. Current price and stock are read from OpenCart at call time.

## Shared human + AI storefront

The website does not disappear when the agent arrives.

After a real WebMCP search, the storefront renders a visible `Shared human + AI results` panel. The shopper sees the same live products the agent received, including real images, current price, current stock and links.

The search result also carries an explicit trust contract, including signals such as:

- `authoritative: true`
- `live_price: true`
- `live_stock: true`
- `result_is_final: true`
- `browser_verification_required: false`
- `navigation_required: false`

This tells the agent that the store itself is the source of truth and that reopening product pages only to verify the same information is unnecessary.

## Read + write collaboration

The strongest demo flow is:

1. Human asks for products in natural language.
2. Agent calls `Search Products`.
3. OpenCart returns live authoritative matches.
4. Human sees the same products in the shared storefront panel.
5. Agent compares results and calls `Get Product` only for the recommendation.
6. If the user explicitly requests it and the administrator enabled the action, the agent calls `Add To Cart`.
7. The active OpenCart cart is changed and the visible storefront cart is refreshed immediately.
8. The action receipt explicitly states that no order and no payment were submitted.

This is a real state-changing WebMCP workflow on a live commerce session, not a simulated chat response.

## Human presentation vs agent payload

The project separates model-oriented structured results from human-oriented visual presentation.

The agent receives compact structured commerce data. The shopper receives product cards and cart confirmation on the storefront. Both are derived from the same underlying tool result.

This keeps the model payload focused while preserving a clear and inspectable human experience.

## Architecture

```text
ChatGPT in-app browser / Chrome WebMCP
             |
             | document.modelContext.registerTool()
             v
      webmcp_catalog.js
             |
             | same-origin JSON POST
             v
OpenCart WebMCP controller
             |
      +------+----------------+
      |                       |
 catalog search          cart mutation
 live price/stock        (opt-in only)
      |                       |
      +----------+------------+
                 |
         shared result state
          /             \
      agent            human UI
```

Catalog truth remains in OpenCart. Final price, currency, stock, SEO URL, image and attributes are hydrated when the tool is called.

## Language handling

The WebMCP layer follows the language of the currently open storefront page instead of trusting an arbitrary agent-selected locale. This keeps agent results aligned with what the human is viewing.

## Security

- Module installs disabled by default.
- `Add To Cart` is a separate opt-in write action and is disabled by default.
- WebMCP endpoints are POST-only and JSON-only.
- Same-origin / same-site browser requests are enforced.
- Request bodies are size-limited.
- A per-session rate limit protects public tool endpoints.
- Numeric identifiers are validated and cast.
- Products with required options are rejected by the write tool and left for shopper completion.
- `Add To Cart` changes the cart only; order placement and payment are not implemented by this competition edition.

See `docs/SECURITY.md` for details.

## Install on OpenCart 3.x / ocStore 3.x

1. Back up the site files and database.
2. Build `WebMCP_Catalog_Challenge_v1.1.0.ocmod.zip` from `opencart-module/` or use the source directly.
3. Upload the archive in Extensions > Installer.
4. Refresh Extensions > Modifications.
5. Install `WebMCP Catalog Bridge` under Extensions > Modules.
6. Open its settings, enable the module and save.
7. Keep `WebMCP Add To Cart` disabled unless you intentionally want the write action.
8. Open the storefront in ChatGPT's in-app browser or a WebMCP-enabled Chrome build.
9. Verify Site tools shows Search Products and Get Product; when cart action is enabled it should also show Add To Cart.

## Build the install archive

```bash
cd opencart-module
zip -r ../dist/WebMCP_Catalog_Challenge_v1.1.0.ocmod.zip install.xml upload
```

## What was built during the WebMCP Challenge

FermaTeh and its OpenCart catalog existed before the challenge. The new challenge work is the WebMCP interaction layer added to the existing storefront.

Challenge-period work includes:

- browser-native `document.modelContext.registerTool()` integration
- authoritative structured product discovery
- live OpenCart price and stock hydration
- multilingual page-locale binding
- explicit trust contract to prevent redundant DOM verification
- shared human + AI storefront presentation
- compact agent payload vs visual human presentation
- optional Add To Cart WebMCP write action
- live storefront cart refresh and action receipt
- same-origin validation, payload limits and rate limiting

See `docs/WHAT_IS_NEW.md` for the breakdown.

## Repository layout

```text
opencart-module/       installable OpenCart competition edition
docs/                  architecture, security, testing and demo notes
dist/                  build artifacts / checksums
DEVPOST_SUBMISSION.md  submission copy
LICENSE                MIT license
```

## Demo video

https://youtu.be/etBFEEBXYAA

## License

MIT. See `LICENSE`.

## Credits

Built by CodeCart PRO as the WebMCP competition edition of the agent-ready storefront layer demonstrated on FermaTeh.
