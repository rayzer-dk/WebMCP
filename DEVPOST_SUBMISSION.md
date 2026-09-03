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

WebMCP lets the website expose the operations the agent actually needs. Search Products returns authoritative live commerce data directly from OpenCart, Get Product supplies details only for a selected item, and Add To Cart can safely change the current cart when the user explicitly requests it.

## How it creates a better user experience

The person stays on the normal store and asks a natural-language question. ChatGPT discovers the site's WebMCP tools and calls Search Products directly instead of navigating the visible search UI first.

The store returns current price, stock, links, images and product attributes. At the same moment, the storefront renders the same products in a visible Shared human + AI results panel. The user can therefore see what the agent received instead of trusting an invisible backend call.

When Add To Cart is explicitly requested, the live OpenCart cart changes in the same browser session and the storefront cart UI is refreshed immediately. The action receipt explicitly confirms that no order and no payment were submitted.

## How WebMCP was implemented

The storefront loads a small browser bridge using `document.modelContext.registerTool()` and exposes Search Products, Get Product and Add To Cart. The bridge binds tool calls to the locale of the currently visible storefront page and sends same-origin JSON POST requests to OpenCart controllers.

The backend hydrates current price, stock, currency, canonical product URL, image and attributes at call time. Search results include trust-contract signals such as `authoritative: true`, `result_is_final: true`, `browser_verification_required: false` and `navigation_required: false`.

Human presentation and agent payload are intentionally separated: the model receives compact structured commerce data while the shopper sees the same products and cart confirmation on the storefront.

The entire module installs disabled. In the competition preset, Add To Cart is enabled in its settings so that after the administrator enables the module all three tools are immediately available. The cart action can be disabled independently. It changes the cart only, rejects products with required options and never implements checkout, order placement or payment.

## What was built during the challenge

The FermaTeh store and its OpenCart catalog existed before the challenge. The WebMCP interaction layer was built during the submission period: browser-native tool registration, authoritative structured product search, live price and stock hydration, multilingual page binding, trust-contract result semantics, shared human + AI presentation, compact agent payloads, Add To Cart, live cart synchronization, cart-only action receipts, same-origin validation, payload limits and rate limiting.

## Technologies

WebMCP, JavaScript, OpenCart 3.x / ocStore 3.x, PHP, MySQL/MariaDB, Twig.
