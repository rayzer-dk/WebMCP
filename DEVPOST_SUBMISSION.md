# Devpost submission copy

## Project name

WebMCP Catalog Bridge — Agent-native product discovery for OpenCart

## One-line summary

A live OpenCart storefront exposes structured product-search tools to browser agents, returning real-time price, stock, links and images while showing the same results visibly to the human.

## Live app

https://fermateh.com.ua/en/

## Public code repository

https://github.com/rayzer-dk/WebMCP

## Suggested judge prompt

Open https://fermateh.com.ua/en/ in the built-in browser and use the site's WebMCP tools to find and show 3 animal repellents priced from 5,000 to 10,000 UAH.

## Why this use case is a strong fit for WebMCP

Online stores already have precise structured knowledge about products, pricing, stock, categories and canonical URLs, but a browser agent normally has to reconstruct that knowledge by navigating and reading the UI. That is slower and more error-prone than asking the store directly.

WebMCP Catalog Bridge lets an OpenCart storefront expose the operation the agent actually needs: search the catalog with constraints and return authoritative commerce data. The agent can complete a product-discovery request in a single tool call instead of opening search pages and multiple product pages.

## How it creates a better user experience

The person stays in the normal storefront and asks a natural-language question. ChatGPT sees the site's `Search Products` and `Get Product` tools. When `Search Products` runs, OpenCart applies the request, reads current price and stock, and returns compact structured results with product links and image URLs.

At the same time, the storefront renders a visible “Products received by AI” panel containing the same product cards. The human can immediately see what the agent received and can open any result. This makes the agent interaction observable instead of hidden.

## What people and agents can do together that was difficult before

A user can ask for a constrained selection such as “show three animal repellents from 5,000 to 10,000 UAH” without manually opening the store search, setting price filters, checking stock, and inspecting several product pages. The agent delegates the exact catalog operation to the site and gives the user the result, while the site visibly confirms the selected products.

## How WebMCP was implemented

The storefront loads a small JavaScript bridge that calls `document.modelContext.registerTool()` and registers two read-only tools. `search_products` accepts a natural-language query plus optional result-count, price and stock constraints. `get_product` retrieves one selected product by ID.

Each tool executes a same-origin JSON POST to an OpenCart controller. The backend searches only active products assigned to the current store, resolves the currently open storefront language, ranks candidates, then hydrates live price, stock, currency, SEO URL, image and product attributes from OpenCart at call time. The tool response is returned directly to the browser agent and can also be rendered as visible cards on the page.

The public competition edition includes the complete OpenCart source, install package, MIT license, architecture notes, testing instructions and demo script.

## What was built during the challenge

The FermaTeh store and catalog were pre-existing. The WebMCP browser integration and agent-native catalog layer were added during the challenge: tool registration, structured search, live commerce hydration, multilingual locale binding, visible AI result presentation, tool-level performance tuning and read-only endpoint security.

## Technologies

WebMCP, JavaScript, OpenCart 3.x / ocStore 3.x, PHP, MySQL/MariaDB, Twig.
