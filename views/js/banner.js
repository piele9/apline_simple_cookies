/*
 * APLINE Simple Cookies module for PrestaShop 9 — front-end consent logic.
 * Decides whether to show the banner, handles the Reject / Preferences /
 * Accept / Save buttons, posts the decision to the /consent endpoint, honors
 * GPC, re-prompts on policy change or expiry, and auto-collapses the
 * Preferences view after 5 seconds (UODO). Vanilla JS, no jQuery.
 *
 * Tracker injection (ascoLoadScripts) is a no-op here and is implemented in
 * CP08.
 * @author APLINE Arkadiusz Pielechowski
 */
(function () {
  'use strict';

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
    post(decision, source, function (json) {
      if (json && json.ok) {
        ascoLoadScripts(json.scripts_to_load || []);
      }
    });
    hideBanner();
  }

  // Tracker injection — implemented in CP08.
  function ascoLoadScripts(list) {
    /* no-op until CP08 */
  }

  /* ------------------------------- init -------------------------------- */

  var consent = validConsent();

  if (CFG.gpc_auto_rejected && !consent) {
    // Browser asked us not to track: record a reject silently, no banner.
    var rejected = buildDecision(false);
    post(rejected, 'gpc', function (json) {
      if (json && json.ok) {
        ascoLoadScripts(json.scripts_to_load || []);
      }
    });
  } else if (consent) {
    // Already decided and still valid: load whatever was granted, no banner.
    ascoLoadScripts(scriptsForDecision(consent.d || {}));
  } else {
    showBanner('main');
  }

  // Client-side mirror of the server gating — used on reload (CP08 fills it in).
  function scriptsForDecision(decision) {
    return [];
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
})();
