/**
 * public/assets/js/password-validator.js
 *
 * Shared real-time password complexity and confirmation validator.
 * Used on User Registration and Profile Update Password forms.
 */
(function (global) {
  'use strict';

  var ICONS = {
    check: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    unmet: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/></svg>',
    cross: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
  };

  /**
   * Attach password complexity validation to a form.
   *
   * @param {Object} options
   *   - form: HTMLFormElement
   *   - passwordInput: HTMLInputElement
   *   - confirmInput: HTMLInputElement
   *   - submitBtn: HTMLButtonElement
   *   - confirmFeedback: HTMLElement
   *   - reqElements: { length, uppercase, lowercase, number, special }
   *   - extraCheck: Function returning boolean (optional)
   */
  function attachPasswordValidator(options) {
    var form = options.form;
    var passwordInput = options.passwordInput;
    var confirmInput = options.confirmInput;
    var submitBtn = options.submitBtn;
    var confirmFeedback = options.confirmFeedback;
    var reqElements = options.reqElements;
    var extraCheck = options.extraCheck || function () { return true; };

    if (!passwordInput) return null;

    // Initialize all requirement icons to unmet state
    Object.keys(reqElements).forEach(function (key) {
      var el = reqElements[key];
      if (el) {
        var iconWrap = el.querySelector('.req-icon');
        if (iconWrap) iconWrap.innerHTML = ICONS.unmet;
        el.classList.add('unmet');
        el.classList.remove('met');
        el.setAttribute('aria-checked', 'false');
      }
    });

    function validate() {
      var pwd = passwordInput.value || '';
      var confirm = confirmInput ? (confirmInput.value || '') : '';

      var checks = {
        length: pwd.length >= 8,
        number: /[0-9]/.test(pwd),
        special: /[^a-zA-Z0-9]/.test(pwd),
        uppercase: /[A-Z]/.test(pwd),
        lowercase: /[a-z]/.test(pwd)
      };

      var allRulesMet = true;
      Object.keys(checks).forEach(function (key) {
        var met = checks[key];
        var el = reqElements[key];
        if (el) {
          var iconWrap = el.querySelector('.req-icon');
          if (met) {
            el.classList.remove('unmet');
            el.classList.add('met');
            el.setAttribute('aria-checked', 'true');
            if (iconWrap) iconWrap.innerHTML = ICONS.check;
          } else {
            el.classList.remove('met');
            el.classList.add('unmet');
            el.setAttribute('aria-checked', 'false');
            if (iconWrap) iconWrap.innerHTML = ICONS.unmet;
            allRulesMet = false;
          }
        }
      });

      var passwordsMatch = false;
      if (confirmInput && confirmFeedback) {
        if (confirm.length === 0) {
          confirmFeedback.innerHTML = '';
          confirmFeedback.className = 'password-match-feedback';
          passwordsMatch = false;
        } else if (confirm === pwd) {
          confirmFeedback.innerHTML = ICONS.check + ' <span>Passwords match</span>';
          confirmFeedback.className = 'password-match-feedback match';
          passwordsMatch = true;
        } else {
          confirmFeedback.innerHTML = ICONS.cross + ' <span>Passwords do not match</span>';
          confirmFeedback.className = 'password-match-feedback mismatch';
          passwordsMatch = false;
        }
      } else {
        passwordsMatch = true;
      }

      var extrasValid = extraCheck();

      if (submitBtn) {
        submitBtn.disabled = !(allRulesMet && passwordsMatch && extrasValid);
      }

      return {
        allRulesMet: allRulesMet,
        passwordsMatch: passwordsMatch,
        isValid: allRulesMet && passwordsMatch && extrasValid
      };
    }

    passwordInput.addEventListener('input', validate);
    if (confirmInput) {
      confirmInput.addEventListener('input', validate);
    }

    if (form) {
      form.addEventListener('submit', function (e) {
        var res = validate();
        if (!res.allRulesMet || !res.passwordsMatch || !res.isValid) {
          e.preventDefault();
          e.stopPropagation();
          if (!res.allRulesMet) {
            passwordInput.focus();
          } else if (confirmInput && !res.passwordsMatch) {
            confirmInput.focus();
          }
        }
      });
    }

    // Run initial validation
    validate();

    return {
      validate: validate
    };
  }

  global.PasswordValidator = {
    attach: attachPasswordValidator,
    ICONS: ICONS
  };
})(window);
