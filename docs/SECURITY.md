# Security

The module itself installs disabled. In the competition preset, `Add To Cart` is enabled in settings so all three WebMCP tools become available immediately after an administrator explicitly enables the module. The cart action can still be disabled independently.

## Controls

- The module installs disabled, so no WebMCP tool is active immediately after installation.
- Search Products and Get Product are read-only.
- Add To Cart changes only the current OpenCart cart and should be called only after an explicit user request.
- No checkout, order placement or payment tool is implemented.
- Products with required options are rejected and left for shopper completion.
- Tool endpoints accept POST only and require `application/json`.
- Cross-site browser calls are rejected using Origin / Sec-Fetch-Site checks.
- Request bodies are bounded before JSON processing.
- Public tool calls have a lightweight per-session rate limit.
- Product IDs and quantities are validated and cast to integers.
- Search strings are normalized and escaped before SQL use.
- Dynamic table/column identifiers are not accepted from user input.
- Only enabled products assigned to the current OpenCart store are searchable.
- Responses use `Cache-Control: no-store`.
- No API key, token, password or payment data is exposed to WebMCP.

## Write-action boundary

The cart tool intentionally stops before checkout. Its action receipt explicitly reports `scope: cart_only`, `order_placed: false`, `payment_performed: false` and `reversible_by_user: true`.

This demonstrates a real state-changing WebMCP action while preserving a clear safety boundary around consequential commerce operations.

## Production note

A production deployment may add stronger IP-aware rate limiting, observability, fraud controls and application-specific authorization for higher-risk actions. Checkout, order placement and payment require a separate design and explicit user confirmation and are intentionally outside this competition edition.
