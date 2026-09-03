# Testing

## Browser

Use ChatGPT's in-app browser with WebMCP support, or a compatible Chrome build with WebMCP enabled.

## Live test

Open:

https://fermateh.com.ua/en/

After the module is enabled, Site tools should contain:

- Search Products
- Get Product
- Add To Cart

Recommended final test prompt:

> Find 3 animal repellents currently in stock between 5,000 and 15,000 UAH. Compare them and recommend the best option for a farm, field or garden. Use Get Product for the recommended item. Then add 1 unit of the recommended simple product to the cart. Do not place an order or make a payment.

Expected behavior:

- The agent calls Search Products instead of scraping the catalog DOM.
- Results satisfy price and stock constraints.
- Current price and stock come from OpenCart.
- The storefront displays the same products in the Shared human + AI results panel.
- The agent calls Get Product only for the selected recommendation.
- Add To Cart changes the active OpenCart cart only after the user asks for it.
- The visible cart refreshes without a page reload.
- The action receipt states `scope=cart_only`, `order_placed=false` and `payment_performed=false`.

## Trust-contract test

Search Products should return signals including `authoritative=true`, `live_price=true`, `live_stock=true`, `result_is_final=true`, `browser_verification_required=false` and `navigation_required=false`. The agent should not reopen product pages only to verify data already returned by the authoritative tool.

## Cart boundary test

Disable WebMCP Add To Cart in module settings and reload the storefront. Site tools should then contain only Search Products and Get Product. Re-enable the setting, save and reload; all three tools should return.

Products with required options must be rejected for shopper completion, and no checkout, order-creation or payment tool exists.

## Language test

Repeat a search on English, Ukrainian and Russian storefront pages. The result locale and product-language data should follow the currently open page.

## Disabled-state test

Disable the whole module. No WebMCP JS or CSS should be injected, tool endpoints should return 404, and the module should create no background work.
