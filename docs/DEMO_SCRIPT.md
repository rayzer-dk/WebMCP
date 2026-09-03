# Final demo video

Public YouTube demo: https://youtu.be/etBFEEBXYAA

The final competition workflow demonstrates:

1. A normal live OpenCart storefront.
2. Browser-native Site tools exposed by the page.
3. `Search Products` for authoritative live catalog discovery.
4. `Get Product` only for the selected recommendation.
5. The `Shared human + AI results` storefront panel showing the same live product state to the shopper.
6. The optional `Add To Cart` WebMCP write action.
7. Immediate visible cart synchronization in the active OpenCart session.
8. An action receipt confirming that no order and no payment were submitted.

Recommended judge prompt:

> Find 3 animal repellents currently in stock between 5,000 and 15,000 UAH. Compare them and recommend the best option for a farm, field or garden. Use Get Product for the recommended item. Then add 1 unit of the recommended simple product to the cart. Do not place an order or make a payment.

The key idea is not only agent-side tool execution. The human and agent share the live storefront state: the model receives compact structured data, while the shopper sees visual product results and cart confirmation on the website itself.
