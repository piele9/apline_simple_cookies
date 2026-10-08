/*
 * APLINE Simple Cookies module for PrestaShop 9 — front-end consent logic.
 * Decides whether to show the banner, handles the Reject / Preferences /
 * Accept / Save buttons, posts the decision to the /consent endpoint, honors
 * GPC, re-prompts on policy change or expiry, and auto-collapses the
 * Preferences view after 5 seconds (UODO). Vanilla JS, no jQuery.
 *
 * When Google Consent Mode v2 is enabled (ASCO_CONSENT_MODE_V2), the PHP
 * side already sent gtag('consent','default', …) — all denied — in <head>,
 * before this file even runs. pushConsentUpdate() below reports the
 * visitor's real decision (fresh save, return visit, or GPC auto-reject)
 * via gtag('consent','update', …).
 *
 * @author Arkadiusz Pielechowski
 */
(function () {
  'use strict';

  // The banner HTML is injected via displayBeforeBodyClosingTag, which the
  // theme renders AFTER the bottom JavaScript bundle. Defer init until the DOM
  // is ready so #asco-banner exists when we look for it.
  function init() {
  var CFG = window.ASCO || {};
  var banner = document.getElementById('asco-banner');
  if (!banner || !CFG.callback_url) {
    return;
  }

  var mainView = banner.querySelector('[data-asco-view="main"]');
  var prefsView = banner.querySelector('[data-asco-view="prefs"]');
  var collapseTimer = null;
  var currentView = 'main';

  /* ------------------------------ helpers ------------------------------ */

  function readCookie(name) {
    var parts = ('; ' + document.cookie).split('; ' + name + '=');
    if (parts.length === 2) {
      return parts.pop().split(';').shift();
    }
    return null;
  }

  // Persist the consent decision client-side. This is the source of truth the
  // banner relies on, so it does not depend on the server response's Set-Cookie
  // header being honored (proxies / output buffering can drop it). PHP reads the
  // same cookie on the next request to populate window.ASCO.existing_consent.
  function writeConsentCookie(decision) {
    var payload = {
      v: CFG.policy_version,
      d: decision,
      t: Math.floor(Date.now() / 1000)
    };
    var maxAge = (CFG.reprompt_days || 365) * 86400;
    var secure = (location.protocol === 'https:') ? '; Secure' : '';
    document.cookie = 'asco_consent=' + encodeURIComponent(JSON.stringify(payload)) +
      '; path=/; max-age=' + maxAge + '; SameSite=Lax' + secure;
  }

  // Returns the parsed consent object if it is still valid, otherwise false.
  function validConsent() {
    var raw = readCookie('asco_consent');
    if (!raw) {
      return false;
    }
    var c;
    try {
      c = JSON.parse(decodeURIComponent(raw));
    } catch (e) {
      return false;
    }
    if (!c || c.v !== CFG.policy_version) {
      return false; // policy version bumped -> re-prompt
    }
    var ageDays = (Date.now() / 1000 - (c.t || 0)) / 86400;
    if (ageDays > (CFG.reprompt_days || 365)) {
      return false; // expired -> re-prompt
    }
    return c;
  }

  function categories() {
    return Array.isArray(CFG.categories) ? CFG.categories : [];
  }

  // Build a decision object: every category -> value, necessary always true.
  function buildDecision(value) {
    var d = {};
    categories().forEach(function (cat) {
      d[cat.slug] = cat.is_necessary ? true : !!value;
    });
    return d;
  }

  // Read the per-category toggles from the Preferences view.
  function readToggles() {
    var d = {};
    categories().forEach(function (cat) {
      if (cat.is_necessary) {
        d[cat.slug] = true;
        return;
      }
      var box = prefsView.querySelector('[data-asco-cat-toggle="' + cat.slug + '"]');
      d[cat.slug] = !!(box && box.checked);
    });
    return d;
  }

  function showView(view) {
    currentView = view;
    if (mainView) {
      mainView.classList.toggle('asco-hidden', view !== 'main');
    }
    if (prefsView) {
      prefsView.classList.toggle('asco-hidden', view !== 'prefs');
    }
    if (view !== 'prefs') {
      clearTimeout(collapseTimer);
    }
  }

  function showBanner(view) {
    banner.classList.remove('asco-hidden');
    showView(view || 'main');
  }

  function hideBanner() {
    banner.classList.add('asco-hidden');
    clearTimeout(collapseTimer);
  }

  function post(decision, source, done) {
    var body = 'decision=' + encodeURIComponent(JSON.stringify(decision)) +
      '&source=' + encodeURIComponent(source) + '&ajax=1';
    fetch(CFG.callback_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body,
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (json) {
      if (done) {
        done(json);
      }
    }).catch(function () {
      if (done) {
        done(null);
      }
    });
  }

  function applyAndClose(decision, source) {
    // Persist client-side first so the banner never reappears, even if the
    // server round-trip fails or its Set-Cookie is dropped by a proxy.
    writeConsentCookie(decision);
    pushConsentUpdate(decision);
    post(decision, source, function (json) {
      ascoLoadScripts((json && json.ok && json.scripts_to_load) ? json.scripts_to_load : scriptsForDecision(decision));
    });
    hideBanner();
  }

  /* ------------------------- Google Consent Mode v2 --------------------- */

  // Maps our consent categories onto Google's Consent Mode v2 signals.
  // functionality_storage / security_storage are not covered by any category
  // here (they were already sent as 'granted' in the <head> default call)
  // and are intentionally left alone.
  function consentModePayload(decision) {
    var d = decision || {};

    return {
      analytics_storage: d.analytics ? 'granted' : 'denied',
      ad_storage: d.marketing ? 'granted' : 'denied',
      ad_user_data: d.marketing ? 'granted' : 'denied',
      ad_personalization: d.marketing ? 'granted' : 'denied'
    };
  }

  // Tell Google about the visitor's actual decision. window.gtag is defined
  // in <head> by the module's consent-default script (see
  // renderConsentModeDefaultScript() in the PHP side) whenever Consent Mode
  // v2 is enabled — never called otherwise, so this is a true no-op when the
  // admin turns the feature off.
  function pushConsentUpdate(decision) {
    if (!CFG.consent_mode_v2 || typeof window.gtag !== 'function') {
      return;
    }
    try {
      window.gtag('consent', 'update', consentModePayload(decision));
    } catch (e) {
      /* never break the page over a consent signal */
    }
  }

  /* ------------------------ tracker injection -------------------------- */

  var loaded = {};

  // Inject the scripts the visitor consented to. Idempotent: each tracker is
  // injected at most once per page.
  function ascoLoadScripts(list) {
    if (!Array.isArray(list)) {
      return;
    }
    list.forEach(function (item) {
      if (!item || !item.type) {
        return;
      }
      var key = item.type + ':' + (item.id || (item.html ? item.html.length : ''));
      if (loaded[key]) {
        return;
      }
      loaded[key] = true;
      try {
        if (item.type === 'ga4') {
          injectGA4(item.id);
        } else if (item.type === 'gtm') {
          injectGTM(item.id);
        } else if (item.type === 'fb') {
          injectFB(item.id);
        } else if (item.type === 'hotjar') {
          injectHotjar(item.id);
        } else if (item.type === 'custom') {
          injectCustom(item.html);
        }
      } catch (e) {
        /* never break the page over a tracker */
      }
    });
  }

  function injectGA4(id) {
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    window.gtag = window.gtag || gtag;
    window.gtag('js', new Date());
    window.gtag('config', id);
  }

  function injectGTM(id) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
  }

  function injectFB(id) {
    if (!window.fbq) {
      var n = window.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
      };
      if (!window._fbq) { window._fbq = n; }
      n.push = n;
      n.loaded = true;
      n.version = '2.0';
      n.queue = [];
      var t = document.createElement('script');
      t.async = true;
      t.src = 'https://connect.facebook.net/en_US/fbevents.js';
      document.head.appendChild(t);
    }
    window.fbq('init', id);
    window.fbq('track', 'PageView');
  }

  function injectHotjar(id) {
    var hjid = parseInt(id, 10);
    if (!hjid) {
      return;
    }
    window.hj = window.hj || function () {
      (window.hj.q = window.hj.q || []).push(arguments);
    };
    window._hjSettings = { hjid: hjid, hjsv: 6 };
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://static.hotjar.com/c/hotjar-' + hjid + '.js?sv=6';
    document.head.appendChild(s);
  }

  // Raw admin HTML/JS: re-create <script> nodes so they actually execute
  // (insertAdjacentHTML would inject them inert).
  function injectCustom(html) {
    if (!html) {
      return;
    }
    var tmp = document.createElement('div');
    tmp.innerHTML = html;
    Array.prototype.slice.call(tmp.childNodes).forEach(function (node) {
      if (node.tagName && node.tagName.toLowerCase() === 'script') {
        var ns = document.createElement('script');
        if (node.src) {
          ns.src = node.src;
          ns.async = true;
        } else {
          ns.textContent = node.textContent;
        }
        document.head.appendChild(ns);
      } else {
        document.head.appendChild(node);
      }
    });
  }

  /* ------------------------------- init -------------------------------- */

  var consent = validConsent();

  if (CFG.gpc_auto_rejected && !consent) {
    // Browser asked us not to track: record a reject silently, no banner.
    var rejected = buildDecision(false);
    writeConsentCookie(rejected);
    pushConsentUpdate(rejected);
    post(rejected, 'gpc', function (json) {
      if (json && json.ok) {
        ascoLoadScripts(json.scripts_to_load || []);
      }
    });
  } else if (consent) {
    // Already decided and still valid: load whatever was granted, no banner.
    // Re-send the decision to Google too — the <head> default call always
    // starts denied, so a returning visitor needs this on every page load.
    pushConsentUpdate(consent.d || {});
    ascoLoadScripts(scriptsForDecision(consent.d || {}));
  } else {
    showBanner('main');
  }

  // Client-side mirror of the server gating — used on reload so already-granted
  // trackers load without a round-trip. Must match scriptsForGrantedConsent().
  function scriptsForDecision(decision) {
    var cfg = CFG.scripts || {};
    var out = [];
    ['ga4', 'gtm', 'fb', 'hotjar'].forEach(function (type) {
      var tr = cfg[type] || {};
      if (tr.id && decision && decision[tr.category]) {
        out.push({ type: type, id: tr.id });
      }
    });
    var custom = cfg.custom || {};
    ['analytics', 'marketing', 'functional'].forEach(function (cat) {
      if (custom[cat] && decision && decision[cat]) {
        out.push({ type: 'custom', html: custom[cat] });
      }
    });
    return out;
  }

  /* ----------------------------- handlers ------------------------------ */

  banner.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('[data-asco-action]') : null;
    if (!btn) {
      return;
    }
    var action = btn.getAttribute('data-asco-action');

    if (action === 'accept_all') {
      applyAndClose(buildDecision(true), currentView === 'prefs' ? 'prefs' : 'banner');
    } else if (action === 'reject_all') {
      applyAndClose(buildDecision(false), currentView === 'prefs' ? 'prefs' : 'banner');
    } else if (action === 'open_prefs') {
      showView('prefs');
      // UODO: auto-collapse Preferences after 5s if nothing is saved.
      clearTimeout(collapseTimer);
      collapseTimer = setTimeout(function () {
        showView('main');
      }, 5000);
    } else if (action === 'save_prefs') {
      applyAndClose(readToggles(), 'prefs');
    }
  });

  // Footer "Cookie settings" link reopens the banner with the current state.
  window.ASCO_openBanner = function () {
    var c = validConsent();
    if (c) {
      // Pre-check the toggles to reflect the saved decision.
      categories().forEach(function (cat) {
        if (cat.is_necessary) {
          return;
        }
        var box = prefsView ? prefsView.querySelector('[data-asco-cat-toggle="' + cat.slug + '"]') : null;
        if (box) {
          box.checked = !!(c.d && c.d[cat.slug]);
        }
      });
    }
    showBanner('main');
  };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
