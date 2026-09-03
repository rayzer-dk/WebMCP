# Devpost submission copy

## Project name

WebMCP Catalog Bridge

## Elevator pitch

A shared human + AI storefront where agents search, compare and act on live commerce data through WebMCP.

## Live app

https://fermateh.com.ua/en/

## Demo video

https://youtu.be/etBFEEBXYAA

## Public code repository

https://github.com/rayzer-dk/WebMCP

## Suggested judge prompt

Open https://fermateh.com.ua/en/ in the built-in browser and use the website's WebMCP tools. Find 3 animal repellents currently in stock between 5,000 and 15,000 UAH. Compare them, recommend the best option for a farm, field or garden, use Get Product for the recommendation, then add 1 unit of the recommended simple product to the cart. Do not place an order or make a payment.

## Why this use case is a strong fit for WebMCP

Commerce sites already know exact product identity, price, stock, language, canonical URL, attributes and cart state. A browser agent should not need to reconstruct that authoritative state by scraping the DOM, opening filters and revisiting product pages.

WebMCP lets the website expose the operations the agent actually needs. `search_products` returns authoritative live commerce data directly from OpenCart, `get_product` supplies details only for a selected item, and the optional `add_to_cart` tool can safely change the current cart when the user explicitly requests it.

This makes WebMCP a native capability layer for the existing open web rather than a replacement storefront or a separate private agent API.

## How it creates a better user experience

The person stays on the normal store and asks a natural-language question. ChatGPT discovers the site's WebMCP tools and calls Search Products directly instead of navigating the visible search UI first.

The store returns current price, stock, links, images and product attributes. At the same moment, the storefront renders the same products in a visible `Shared human + AI results` panel.

The user can therefore see what the agent received instead of trusting an invisible backend call. The agent gets compact structured data, while the shopper gets real product cards and action confirmation on the storefront.

When Add To Cart is explicitly requested, the live OpenCart cart changes in the same browser session and the storefront cart UI is refreshed immediately. The action receipt explicitly confirms that no order and no payment were submitted.

## What people and agents can do together that was difficult before

A shopper can ask for a constrained selection such as “find three in-stock animal repellents from 5,000 to 15,000 UAH,” receive a comparison and recommendation, inspect detailed structured data for the winner, and ask the agent to add that exact item to the live cart.

Before WebMCP, an agent would normally have to imitate a user by navigating catalog pages, manipulating filters, parsing rendered content and then separately clicking cart UI. Here the website exposes the same domain operations directly, while the person remains on the normal storefront and sees the shared state.

This pattern can be added to an existing CMS without rebuilding the store for agents. Established human-first websites can become shared human-agent applications while preserving their UI, SEO and existing commerce backend.

## How WebMCP was implemented

The storefront loads a small browser bridge using `document.modelContext.registerTool()`.

The tools are:

- `search_products` — primary catalog discovery with natural-language query, count, price and stock constraints.
- `get_product` — detailed information for one already-selected product.
- `add_to_cart` — opt-in write tool that adds a selected simple product to the current cart only.

The bridge binds tool calls to the locale of the currently visible storefront page and sends same-origin JSON POST requests to OpenCart controllers.

The backend searches active products assigned to the current store and hydrates current price, stock, currency, canonical product URL, image and attributes at call time.

Search results include an explicit trust contract such as `authoritative: true`, `live_price: true`, `live_stock: true`, `result_is_final: true`, `browser_verification_required: false` and `navigation_required: false`. This tells the agent that the store itself is the source of truth and prevents unnecessary DOM re-checks after a final tool result.

Human presentation and agent payload are intentionally separated. The agent receives compact structured commerce data while the shopper sees the same products and cart confirmation in the storefront panel.

The optional cart write tool is disabled by default. It is same-origin, validates product and quantity, rejects products with required options, changes the cart only, and never implements checkout, order placement or payment.

## What was built during the challenge

The FermaTeh store and its OpenCart catalog existed before the challenge. The WebMCP interaction layer was built during the submission period.

Challenge-period work includes:

- browser-native WebMCP tool registration
- authoritative structured product search
- live price and stock hydration
- multilingual page-locale binding
- trust-contract result semantics
- shared human + AI storefront presentation
- compact agent payload vs visual human presentation
- optional Add To Cart WebMCP write action
- live cart UI synchronization and action receipts
- same-origin validation, payload limits and rate limiting

## Technologies

WebMCP, JavaScript, OpenCart 3.x / ocStore 3.x, PHP, MySQL/MariaDB, Twig.
