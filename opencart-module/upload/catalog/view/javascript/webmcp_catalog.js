(function () {
  'use strict';

  var currentScript = document.currentScript || (function () {
    var scripts = document.querySelectorAll('script[data-webmcp-catalog="1"]');
    return scripts.length ? scripts[scripts.length - 1] : null;
  }());
  if (!currentScript || window.__webmcpCatalogBooted) return;
  window.__webmcpCatalogBooted = true;

  var searchUrl = currentScript.getAttribute('data-search-url') || '';
  var productUrl = currentScript.getAttribute('data-product-url') || '';
  var configuredLocale = (currentScript.getAttribute('data-locale') || '').toLowerCase();
  var liveResults = currentScript.getAttribute('data-live-results') === '1';
  var maxResults = parseInt(currentScript.getAttribute('data-max-results') || '6', 10);
  if (!maxResults || maxResults < 1) maxResults = 6;
  if (maxResults > 10) maxResults = 10;

  function locale() {
    var htmlLang = (document.documentElement.getAttribute('lang') || '').toLowerCase().replace('_', '-');
    if (htmlLang) return htmlLang;
    return configuredLocale || 'en-gb';
  }

  function context() {
    if (document.modelContext && typeof document.modelContext.registerTool === 'function') return document.modelContext;
    if (navigator.modelContext && typeof navigator.modelContext.registerTool === 'function') return navigator.modelContext;
    return null;
  }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    }).then(function (response) {
      return response.json().catch(function () {
        return { ok: false, error: 'invalid_json_response' };
      }).then(function (payload) {
        if (!response.ok && payload && payload.ok !== false) payload.ok = false;
        return payload;
      });
    });
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function uiText() {
    var short = locale().split('-')[0];
    if (short === 'uk') return { title: 'Товари, отримані AI', close: 'Закрити', inStock: 'В наявності', outStock: 'Немає в наявності', model: 'Модель' };
    if (short === 'ru') return { title: 'Товары, полученные AI', close: 'Закрыть', inStock: 'В наличии', outStock: 'Нет в наличии', model: 'Модель' };
    return { title: 'Products received by AI', close: 'Close', inStock: 'In stock', outStock: 'Out of stock', model: 'Model' };
  }

  function renderProducts(result) {
    if (!liveResults || !result || !result.ok || !Array.isArray(result.results)) return;
    var labels = uiText();
    var old = document.getElementById('webmcp-catalog-results');
    if (old && old.parentNode) old.parentNode.removeChild(old);

    var panel = document.createElement('section');
    panel.id = 'webmcp-catalog-results';
    panel.className = 'webmcp-catalog-results';
    var cards = result.results.map(function (item) {
      var image = item.image_url ? '<img src="' + escapeHtml(item.image_url) + '" alt="' + escapeHtml(item.image_alt || item.name) + '" loading="lazy">' : '<div class="webmcp-catalog-noimage">No image</div>';
      var stock = item.in_stock ? labels.inStock : labels.outStock;
      return '<article class="webmcp-catalog-card">'
        + '<a class="webmcp-catalog-image" href="' + escapeHtml(item.url) + '">' + image + '</a>'
        + '<div class="webmcp-catalog-body">'
        + '<a class="webmcp-catalog-name" href="' + escapeHtml(item.url) + '">' + escapeHtml(item.name) + '</a>'
        + (item.model ? '<div class="webmcp-catalog-model">' + escapeHtml(labels.model) + ': ' + escapeHtml(item.model) + '</div>' : '')
        + '<div class="webmcp-catalog-price">' + escapeHtml(item.price_formatted || (item.final_price + ' ' + item.currency)) + '</div>'
        + '<div class="webmcp-catalog-stock ' + (item.in_stock ? 'is-stock' : 'no-stock') + '">' + stock + '</div>'
        + '</div></article>';
    }).join('');

    panel.innerHTML = '<div class="webmcp-catalog-head"><strong>' + escapeHtml(labels.title) + '</strong><button type="button" aria-label="' + escapeHtml(labels.close) + '" title="' + escapeHtml(labels.close) + '">×</button></div><div class="webmcp-catalog-grid">' + cards + '</div>';
    panel.querySelector('button').addEventListener('click', function () {
      if (panel.parentNode) panel.parentNode.removeChild(panel);
    });
    document.body.appendChild(panel);
  }

  function renderSingle(result) {
    if (!result || !result.ok || !result.product) return;
    renderProducts({ ok: true, results: [result.product] });
  }

  function registerTools(modelContext) {
    if (window.__webmcpCatalogRegistered) return;

    modelContext.registerTool({
      name: 'search_products',
      description: 'Primary product-discovery tool for this store. Use it before browsing the DOM or search pages. Send the user request once and map hard price, stock and result-count constraints into the schema. The store returns live OpenCart price and stock plus product URL and image. When ok=true and authoritative=true, answer from the tool result without visually re-checking the catalog. If fewer products are returned and has_more=false, report the smaller available set instead of browsing for more.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        properties: {
          query: { type: 'string', minLength: 1, maxLength: 300, description: 'Natural-language product request without navigation instructions.' },
          limit: { type: 'integer', minimum: 1, maximum: maxResults, description: 'Number of products requested by the user.' },
          min_price: { type: 'number', minimum: 0, description: 'Minimum price in the storefront currency when the user gives a lower bound.' },
          max_price: { type: 'number', minimum: 0, description: 'Maximum price in the storefront currency when the user gives an upper bound.' },
          in_stock: { type: 'boolean', description: 'Set true only when in-stock availability is a hard requirement; omit otherwise.' }
        },
        required: ['query']
      },
      execute: function (input) {
        var body = Object.assign({}, input || {}, { locale: locale() });
        return postJson(searchUrl, body).then(function (result) {
          renderProducts(result);
          return result;
        });
      }
    });

    modelContext.registerTool({
      name: 'get_product',
      description: 'Get one product by product_id with live price, stock, URL, image and structured attributes. Use only after search_products when the user needs details about a selected product; do not call it for every search candidate.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        properties: {
          product_id: { type: 'integer', minimum: 1, description: 'OpenCart product_id returned by search_products.' }
        },
        required: ['product_id']
      },
      execute: function (input) {
        return postJson(productUrl, { product_id: input.product_id, locale: locale() }).then(function (result) {
          renderSingle(result);
          return result;
        });
      }
    });

    window.__webmcpCatalogRegistered = true;
    window.dispatchEvent(new CustomEvent('webmcp-catalog:registered', { detail: { tools: 2, locale: locale() } }));
  }

  var started = Date.now();
  var timer = window.setInterval(function () {
    var modelContext = context();
    if (modelContext) {
      window.clearInterval(timer);
      try {
        registerTools(modelContext);
      } catch (error) {
        window.__webmcpCatalogRegistered = false;
        console.warn('[WebMCP Catalog Bridge] registration failed', error);
      }
      return;
    }
    if (Date.now() - started > 12000) {
      window.clearInterval(timer);
    }
  }, 200);
}());
