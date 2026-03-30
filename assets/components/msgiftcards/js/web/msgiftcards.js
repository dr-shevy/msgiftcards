(function () {
  function init() {
    var cfg = window.msGiftCardsConfig || {};
    var root = document.querySelector('[data-ms2giftcards]');
    if (!root || !cfg.connectorUrl) {
      return;
    }

    var input = root.querySelector('#gift_code');
    var message = root.querySelector('[data-ms2giftcards-message]');
    var inlineInfoBlock = root.querySelector('[data-ms2giftcards-info-block]');
    var applyBtn = root.querySelector('[data-ms2giftcards-apply]');
    var removeBtn = root.querySelector('[data-ms2giftcards-remove]');

    if (inlineInfoBlock) {
      var allInfoBlocks = document.querySelectorAll('[data-ms2giftcards-info-block]');
      if (allInfoBlocks.length > 1) {
        inlineInfoBlock.classList.add('d-none');
      } else {
        inlineInfoBlock.classList.remove('d-none');
      }
    }

    function setMessage(text, isError) {
      if (!message) return;
      message.textContent = text || '';
      message.classList.remove('ms2giftcards-success', 'ms2giftcards-error');
      if (text) {
        message.classList.add(isError ? 'ms2giftcards-error' : 'ms2giftcards-success');
      }
    }

    function isSuccess(res) {
      return !!(res && (res.success === true || res.success === 1 || res.success === '1'));
    }

    function getPayload(res) {
      if (!res || typeof res !== 'object') {
        return {};
      }
      if (res.data && typeof res.data === 'object') {
        return res.data;
      }
      if (res.object && typeof res.object === 'object') {
        return res.object;
      }
      return {};
    }

    function formatAmount(v) {
      var n = Number(v);
      if (!isFinite(n)) return '0';
      var s = n.toFixed(2);
      return s.replace(/\.?0+$/, '');
    }

    function interpolateTemplate(template, data) {
      return String(template || '').replace(/\[\[\+([a-zA-Z0-9_]+)\]\]/g, function (full, key) {
        return Object.prototype.hasOwnProperty.call(data, key) ? String(data[key]) : '';
      });
    }

    function buildAppliedMessage(data) {
      var nominal = formatAmount(data.nominal);
      var balance = formatAmount(data.balance);
      var writeoff = formatAmount(data.writeoff);
      var remain = formatAmount(data.balance_after);
      var currency = data.currency || '';
      var template = cfg.messageAppliedTemplate || '';
      return interpolateTemplate(template, {
        nominal: nominal,
        balance: balance,
        writeoff: writeoff,
        balance_after: remain,
        currency: currency
      });
    }

    function setInfo(html) {
      var blocks = document.querySelectorAll('[data-ms2giftcards-info-block]');
      for (var i = 0; i < blocks.length; i++) {
        blocks[i].innerHTML = html || '';
      }
    }

    function updateButtons(isApplied) {
      if (applyBtn) {
        applyBtn.classList.toggle('d-none', !!isApplied);
      }
      if (removeBtn) {
        removeBtn.classList.toggle('d-none', !isApplied);
      }
    }

    function renderInfoFromResponse(data) {
      if (!data || !data.code) {
        setInfo('');
        return;
      }
      if (data.info_html) {
        setInfo(data.info_html);
        return;
      }
      setInfo('');
    }

    function post(action, code) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('ctx', cfg.ctx || 'web');
      if (typeof code === 'string') {
        body.set('code', code);
      }

      return fetch(cfg.connectorUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: body.toString()
      }).then(function (r) { return r.json(); });
    }

    function refreshCheckoutCost() {
      if (window.miniShop2 && miniShop2.Order && typeof miniShop2.Order.getcost === 'function') {
        miniShop2.Order.getcost();
      }
    }

    function refreshCheckoutCostSafe() {
      refreshCheckoutCost();
      window.setTimeout(refreshCheckoutCost, 120);
    }

    function syncCurrentInfo() {
      return post('giftcard/current').then(function (res) {
        if (!isSuccess(res)) {
          return null;
        }
        var data = getPayload(res);
        if (!data.applied) {
          updateButtons(false);
          setInfo('');
          setMessage('', false);
          return data;
        }
        updateButtons(true);
        renderInfoFromResponse(data);
        setMessage(buildAppliedMessage(data), false);
        return data;
      });
    }

    var syncTimer = null;
    function scheduleSync(delay) {
      if (syncTimer) {
        window.clearTimeout(syncTimer);
      }
      syncTimer = window.setTimeout(function () {
        syncCurrentInfo();
      }, typeof delay === 'number' ? delay : 250);
    }

    function hasAppliedCertificate() {
      return !!(input && String(input.value || '').trim().length);
    }

    function formatOrderCostValue(value) {
      if (window.miniShop2 && miniShop2.Utils && typeof miniShop2.Utils.formatPrice === 'function') {
        return miniShop2.Utils.formatPrice(value);
      }
      var n = Number(value);
      if (isFinite(n)) {
        return n.toFixed(2).replace(/\.?0+$/, '');
      }
      return String(value == null ? '' : value);
    }

    function forceRenderOrderCost(value) {
      if (value == null || value === '') {
        return;
      }
      var formatted = formatOrderCostValue(value);
      var nodes = document.querySelectorAll('#ms2_order_cost');
      for (var i = 0; i < nodes.length; i++) {
        nodes[i].textContent = formatted;
      }
    }

    function bindMiniShop2Callbacks() {
      if (!window.miniShop2 || !miniShop2.Callbacks || typeof miniShop2.Callbacks.add !== 'function') {
        return;
      }

      var onCartChanged = function () {
        if (!hasAppliedCertificate()) {
          return;
        }
        refreshCheckoutCostSafe();
        scheduleSync(180);
      };

      var onOrderCostChanged = function (response) {
        if (!hasAppliedCertificate()) {
          return;
        }
        var data = (response && response.data) ? response.data : {};
        var costValue = data.cost;
        if ((costValue == null || costValue === '') && data.cart_cost != null) {
          var cart = Number(data.cart_cost);
          var delivery = Number(data.delivery_cost || 0);
          if (isFinite(cart) && isFinite(delivery)) {
            costValue = cart + delivery;
          }
        }
        forceRenderOrderCost(costValue);
        scheduleSync(120);
      };

      miniShop2.Callbacks.add('Cart.add.response.success', 'msgiftcards', onCartChanged);
      miniShop2.Callbacks.add('Cart.remove.response.success', 'msgiftcards', onCartChanged);
      miniShop2.Callbacks.add('Cart.change.response.success', 'msgiftcards', onCartChanged);
      miniShop2.Callbacks.add('Cart.clean.response.success', 'msgiftcards', function () {
        if (input) {
          input.value = '';
        }
        updateButtons(false);
        setMessage('', false);
        setInfo('');
      });
      miniShop2.Callbacks.add('Order.getcost.response.success', 'msgiftcards', onOrderCostChanged);
    }

    if (applyBtn) {
      applyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var code = input ? input.value.trim() : '';
        post('giftcard/apply', code).then(function (res) {
          if (!isSuccess(res)) {
            setMessage((res && res.message) || (cfg.messageErrorGeneric || ''), true);
            return;
          }
          var data = getPayload(res);
          updateButtons(true);
          renderInfoFromResponse(data);
          setMessage(buildAppliedMessage(data), false);
          refreshCheckoutCostSafe();
          scheduleSync(350);
        }).catch(function () {
          setMessage(cfg.messageNetworkError || '', true);
        });
      });
    }

    if (removeBtn) {
      removeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        post('giftcard/remove').then(function (res) {
          if (input) {
            input.value = '';
          }
          if (!isSuccess(res)) {
            setMessage((res && res.message) || (cfg.messageErrorGeneric || ''), true);
            return;
          }
          updateButtons(false);
          setMessage(cfg.messageRemoved || '', false);
          setInfo('');
          refreshCheckoutCostSafe();
        }).catch(function () {
          setMessage(cfg.messageNetworkError || '', true);
        });
      });
    }

    document.addEventListener('click', function (e) {
      var cleanBtn = e.target.closest('[name="ms2_action"][value="cart/clean"], [data-ms2-action="cart/clean"], .ms2_cart_link_clean');
      if (!cleanBtn) {
        return;
      }
      setTimeout(function () {
        if (input) {
          input.value = '';
        }
        updateButtons(false);
        setMessage('', false);
        setInfo('');
        post('giftcard/remove');
      }, 150);
    });

    document.addEventListener('click', function (e) {
      var cartAction = e.target.closest(
        '[name="ms2_action"][value="cart/add"],'
        + '[name="ms2_action"][value="cart/change"],'
        + '[name="ms2_action"][value="cart/remove"],'
        + '[name="ms2_action"][value="cart/clean"],'
        + '[data-ms2-action="cart/add"],'
        + '[data-ms2-action="cart/change"],'
        + '[data-ms2-action="cart/remove"],'
        + '[data-ms2-action="cart/clean"],'
        + '.ms2_cart_link_plus,'
        + '.ms2_cart_link_minus,'
        + '.ms2_cart_link_delete,'
        + '.ms2_cart_link_clean'
      );
      if (cartAction) {
        scheduleSync(300);
      }
    });

    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!form || !form.querySelector) {
        return;
      }
      var actionInput = form.querySelector('[name="ms2_action"]');
      if (!actionInput || !actionInput.value) {
        return;
      }
      if (
        actionInput.value === 'cart/add'
        || actionInput.value === 'cart/change'
        || actionInput.value === 'cart/remove'
        || actionInput.value === 'cart/clean'
      ) {
        scheduleSync(300);
      }
    });

    bindMiniShop2Callbacks();
    updateButtons(hasAppliedCertificate());
    setTimeout(syncCurrentInfo, 100);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
