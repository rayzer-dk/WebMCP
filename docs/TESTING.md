# Testing

## Browser

Use ChatGPT's in-app browser with WebMCP support, or a compatible Chrome build with WebMCP enabled.

## Live test

Open:

https://fermateh.com.ua/en/

Verify Site tools contains:

- Search Products
- Get Product
- Add To Cart, when the administrator has enabled the optional cart action

Recommended final test prompt:

> Find 3 animal repellents currently in stock between 5,000 and 15,000 UAH. Compare them and recommend the best option for a farm, field or garden. Use Get Product for the recommended item. Then add 1 unit of the recommended simple product to the cart. Do not place an order or make a payment.

Expected behavior:

- The agent calls Search Products instead of scraping the catalog DOM.
- Results satisfy price and stock constraints.
- Current price and stock come from OpenCart.
- The storefront displays the same products in the Shared human + AI results panel.
- The agent calls Get Product only for the selected recommendation.
- If cart action is enabled, Add To Cart changes the active OpenCart cart.
- The visible cart refreshes without a page reload.
- The action receipt states `scope=cart_only`, `order_placed=false` and `payment_performed=false`.

## Trust-contract test

Inspect the Search Products result.

Expected signals include:

- authoritative = true
- live_price = true
- live_stock = true
- result_is_final = true
- browser_verification_required = false
- navigation_required = false

The agent should not reopen product pages only to verify price or stock already returned by the authoritative tool.

## Product detail test

After search, ask for technical details of one selected item.

Expected behavior:

- The agent calls Get Product for that product_id.
- It does not call Get Product for every candidate.

## Cart safety test

Keep the cart action disabled in module settings and reload the storefront.

Expected behavior:

- Site tools contains only the two read-only tools.

Enable WebMCP Add To Cart, save settings and open/reload a storefront document.

Expected behavior:

- Site tools contains three tools.
- Add To Cart accepts a selected simple product and quantity.
- Products with required options are rejected for shopper completion.
- No checkout, order creation or payment tool exists.

## Language test

Open an English, Ukrainian or Russian storefront language and repeat a search.

Expected behavior:

- The tool result follows the currently open storefront language.
- Product names/descriptions come from the corresponding OpenCart language.

## Disabled-state test

Disable the module in admin.

Expected behavior:

- No WebMCP JS or CSS is injected into storefront pages.
- Tool endpoints return 404.
- No background jobs or extra storefront SQL queries are created by the module.
