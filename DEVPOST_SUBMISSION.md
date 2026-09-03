# Devpost submission copy

## Project name

WebMCP Catalog Bridge — Shared human-agent commerce for the existing open web

## One-line summary

An existing OpenCart storefront becomes a shared human-agent commerce interface: WebMCP gives the agent authoritative live catalog tools while the same selected products are rendered visibly to the person on the storefront.

## Live app

https://fermateh.com.ua/en/

## Public code repository

https://github.com/rayzer-dk/WebMCP

## Suggested judge prompt

Open https://fermateh.com.ua/en/ in the built-in browser and use the site's WebMCP tools to find and show 3 animal repellents priced from 5,000 to 15,000 UAH.

## Why this use case is a strong fit for WebMCP

Commerce sites already know exact product identity, price, stock, language, canonical URL and structured attributes. A browser agent should not have to reconstruct that authoritative state by scraping the DOM, opening filters and revisiting product pages.

WebMCP lets the website expose the operation the agent actually needs. `search_products` accepts the user's request and explicit constraints and returns authoritative live commerce data directly from OpenCart. The human-facing website does not disappear when the agent arrives: the ordinary storefront remains the shared interaction surface.

This makes WebMCP a native capability layer for the existing open web rather than a replacement website or a separate private agent API.

## How it creates a better user experience

The person stays on the normal store and asks a natural-language question. ChatGPT discovers the site's WebMCP tools and calls `Search Products` directly instead of navigating the visible search UI first.

The store returns current price, stock, links, images and product attributes. At the same moment, the storefront renders the exact same result in a visible “Products received by AI” panel with a WebMCP / live-store-data marker.

The user can therefore see what the agent received instead of trusting an invisible backend call. The agent and human share the same catalog result while each gets the interface best suited to them: structured data for the agent and product cards for the person.

## What people and agents can do together that was difficult before

A user can ask for a constrained selection such as “find three animal repellents from 5,000 to 15,000 UAH” without manually opening catalog search, setting filters, checking availability and comparing multiple product pages.

The agent delegates the exact catalog operation to the website. OpenCart resolves the request against the live store, and the selected products become shared state: ChatGPT receives the structured result while the person simultaneously sees those products on the storefront.

This pattern can be added to an existing CMS without rebuilding the store for agents. The same approach can turn established human-first websites into shared human-agent applications while preserving their normal UI, SEO and commerce backend.

## How WebMCP was implemented

The storefront loads a small browser bridge that uses `document.modelContext.registerTool()` to register two read-only tools:

- `search_products` — primary catalog discovery with natural-language query, result count, price and optional stock constraints.
- `get_product` — details for one product already selected by search.

The bridge binds tool calls to the locale of the currently visible storefront page. Each tool executes a same-origin JSON POST to an OpenCart controller. The backend searches active products assigned to the current store, ranks candidates and hydrates current price, stock, currency, SEO URL, image and attributes at call time.

Successful search results are marked authoritative and final so the agent does not need to re-open catalog or product pages for verification. Remote image URLs remain structured data, while the storefront itself renders the images to avoid dependence on a chat image proxy.

The browser also publishes the latest shared storefront result as page state and emits a shared-state event, allowing the human-facing UI and future page integrations to react to the same WebMCP result the agent received.

The competition edition is deliberately read-only. It includes same-origin validation, bounded payloads, rate limiting and no checkout, payment or order-creation tool.

## What was built during the challenge

The FermaTeh store and product catalog existed before the challenge. The challenge work added the agent-native WebMCP layer: browser tool registration, structured product discovery, live commerce hydration, multilingual page-locale binding, authoritative final-result semantics, shared human-agent storefront presentation, visible data provenance, performance-oriented tool design and read-only endpoint security.

## Technologies

WebMCP, JavaScript, OpenCart 3.x / ocStore 3.x, PHP, MySQL/MariaDB, Twig.
