# Demo video script (target: 2:15–2:40)

## 0:00–0:20 — Problem

Show the FermaTeh storefront.

Voiceover:

“Product discovery is a structured commerce task, but browser agents normally have to navigate search pages, filters and product cards. WebMCP lets the store expose the operation directly.”

## 0:20–0:40 — Site tools

Open the Site tools menu and show `Search Products` and `Get Product`.

Voiceover:

“This existing OpenCart store now exposes two read-only browser-native WebMCP tools.”

## 0:40–1:25 — Main request

Prompt:

“Find and show 3 animal repellents priced from 5,000 to 10,000 UAH.”

Keep the Site tools invocation visible if possible.

Voiceover:

“The agent sends one structured catalog request. OpenCart resolves the product type and constraints, then reads current price and stock from the commerce backend.”

## 1:25–1:55 — Human-agent shared result

Focus on the `Products received by AI` panel with real product images, prices and stock.

Voiceover:

“The same products returned to the agent are also rendered visibly on the storefront. The human can see exactly what the agent received and open a result directly.”

## 1:55–2:20 — Why WebMCP

Show the concise ChatGPT answer and product links.

Voiceover:

“There is no DOM guessing and no need to open multiple product pages to verify catalog facts. The agent gets an explicit interface while the human keeps the normal shopping experience.”

## 2:20–2:35 — Implementation

Briefly show the GitHub repository and the `document.modelContext.registerTool()` section.

Voiceover:

“The competition edition is open source. It registers the tools in the browser and uses same-origin OpenCart endpoints for live catalog data.”

## Recording notes

- Record a clean run without connection-retry messages.
- Use the English storefront for the submission unless another language demonstrates the experience better.
- Keep the product result panel visible for several seconds.
- Do not expose admin credentials, private logs, customer data or API keys.
- Keep the final public video concise and include audio.
