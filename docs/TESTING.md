# Testing

## Browser

Use ChatGPT in-app browser with WebMCP support, or another browser build exposing the WebMCP model context API.

## Live test

Open:

https://fermateh.com.ua/en/

Verify the browser's Site tools menu contains:

- Search Products
- Get Product

Run:

> Find and show 3 animal repellents priced from 5,000 to 10,000 UAH.

Expected behavior:

- The agent calls `Search Products` rather than manually scraping the search page.
- The response contains products in the requested price range.
- Product URL, image URL, current price and stock are returned as structured fields.
- The storefront shows a visible “Products received by AI” panel with real product cards.
- The agent can answer directly from the tool result.

## Availability test

Run:

> Find 3 animal repellents in stock from 5,000 to 10,000 UAH.

Expected behavior:

- `in_stock=true` is sent as a hard constraint.
- If fewer than three products satisfy all constraints, the tool returns the smaller valid set instead of unrelated products.

## Product detail test

After search, ask:

> Give me the technical details for the first product.

Expected behavior:

- The agent may call `Get Product` with the returned product_id.
- It should not need to open every candidate product page.

## Language test

Open an English, Ukrainian or Russian storefront language and repeat a search.

Expected behavior:

- The tool result locale matches the open storefront language.
- Product names/descriptions come from that OpenCart language ID.

## Disabled-state test

Disable the module in admin.

Expected behavior:

- No WebMCP JS or CSS is injected into storefront pages.
- Tool endpoints return 404.
- No background jobs or extra storefront SQL queries are created by the module.
