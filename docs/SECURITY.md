# Security

The competition edition is read-only by default. The optional `Add To Cart` write tool is disabled until a store administrator explicitly enables it.

## Controls

- The module installs disabled.
- Search Products and Get Product are read-only.
- Add To Cart is a separate opt-in write tool and is disabled by default.
- Add To Cart changes only the current OpenCart cart; no checkout, order placement or payment tool is implemented.
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

The cart tool intentionally stops before checkout. Its action receipt explicitly reports:

- `scope: cart_only`
- `order_placed: false`
- `payment_performed: false`
- `reversible_by_user: true`

This demonstrates a real state-changing WebMCP action while preserving a clear safety boundary around consequential commerce operations.

## Threat model

The public read tools are intended for product discovery. The optional write action can mutate only the anonymous/current session cart. The implementation bounds payload size, result count and request rate and rejects cross-site calls.

## Production note

A production deployment may add stronger IP-aware rate limiting, observability, fraud controls and application-specific authorization for higher-risk actions. Checkout, order placement and payment should require a separate design and explicit user confirmation; they are intentionally outside this competition edition.
