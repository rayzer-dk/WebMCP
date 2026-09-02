# WebMCP Catalog Bridge for OpenCart

Open-source competition edition of the WebMCP storefront layer running on FermaTeh.

Live demo: https://fermateh.com.ua/en/

Suggested judge prompt:

> Open https://fermateh.com.ua/en/ in the built-in browser and use the site's WebMCP tools to find and show 3 animal repellents priced from 5,000 to 10,000 UAH.

## What it does

WebMCP Catalog Bridge turns a normal OpenCart storefront into an agent-native catalog without asking the agent to scrape or guess the UI.

The page registers two read-only tools with the browser-native WebMCP API:

```js
document.modelContext.registerTool({
  name: "search_products",
  description: "Primary product-discovery tool for this store...",
  inputSchema: { /* query, limit, min_price, max_price, in_stock */ },
  execute: async (input) => { /* same-origin OpenCart request */ }
});
```

The tools are:

- `search_products` — product discovery with structured constraints, live OpenCart price, live stock, URLs, images and attributes.
- `get_product` — structured details for one product selected from search results.

After a real WebMCP call, the storefront can also render the returned products in a visible panel. This lets the human see the same products the agent received, with real product images, price, stock and links.

## Why this is a strong WebMCP use case

E-commerce search is a good example of where browser agents normally waste time. A visual agent may navigate search pages, inspect filters, open multiple product pages, misread availability, or re-check data that the store already knows precisely.

With WebMCP, the store exposes the catalog operation directly. The agent can send one structured request and receive authoritative results from the commerce backend. The human keeps the normal storefront, while the agent gets a fast, explicit interface designed for the task.

For the live FermaTeh demo, server-side catalog work is typically tens of milliseconds. The agent receives current price and stock from OpenCart instead of inferring them from rendered DOM.

## Human + agent experience

1. The human asks in natural language.
2. The agent calls a structured site tool.
3. OpenCart applies catalog constraints and reads live commerce state.
4. The agent receives structured results.
5. The human sees matching product cards on the storefront.
6. The agent answers with the same products and links.

No checkout, payment, order creation or account mutation is exposed in this competition edition.

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
             v
Catalog search + live product hydration
             |
      +------+-------+
      |              |
 product data     visible page panel
      |              |
      +------> agent + human
```

The browser tool is intentionally small. Catalog truth remains in OpenCart. Search candidates are resolved server-side; final price, currency, stock, SEO URL, image and attributes are hydrated when the tool is called.

## Language handling

The agent does not choose an arbitrary store language. The WebMCP script uses the locale of the currently open storefront page and sends that locale to the backend. The backend resolves it only against enabled OpenCart languages.

## Performance choices

- Only two read-only tools are registered, reducing tool-selection ambiguity.
- `search_products` is the primary discovery operation so the agent does not need a DOM search first.
- Price and stock are read live only for ranked candidates.
- Responses are intentionally compact.
- The result limit is capped server-side at 10.
- The module does not add background jobs, cron tasks or frontend requests when disabled.

## Security

- Module installs disabled by default.
- WebMCP endpoints are POST-only and JSON-only.
- Same-origin / same-site browser requests are enforced.
- Request bodies are size-limited.
- A lightweight per-session rate limit protects public tool endpoints.
- SQL inputs are escaped; numeric IDs are cast to integers.
- Only active products assigned to the current store are returned.
- The competition edition exposes no write tools.

See `docs/SECURITY.md` for details.

## Install on OpenCart 3.x / ocStore 3.x

1. Back up the site files and database.
2. Download or build `WebMCP_Catalog_Challenge_v1.0.0.ocmod.zip`.
3. In OpenCart admin open Extensions > Installer and upload the archive.
4. Open Extensions > Modifications and refresh modifications.
5. Open Extensions > Extensions > Modules.
6. Install `WebMCP Catalog Bridge`.
7. Open its settings, enable the module, and save.
8. Clear the theme/OCMOD cache if needed.
9. Open the storefront in ChatGPT's in-app browser or a WebMCP-enabled browser.
10. Open Site tools and verify `Search Products` and `Get Product` are present.

The install archive contains the same source shown under `opencart-module/`.

## Build the install archive

```bash
cd opencart-module
zip -r ../dist/WebMCP_Catalog_Challenge_v1.0.0.ocmod.zip install.xml upload
```

## What is new for the WebMCP Challenge

FermaTeh and its OpenCart catalog existed before the challenge. The WebMCP work was added during the challenge period as a new agent-native interaction layer.

The challenge work includes browser-native WebMCP tool registration, structured catalog discovery, live price/stock hydration, multilingual page-locale binding, visible product-card presentation, read-only endpoint security, and tool descriptions designed to avoid redundant DOM navigation.

See `docs/WHAT_IS_NEW.md` for the precise breakdown.

## Repository layout

```text
opencart-module/       installable OpenCart competition edition
docs/                  architecture, security, testing and demo notes
dist/                  ready-to-install OCMOD ZIP
DEVPOST_SUBMISSION.md  ready-to-paste submission copy
LICENSE                MIT license
```

## Testing

See `docs/TESTING.md` for exact judge prompts and expected behavior.

## Demo video

See `docs/DEMO_SCRIPT.md` for a focused sub-3-minute recording plan.

## License

MIT. See `LICENSE`.

## Credits

Built by CodeCart PRO as the WebMCP competition edition of the agent-ready catalog layer used on FermaTeh.
