/**
 * SPYRJA — shared front-end behaviour.
 *
 * One file for both locales. Strings are keyed by <html lang="…">, so adding a
 * language means adding one entry to STRINGS, not copying this file.
 */
(function () {
  'use strict';

  var STRINGS = {
    en: {
      valueMissing: 'This field is required',
      typeMismatch: 'Please enter a valid e-mail address',
      networkError: 'Could not reach the server. Please try again.',
      genericError: 'Error sending message.'
    },
    is: {
      valueMissing: 'Þennan reit verður að fylla út',
      typeMismatch: 'Sláðu inn gilt netfang',
      networkError: 'Náðist ekki samband við netþjóninn. Reyndu aftur.',
      genericError: 'Villa kom upp við að senda skilaboð.'
    }
  };

  var lang = document.documentElement.lang || 'en';
  var t = STRINGS[lang] || STRINGS.en;

  document.addEventListener('DOMContentLoaded', function () {
    initReveal();
    initValidation();
    initContactForm();
  });

  /* ---------------------------------------------------------------- *
   * Reveal animation
   *
   * The tiles are visible by default in CSS. The `js` class on <html>
   * is what hides them, so a visitor without JavaScript — or one whose
   * browser lacks IntersectionObserver — always sees the full section.
   * ---------------------------------------------------------------- */
  function initReveal() {
    var items = document.querySelectorAll('.service-item');
    if (!items.length) return;

    var reducedMotion = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function showAll() {
      Array.prototype.forEach.call(items, function (item) {
        item.classList.add('show');
      });
    }

    if (reducedMotion || !('IntersectionObserver' in window)) {
      showAll();
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var index = Array.prototype.indexOf.call(items, entry.target);
        setTimeout(function () {
          entry.target.classList.add('show');
        }, index * 120);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.15 });

    Array.prototype.forEach.call(items, function (item) {
      observer.observe(item);
    });
  }

  /* ---------------------------------------------------------------- *
   * Localised constraint-validation messages
   * ---------------------------------------------------------------- */
  function initValidation() {
    var form = document.getElementById('contact-form');
    if (!form) return;

    Array.prototype.forEach.call(form.querySelectorAll('[required]'), function (field) {
      function refresh() {
        // Clear first, otherwise a stale custom message keeps the field invalid.
        field.setCustomValidity('');
        if (field.validity.valueMissing) {
          field.setCustomValidity(t.valueMissing);
        } else if (field.validity.typeMismatch) {
          field.setCustomValidity(t.typeMismatch);
        }
      }

      field.addEventListener('invalid', refresh);
      // Clearing on input/change is what lets a corrected field submit on the
      // FIRST click instead of the second.
      field.addEventListener('input', function () { field.setCustomValidity(''); });
      field.addEventListener('change', function () { field.setCustomValidity(''); });
    });
  }

  /* ---------------------------------------------------------------- *
   * Contact form submission
   * ---------------------------------------------------------------- */
  function initContactForm() {
    var form = document.getElementById('contact-form');
    var feedback = document.getElementById('contact-feedback');
    if (!form || !feedback) return;

    var content = feedback.querySelector('.feedback-content');
    var sendAnother = document.getElementById('send-another');
    var submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      submitBtn.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'text/plain' }
      })
        .then(function (resp) {
          return resp.text().then(function (text) {
            return { ok: resp.ok, text: text.trim() };
          });
        })
        .then(function (result) {
          if (result.ok) {
            form.reset();
            show(result.text || '', true);
          } else {
            show(result.text || t.genericError, false);
          }
        })
        .catch(function () {
          show(t.networkError, false);
        })
        .finally(function () {
          submitBtn.disabled = false;
        });
    });

    if (sendAnother) {
      sendAnother.addEventListener('click', function () {
        feedback.classList.add('hidden');
        content.textContent = '';
        form.classList.remove('hidden');
        form.querySelector('input, textarea, select').focus();
      });
    }

    /**
     * textContent, never innerHTML — the response body is data, not markup.
     */
    function show(text, success) {
      content.textContent = text;
      feedback.classList.toggle('is-error', !success);
      feedback.classList.remove('hidden');
      // On an error the form stays on screen so the visitor can correct and
      // retry — offering "send another" there would make no sense.
      form.classList.toggle('hidden', success);
      if (sendAnother) {
        sendAnother.classList.toggle('hidden', !success);
      }
      feedback.focus();
    }
  }
})();
