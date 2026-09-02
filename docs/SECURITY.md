# Security

The competition edition is deliberately read-only.

## Controls

- The module installs disabled.
- No checkout, payment, order, account or cart mutation tool is registered.
- Tool endpoints accept POST only.
- Tool endpoints require `application/json`.
- Cross-site browser calls are rejected using Origin / Sec-Fetch-Site checks.
- Request bodies are bounded before JSON processing.
- Public tool calls have a lightweight per-session rate limit.
- Product IDs are cast to integers.
- Search strings are normalized and escaped before SQL use.
- Dynamic table/column identifiers are not accepted from user input.
- Only enabled products assigned to the current OpenCart store are searchable.
- Responses use `Cache-Control: no-store`.
- No API key, token, password or customer data is exposed to WebMCP.

## Threat model

The public tools are intended for anonymous product discovery. An attacker should not be able to mutate catalog or customer state through these endpoints. The main risks are request amplification and malformed input, so the implementation bounds payload size, result count, candidate count and request rate.

## Production note

A production deployment may add a stronger IP-aware rate limiter, observability, a prebuilt catalog index and application-specific bot controls. Those are operational layers rather than requirements for the WebMCP interface itself.
