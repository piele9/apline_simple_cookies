/*
 * APLINE Simple Cookies module for PrestaShop 9 — admin configuration helpers.
 * Help modals ("how to add this"), the cookie-policy template modal with
 * PL/EN tabs, and copy-to-clipboard. Vanilla JS, no jQuery dependency.
 * @author APLINE Arkadiusz Pielechowski
 */
(function () {
  'use strict';

  function openModal(id) {
    var m = document.getElementById(id);
    if (m) {
      m.classList.add('asco-modal-open');
    }
  }

  function closeModal(m) {
    m.classList.remove('asco-modal-open');
  }

  document.addEventListener('click', function (e) {
    var target = e.target;

    // "How to add this" help links next to each tracker field.
    var help = target.closest ? target.closest('.asco-help-link') : null;
    if (help) {
      e.preventDefault();
      openModal('asco-modal-' + help.getAttribute('data-asco-help'));
      return;
    }

    // "Show example template" button.
    if (target.closest && target.closest('#asco-show-policy')) {
      e.preventDefault();
      openModal('asco-modal-policy');
      return;
    }

    // Close button or clicking the dark overlay.
    if ((target.closest && target.closest('[data-asco-close]'))
      || target.classList.contains('asco-modal')) {
      var modal = target.closest('.asco-modal');
      if (modal) {
        closeModal(modal);
      }
      return;
    }

    // Tab switching inside the policy modal.
    var tab = target.closest ? target.closest('[data-asco-tab]') : null;
    if (tab) {
      e.preventDefault();
      var which = tab.getAttribute('data-asco-tab');
      var modalEl = tab.closest('.asco-modal');
      if (!modalEl) {
        return;
      }
      modalEl.querySelectorAll('.asco-tabs li').forEach(function (li) {
        li.classList.remove('active');
      });
      tab.parentNode.classList.add('active');
      modalEl.querySelectorAll('[data-asco-tabpane]').forEach(function (pane) {
        pane.style.display = (pane.getAttribute('data-asco-tabpane') === which) ? '' : 'none';
      });
      return;
    }

    // Copy-to-clipboard.
    var copy = target.closest ? target.closest('.asco-copy-btn') : null;
    if (copy) {
      e.preventDefault();
      var ta = document.getElementById(copy.getAttribute('data-asco-copy'));
      if (!ta) {
        return;
      }
      ta.select();
      ta.setSelectionRange(0, ta.value.length);
      var done = function () {
        var original = copy.innerHTML;
        copy.innerHTML = '✓ Skopiowano';
        setTimeout(function () { copy.innerHTML = original; }, 1500);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(ta.value).then(done, function () {
          try { document.execCommand('copy'); done(); } catch (err) {}
        });
      } else {
        try { document.execCommand('copy'); done(); } catch (err) {}
      }
    }
  });

  // Close any open modal on Escape.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.asco-modal-open').forEach(function (m) {
        closeModal(m);
      });
    }
  });
})();
